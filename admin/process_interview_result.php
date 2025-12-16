<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/../includes/Mailer.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = $_POST['application_id'];
    $result = $_POST['interview_result'];
    $notes = $_POST['interview_notes'];

    // Update application status
    $stmt = $conn->prepare("UPDATE applications SET 
        interview_result = ?,
        interview_notes = ?,
        processed_by = ?,
        processed_at = NOW()
        WHERE id = ?");

    if ($stmt) {
        $stmt->bind_param('ssii', $result, $notes, $_SESSION['user_id'], $app_id);
        
        if ($stmt->execute()) {
            // Get user email for notification
            $sql = "SELECT u.email, u.username, j.title 
                   FROM applications a 
                   JOIN users u ON a.user_id = u.id 
                   JOIN job_postings j ON a.job_id = j.id 
                   WHERE a.id = ?";
            
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param('i', $app_id);
            $stmt2->execute();
            $data = $stmt2->get_result()->fetch_assoc();

            // Send email notification
            if ($data) {
                $to = $data['email'];
                $subject = "Hasil Interview - " . htmlspecialchars($data['title']);

                if ($result === 'accepted') {
                    $message = "<html><body>".
                        "<h2>Selamat! Anda Diterima</h2>".
                        "<p>Halo, " . htmlspecialchars($data['username']) . ",</p>".
                        "<p>Kami dengan senang hati menginformasikan bahwa Anda <strong>DITERIMA</strong> untuk posisi " . htmlspecialchars($data['title']) . ".</p>".
                        (!empty($notes) ? "<p><strong>Feedback:</strong><br>" . nl2br(htmlspecialchars($notes)) . "</p>" : "") .
                        "<p>Silakan login ke sistem untuk melihat langkah selanjutnya.</p>".
                        "<p>Terima kasih.</p>".
                        "</body></html>";

                    sendMail($to, $subject, $message);
                }

                // Optional: create notification record for the user
                $check = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
                if ($check && mysqli_num_rows($check) > 0) {
                    $statusText = $result === 'accepted' ? 'Diterima' : 'Ditolak';
                    $title = 'Hasil Interview: ' . htmlspecialchars($data['title']);
                    $messageNotif = 'Status: ' . $statusText;
                    if (!empty($notes)) {
                        $messageNotif .= '. Feedback: ' . $notes;
                    }
                    // get user id
                    $uidStmt = $conn->prepare("SELECT user_id FROM applications WHERE id = ?");
                    if ($uidStmt) {
                        $uidStmt->bind_param('i', $app_id);
                        $uidStmt->execute();
                        $uidRes = $uidStmt->get_result();
                        $uidRow = $uidRes ? $uidRes->fetch_assoc() : null;
                        $uidStmt->close();
                        $recipient_user_id = (int)($uidRow['user_id'] ?? 0);

                        $ins = $conn->prepare("INSERT INTO notifications (title, message, recipient_role, recipient_user_id, application_id) VALUES (?, ?, 'user', ?, ?)");
                        if ($ins) {
                            $ins->bind_param('ssii', $title, $messageNotif, $recipient_user_id, $app_id);
                            $ins->execute();
                            $ins->close();
                        }
                    }
                }
            }

            $_SESSION['success'] = "Hasil interview berhasil disimpan dan notifikasi telah dikirim";
        } else {
            $_SESSION['error'] = "Gagal menyimpan hasil interview";
        }
    }
    
    header('Location: view_application.php?id=' . $app_id);
    exit();
}

// If not POST, redirect back
header('Location: applications.php');
exit();
