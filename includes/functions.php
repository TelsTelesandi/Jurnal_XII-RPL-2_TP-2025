<?php

// Create notification
function createNotification($conn, $user_id, $title, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message]);
}

// Upload file
function uploadFile($file, $target_dir) {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $fileName = uniqid() . '.' . $fileType;
    $target_file = $target_dir . $fileName;
    
    // Check if file is PDF
    if ($fileType != "pdf") {
        throw new Exception("Only PDF files are allowed.");
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $fileName;
    } else {
        throw new Exception("Failed to upload file.");
    }
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Get application status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger'
    ];
    
    return '<span class="badge bg-' . $badges[$status] . '">' . ucfirst($status) . '</span>';
}

// Count applications by status
function getApplicationStats($conn) {
    $stats = [
        'total' => 0,
        'approved' => 0,
        'rejected' => 0,
        'pending' => 0
    ];
    
    $stmt = $conn->query("SELECT status, COUNT(*) as count FROM job_applications GROUP BY status");
    while ($row = $stmt->fetch()) {
        $stats[$row['status']] = $row['count'];
        $stats['total'] += $row['count'];
    }
    
    return $stats;
}