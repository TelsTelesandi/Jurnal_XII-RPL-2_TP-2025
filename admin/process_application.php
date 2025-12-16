<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$id = (int)($_REQUEST['id'] ?? 0);
$action = ($_REQUEST['action'] ?? '');
if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    header('Location: applications.php');
    exit();
}

// fetch application to get user_id, job title for notification
$appStmt = $conn->prepare("SELECT id, user_id, job_id FROM applications WHERE id = ? LIMIT 1");
if (!$appStmt) {
    $_SESSION['error'] = 'DB error.';
    header('Location: applications.php'); exit();
}
$appStmt->bind_param('i', $id);
$appStmt->execute();
$appRes = $appStmt->get_result();
$appRow = $appRes ? $appRes->fetch_assoc() : null;
$appStmt->close();

if (!$appRow) {
    $_SESSION['error'] = 'Application not found.';
    header('Location: applications.php'); exit();
}

$status = $action === 'approve' ? 'approved' : 'rejected';
$processed_by = (int)$_SESSION['user_id'];

// update application with processed_by and processed_at
$update = $conn->prepare("UPDATE applications SET status = ?, processed_by = ?, processed_at = NOW() WHERE id = ? LIMIT 1");
if (!$update) {
    $_SESSION['error'] = 'DB error (update).';
    header('Location: applications.php'); exit();
}
$update->bind_param('sii', $status, $processed_by, $id);
$ok = $update->execute();
$update->close();

if ($ok) {
    // if approved, insert notification record (if notifications table exists)
    if ($status === 'approved') {
        $check = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
        if ($check && mysqli_num_rows($check) > 0) {
            // get job title (optional)
            $jobTitle = null;
            $j = $conn->prepare("SELECT title FROM job_postings WHERE id = ? LIMIT 1");
            if ($j) {
                $j->bind_param('i', $appRow['job_id']);
                $j->execute();
                $jr = $j->get_result();
                $jj = $jr ? $jr->fetch_assoc() : null;
                $jobTitle = $jj['title'] ?? null;
                $j->close();
            }

            $title = 'Application approved';
            if ($jobTitle) $title = 'Approved: ' . $jobTitle;
            $message = 'Your application #' . $id . ' has been approved.';
            $recipient_user_id = (int)$appRow['user_id'];
            $application_id = (int)$appRow['id']; // <-- application id

            $ins = $conn->prepare("INSERT INTO notifications (title, message, recipient_role, recipient_user_id, application_id) VALUES (?, ?, 'user', ?, ?)");
            if ($ins) {
                $ins->bind_param('ssii', $title, $message, $recipient_user_id, $application_id);
                $ins->execute();
                $ins->close();
            }
        }
    }
    $_SESSION['success'] = 'Application processed.';
} else {
    $_SESSION['error'] = 'Failed to update application.';
}

header('Location: applications.php');
exit();
?>