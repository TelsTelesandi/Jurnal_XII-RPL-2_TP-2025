<?php
require_once '../includes/db_connect.php';

// Set header untuk response JSON
header('Content-Type: application/json');

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Validasi input
    $required_fields = ['item_id', 'item_code', 'item_name', 'unit', 'stock', 'unit_price'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception("Field $field is required");
        }
    }

    $item_id = (int)$_POST['item_id'];
    $item_code = trim($_POST['item_code']);
    $item_name = trim($_POST['item_name']);
    $unit = trim($_POST['unit']);
    $stock = (int)$_POST['stock'];
    $unit_price = (float)$_POST['unit_price'];
    $description = trim($_POST['description'] ?? '');

    // Periksa apakah kode barang sudah digunakan (kecuali untuk item ini)
    $stmt = $pdo->prepare("SELECT id FROM items WHERE item_code = ? AND id != ?");
    $stmt->execute([$item_code, $item_id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Kode barang sudah digunakan");
    }

    // Update data barang
    $stmt = $pdo->prepare("
        UPDATE items 
        SET item_code = ?, item_name = ?, unit = ?, 
            stock = ?, unit_price = ?, description = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $item_code, $item_name, $unit, 
        $stock, $unit_price, $description,
        $item_id
    ]);

    // Berhasil diupdate
    echo json_encode([
        'success' => true,
        'message' => 'Data barang berhasil diperbarui'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
