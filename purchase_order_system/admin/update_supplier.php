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
    $required_fields = ['supplier_id', 'supplier_code', 'name', 'contact_person', 'phone'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception("Field $field is required");
        }
    }

    $supplier_id = (int)$_POST['supplier_id'];
    $supplier_code = trim($_POST['supplier_code']);
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Periksa apakah kode supplier sudah digunakan (kecuali untuk supplier ini)
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE supplier_id = ? AND id != ?");
    $stmt->execute([$supplier_code, $supplier_id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Kode supplier sudah digunakan");
    }

    // Update data supplier
    $stmt = $pdo->prepare("
        UPDATE suppliers 
        SET supplier_id = ?, name = ?, contact_person = ?, 
            phone = ?, email = ?, address = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $supplier_code, $name, $contact_person, 
        $phone, $email, $address,
        $supplier_id
    ]);

    // Berhasil diupdate
    echo json_encode([
        'success' => true,
        'message' => 'Data supplier berhasil diperbarui'
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
