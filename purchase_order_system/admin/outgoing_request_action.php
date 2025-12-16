<?php
require_once __DIR__ . '/../includes/db_connect.php';

// Pastikan pengguna adalah admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Validasi input
$transactionId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$adminNotes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : '';

if ($transactionId <= 0 || $action === '') {
    http_response_code(400);
    echo 'Bad Request';
    exit;
}

try {
    // Pastikan transaksi ada
    $stmt = $pdo->prepare("SELECT id, status FROM outgoing_transactions WHERE id = ?");
    $stmt->execute([$transactionId]);
    $tx = $stmt->fetch();
    if (!$tx) {
        http_response_code(404);
        echo 'Not Found';
        exit;
    }

    if ($action === 'approve') {
        // Proses approval dengan dukungan approve sebagian dan pengurangan stok
        $pdo->beginTransaction();

        // Deteksi kolom skema untuk kompatibilitas (requested_quantity vs quantity, approved_quantity, status)
        $hasRequested = false; $hasQuantity = false; $hasApproved = false; $hasItemStatus = false;
        try {
            $hasRequested = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'requested_quantity'")->fetch();
            $hasQuantity  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'quantity'")->fetch();
            $hasApproved  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch();
            $hasItemStatus = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'status'")->fetch();
        } catch (Exception $ex) {}

        $requestedCol = $hasRequested ? 'requested_quantity' : ($hasQuantity ? 'quantity' : 'requested_quantity');

        // Ambil detail item yang diminta beserta stok saat ini
        $statusSelect = $hasItemStatus ? "COALESCE(oti.status,'pending')" : "'pending'";
        $itemsSQL = "SELECT oti.id AS detail_id, oti.item_id,
                            oti.$requestedCol AS requested_qty,
                            i.stock AS current_stock,
                            $statusSelect AS item_status
                     FROM outgoing_transaction_items oti
                     JOIN items i ON i.id = oti.item_id
                     WHERE oti.transaction_id = ?";
        $itemsStmt = $pdo->prepare($itemsSQL);
        $itemsStmt->execute([$transactionId]);
        $details = $itemsStmt->fetchAll();

        if (!$details) {
            throw new Exception('Tidak ada item pada request ini.');
        }

        $allFulfilled = true;

        foreach ($details as $d) {
            $requested = (int)$d['requested_qty'];
            $stock = (int)$d['current_stock'];
            if ($hasItemStatus && isset($d['item_status']) && $d['item_status'] === 'rejected') {
                $approveQty = 0;
            } else {
                $approveQty = min($requested, max($stock, 0));
            }

            // Update approved_quantity dan/atau status item sesuai kolom yang tersedia
            if ($hasApproved && $hasItemStatus) {
                $updateApproved = $pdo->prepare(
                    "UPDATE outgoing_transaction_items
                     SET approved_quantity = ?,
                         status = CASE WHEN ? > 0 THEN 'approved' ELSE 'rejected' END
                     WHERE id = ?"
                );
                $updateApproved->execute([$approveQty, $approveQty, $d['detail_id']]);
            } elseif ($hasApproved && !$hasItemStatus) {
                $updateApproved = $pdo->prepare(
                    "UPDATE outgoing_transaction_items SET approved_quantity = ? WHERE id = ?"
                );
                $updateApproved->execute([$approveQty, $d['detail_id']]);
            } elseif (!$hasApproved && $hasItemStatus) {
                $updateApproved = $pdo->prepare(
                    "UPDATE outgoing_transaction_items
                     SET status = CASE WHEN ? > 0 THEN 'approved' ELSE 'rejected' END
                     WHERE id = ?"
                );
                $updateApproved->execute([$approveQty, $d['detail_id']]);
            }

            // Kurangi stok berdasarkan approved_quantity
            if ($approveQty > 0) {
                $updateStock = $pdo->prepare("UPDATE items SET stock = stock - ? WHERE id = ? AND stock >= ?");
                $updateStock->execute([$approveQty, $d['item_id'], $approveQty]);
                if ($updateStock->rowCount() === 0) {
                    // Tidak bisa kurangi stok sesuai approvedQty -> tandai tidak terpenuhi penuh
                    $allFulfilled = false;
                }
            }

            if ($approveQty < $requested) {
                $allFulfilled = false;
            }
        }

        // Tentukan status transaksi dan simpan catatan admin
        $status = $allFulfilled ? 'approved' : 'partially_approved';
        $notesToSave = $adminNotes;
        if (!$allFulfilled) {
            $notesToSave = trim($notesToSave . (empty($notesToSave) ? '' : '\n') . 'Sebagian item disetujui karena stok tidak mencukupi.');
        }

        // Pastikan enum status mendukung 'partially_approved'; jika tidak, fallback ke 'approved' dengan catatan
        try {
            $infoStmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'outgoing_transactions' AND COLUMN_NAME = 'status'");
            $infoStmt->execute();
            $colType = $infoStmt->fetchColumn();
            if ($status === 'partially_approved' && (!$colType || stripos($colType, "'partially_approved'") === false)) {
                $status = 'approved';
                $notesToSave = trim($notesToSave . (empty($notesToSave) ? '' : '\n') . 'Disetujui sebagian.');
            }
        } catch (Exception $ex) {}

        // Update transaksi dengan status dan catatan admin
        $pdo->prepare("UPDATE outgoing_transactions SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$status, $notesToSave, $transactionId]);

        $pdo->commit();
        $_SESSION['success_message'] = $allFulfilled ? 'Request disetujui.' : 'Request disetujui sebagian.';
    } elseif ($action === 'reject') {
        if ($adminNotes === '') {
            $_SESSION['error_message'] = 'Catatan wajib diisi saat menolak request.';
            $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../admin/outgoing_items.php';
            header('Location: ' . $redirect);
            exit;
        }
        $pdo->prepare("UPDATE outgoing_transactions SET status = 'rejected', admin_notes = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$adminNotes, $transactionId]);
        $_SESSION['success_message'] = 'Request ditolak.';
    } elseif ($action === 'reject_items') {
        if ($adminNotes === '') {
            $_SESSION['error_message'] = 'Catatan wajib diisi saat menolak item.';
            $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../admin/outgoing_items.php';
            header('Location: ' . $redirect);
            exit;
        }

        $itemIds = isset($_POST['reject_item_ids']) && is_array($_POST['reject_item_ids']) ? array_map('intval', $_POST['reject_item_ids']) : [];
        if (empty($itemIds)) {
            $_SESSION['error_message'] = 'Pilih minimal satu item untuk ditolak.';
            $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../admin/outgoing_items.php';
            header('Location: ' . $redirect);
            exit;
        }

        $pdo->beginTransaction();
        // Deteksi kolom status/approved pada item
        $hasApproved = false; $hasItemStatus = false;
        try {
            $hasApproved  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch();
            $hasItemStatus = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'status'")->fetch();
        } catch (Exception $ex) {}

        // Validasi item belongs to transaction
        $inPlaceholders = implode(',', array_fill(0, count($itemIds), '?'));
        $validateStmt = $pdo->prepare("SELECT COUNT(*) FROM outgoing_transaction_items WHERE id IN ($inPlaceholders) AND transaction_id = ?");
        $validateParams = $itemIds; $validateParams[] = $transactionId;
        $validateStmt->execute($validateParams);
        $countBelongs = (int)$validateStmt->fetchColumn();
        if ($countBelongs !== count($itemIds)) {
            $pdo->rollBack();
            $_SESSION['error_message'] = 'Item yang ditolak tidak valid untuk request ini.';
            $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../admin/outgoing_items.php';
            header('Location: ' . $redirect);
            exit;
        }

        // Update item status/approved dan simpan catatan per item
        if ($hasItemStatus) {
            $upd = $pdo->prepare("UPDATE outgoing_transaction_items SET status = 'rejected' WHERE id IN ($inPlaceholders) AND transaction_id = ?");
            $params = $itemIds; $params[] = $transactionId;
            $upd->execute($params);
            // Simpan catatan ke kolom item notes
            $updNotes = $pdo->prepare("UPDATE outgoing_transaction_items SET notes = CONCAT(COALESCE(notes,''), CASE WHEN COALESCE(notes,'') = '' THEN '' ELSE '\n' END, ?) WHERE id IN ($inPlaceholders) AND transaction_id = ?");
            $paramsN = array_merge([$adminNotes], $itemIds); $paramsN[] = $transactionId;
            $updNotes->execute($paramsN);
        }
        if ($hasApproved) {
            $upd2 = $pdo->prepare("UPDATE outgoing_transaction_items SET approved_quantity = 0 WHERE id IN ($inPlaceholders) AND transaction_id = ?");
            $params2 = $itemIds; $params2[] = $transactionId;
            $upd2->execute($params2);
        }

        // Catatan admin hanya disimpan pada item yang ditolak (kolom notes item)

        // Jika semua item ditolak, ubah status transaksi menjadi rejected
        $checkAllStmt = $pdo->prepare("SELECT SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rej, COUNT(*) AS total FROM outgoing_transaction_items WHERE transaction_id = ?");
        $checkAllStmt->execute([$transactionId]);
        $row = $checkAllStmt->fetch();
        if ($row && (int)$row['rej'] >= (int)$row['total'] && (int)$row['total'] > 0) {
            $pdo->prepare("UPDATE outgoing_transactions SET status = 'rejected', updated_at = NOW() WHERE id = ?")
                ->execute([$transactionId]);
        }

        $pdo->commit();
        $_SESSION['success_message'] = 'Item terpilih ditolak.';
    } elseif ($action === 'delete') {
        // Hapus transaksi dan item terkait
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM outgoing_transaction_items WHERE transaction_id = ?")
            ->execute([$transactionId]);
        $pdo->prepare("DELETE FROM outgoing_transactions WHERE id = ?")
            ->execute([$transactionId]);
        $pdo->commit();
        $_SESSION['success_message'] = 'Request dihapus.';
    } else {
        http_response_code(400);
        echo 'Unsupported action';
        exit;
    }

    // Redirect kembali ke halaman yang memanggil (fallback ke outgoing_items.php)
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../admin/outgoing_items.php';
    header('Location: ' . $redirect);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Admin action error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Terjadi kesalahan saat memproses aksi.';
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../admin/outgoing_items.php';
    header('Location: ' . $redirect);
    exit;
}
?>