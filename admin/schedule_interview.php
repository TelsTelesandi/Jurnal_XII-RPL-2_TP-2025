<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/../includes/Mailer.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);

// Helper: fleksibel untuk berbagai skema tabel notifications
function addNotificationFlexible($conn, $title, $message, $userId, $appId = null, $role = 'user') {
    $checkNotifTable = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
    if (!$checkNotifTable || mysqli_num_rows($checkNotifTable) === 0) return;

    // Skema lengkap (dengan recipient_role, recipient_user_id, application_id)
    $stmt = $conn->prepare("INSERT INTO notifications (title, message, recipient_role, recipient_user_id, application_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $roleVal = $role ?: 'user';
        $appVal  = $appId ?? 0;
        $stmt->bind_param('sssii', $title, $message, $roleVal, $userId, $appVal);
        $stmt->execute();
        $stmt->close();
        return;
    }

    // Fallback skema sederhana (user_id, title, message)
    $stmt2 = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    if ($stmt2) {
        $stmt2->bind_param('iss', $userId, $title, $message);
        $stmt2->execute();
        $stmt2->close();
    }
}

// Get application details and verify it's approved
$stmt = $conn->prepare("SELECT a.*, u.email, j.title as job_title 
                       FROM applications a 
                       JOIN users u ON a.user_id = u.id 
                       JOIN job_postings j ON a.job_id = j.id 
                       WHERE a.id = ? AND a.status = 'approved'");
$stmt->bind_param('i', $id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();

if (!$app) {
    header('Location: applications.php');
    exit();
}

// Lock schedule if it already has a date (once created it cannot be edited)
$schedule_locked = !empty($app['interview_date']);

// Consider schedule complete for showing result section (date+time+location)
$schedule_complete = !empty($app['interview_date']) && !empty($app['interview_time']) && !empty($app['interview_location']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle hasil interview (feedback) jika dikirim
    if (isset($_POST['interview_result'])) {
        $interview_result = $_POST['interview_result'];
        $interview_feedback = trim($_POST['interview_feedback'] ?? '');
        
        // Simpan feedback hasil interview di kolom interview_feedback (terpisah dari interview_notes)
        $stmt = $conn->prepare("UPDATE applications 
                               SET interview_result = ?, 
                                   interview_feedback = ?,
                                   processed_by = ?,
                                   processed_at = NOW()
                               WHERE id = ?");
        $stmt->bind_param('ssii', $interview_result, $interview_feedback, $_SESSION['user_id'], $id);
        
        if ($stmt->execute()) {
            // Kirim email notifikasi ke pelamar
            $to = $app['email'];
            $subject = "Hasil Interview - " . htmlspecialchars($app['job_title']);
            
            if ($interview_result === 'accepted') {
                $message = "
                <html>
                <body>
                    <h2>Selamat! Anda Diterima</h2>
                    <p>Halo,</p>
                    <p>Kami dengan senang hati menginformasikan bahwa Anda <strong>DITERIMA</strong> untuk posisi " . htmlspecialchars($app['job_title']) . ".</p>
                    " . (!empty($interview_feedback) ? "<p><strong>Feedback:</strong><br>" . nl2br(htmlspecialchars($interview_feedback)) . "</p>" : "") . "
                    <p>Silakan login ke sistem untuk melihat langkah selanjutnya.</p>
                    <p>Terima kasih.</p>
                </body>
                </html>
                ";
            } else {
                $message = "
                <html>
                <body>
                    <h2>Hasil Interview</h2>
                    <p>Halo,</p>
                    <p>Terima kasih telah mengikuti proses interview untuk posisi " . htmlspecialchars($app['job_title']) . ".</p>
                    <p>Mohon maaf, pada kesempatan kali ini kami belum dapat melanjutkan proses rekrutmen.</p>
                    " . (!empty($interview_feedback) ? "<p><strong>Feedback:</strong><br>" . nl2br(htmlspecialchars($interview_feedback)) . "</p>" : "") . "
                    <p>Kami berharap dapat bekerja sama dengan Anda di kesempatan lain.</p>
                    <p>Terima kasih.</p>
                </body>
                </html>
                ";
            }

            if ($interview_result === 'accepted') {
                if (sendMail($to, $subject, $message)) {
                    $_SESSION['success'] = "Hasil interview berhasil disimpan dan email notifikasi telah dikirim";
                } else {
                    $_SESSION['warning'] = "Hasil interview tersimpan tetapi gagal mengirim email notifikasi";
                }
            } else {
                $_SESSION['success'] = "Hasil interview berhasil disimpan";
            }

            // Buat notifikasi untuk user terkait hasil interview
            $notif_title = 'Hasil Interview: ' . ($app['job_title'] ?? '');
            $status_text = $interview_result === 'accepted' ? 'Diterima' : 'Ditolak';
            $notif_message = "Status: {$status_text}.";
            if (!empty($interview_feedback)) {
                $notif_message .= " Feedback: " . $interview_feedback;
            }
            addNotificationFlexible($conn, $notif_title, $notif_message, (int)$app['user_id'], (int)$app['id'], 'user');
            
            header('Location: schedule_interview.php?id=' . $id);
            exit();
        }
    } else {
        // Handle jadwal interview
        // If schedule is locked (already created), do NOT allow updates
        if ($schedule_locked) {
            $_SESSION['warning'] = "Jadwal interview sudah tersimpan dan tidak dapat diubah.";
            header('Location: schedule_interview.php?id=' . $id);
            exit();
        }

        $interview_date = $_POST['interview_date'] ?? '';
        $interview_time = $_POST['interview_time'] ?? '';
        $interview_location = $_POST['interview_location'] ?? '';
        $interview_notes = trim($_POST['interview_notes'] ?? '');

        // Update semua field jadwal termasuk interview_notes
        $stmt = $conn->prepare("UPDATE applications 
                               SET interview_date = ?, 
                                   interview_time = ?, 
                                   interview_location = ?,
                                   interview_notes = ?
                               WHERE id = ?");
        $stmt->bind_param('ssssi', 
            $interview_date, 
            $interview_time, 
            $interview_location, 
            $interview_notes, 
            $id
        );

        if ($stmt->execute()) {
            // Cek apakah ini jadwal baru atau update
            $is_new_schedule = empty($app['interview_date']);
            
            if ($is_new_schedule) {
                // Kirim email hanya saat pertama kali membuat jadwal
                $to = $app['email'];
                $subject = "Jadwal Interview - " . htmlspecialchars($app['job_title']);
                
                $message = "
                <html>
                <body>
                    <h2>Jadwal Interview</h2>
                    <p>Halo,</p>
                    <p>Anda telah dijadwalkan untuk interview pada:</p>
                    <ul>
                        <li><strong>Tanggal:</strong> " . date('d F Y', strtotime($interview_date)) . "</li>
                        <li><strong>Waktu:</strong> " . date('H:i', strtotime($interview_time)) . " WIB</li>
                        <li><strong>Lokasi:</strong> " . htmlspecialchars($interview_location) . "</li>
                    </ul>
                    " . (!empty($interview_notes) ? "<p><strong>Catatan:</strong><br>" . nl2br(htmlspecialchars($interview_notes)) . "</p>" : "") . "
                    <p>Harap hadir tepat waktu. Terima kasih.</p>
                </body>
                </html>
                ";

                // Kirim email via PHPMailer
                if (sendMail($to, $subject, $message)) {
                    $_SESSION['success'] = "Jadwal interview berhasil disimpan dan email notifikasi telah dikirim";
                } else {
                    $_SESSION['warning'] = "Jadwal tersimpan tetapi gagal mengirim email notifikasi";
                }

                // Buat notifikasi untuk user (jadwal interview)
                $notif_title = 'Jadwal Interview: ' . ($app['job_title'] ?? '');
                $notif_message = 'Jadwal: ' . date('d M Y', strtotime($interview_date)) . ' ' . date('H:i', strtotime($interview_time)) . ' WIB. ';
                $notif_message .= 'Lokasi: ' . (!empty($interview_location) ? $interview_location : '-');
                if (!empty($interview_notes)) {
                    $notif_message .= '. Catatan: ' . $interview_notes;
                }
                addNotificationFlexible($conn, $notif_title, $notif_message, (int)$app['user_id'], (int)$app['id'], 'user');
            } else {
                $_SESSION['success'] = "Jadwal interview berhasil diupdate";
            }
            
            header('Location: schedule_interview.php?id=' . $id);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jadwalkan Interview</title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="applications.php">📝 Lamaran</a>
            <a href="vacancies.php">💼 Lowongan</a>
        </div>
        <div class="user-menu">
            <span class="admin-badge">👑 Admin</span>
        </div>
    </div>

    <div class="container">
        <div class="content-box">
            <h2 class="content-title">
                <?= $schedule_locked ? '📅 Detail Jadwal Interview' : (!empty($app['interview_date']) ? '📅 Edit Jadwal Interview' : '📅 Jadwalkan Interview') ?>
            </h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning">
                    <?= $_SESSION['warning'] ?>
                    <?php unset($_SESSION['warning']); ?>
                </div>
            <?php endif; ?>
            
            <div class="application-info">
                <p><strong>Posisi:</strong> <?= htmlspecialchars($app['job_title']) ?></p>
                <p><strong>Email Pelamar:</strong> <?= htmlspecialchars($app['email']) ?></p>
            </div>

            <!-- Form Jadwal Interview -->
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Tanggal Interview</label>
                    <input type="date" name="interview_date" class="form-control" 
                           value="<?= !empty($app['interview_date']) ? date('Y-m-d', strtotime($app['interview_date'])) : '' ?>"
                           min="<?= date('Y-m-d') ?>"
                           <?= $schedule_locked ? 'disabled' : 'required' ?>>
                </div>

                <div class="form-group">
                    <label class="form-label">Waktu Interview</label>
                    <input type="time" name="interview_time" class="form-control" 
                           value="<?= !empty($app['interview_time']) ? date('H:i', strtotime($app['interview_time'])) : '' ?>"
                           <?= $schedule_locked ? 'disabled' : 'required' ?>>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi Interview</label>
                    <input type="text" name="interview_location" class="form-control" 
                           value="<?= htmlspecialchars($app['interview_location'] ?? '') ?>"
                           placeholder="Contoh: Ruang Meeting Lt.2"
                           <?= $schedule_locked ? 'disabled' : 'required' ?>>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="interview_notes" class="form-control" rows="4"
                              placeholder="Masukkan catatan atau instruksi tambahan..."
                              <?= $schedule_locked ? 'disabled' : '' ?>><?php 
                        if (!empty($app['interview_notes'])) {
                            echo htmlspecialchars($app['interview_notes']);
                        }
                    ?></textarea>
                    <small class="form-hint">Catatan ini untuk instruksi/petunjuk sebelum atau setelah interview. Terpisah dari feedback hasil interview.</small>
                </div>

                <?php if (!$schedule_locked): ?>
                    <button type="submit" class="btn-submit">
                        <?= !empty($app['interview_date']) ? '💾 Update Jadwal' : '💾 Simpan Jadwal' ?>
                    </button>
                <?php else: ?>
                    <div class="alert alert-info">
                        ✅ Jadwal interview sudah tersimpan dan tidak dapat diubah.
                    </div>
                <?php endif; ?>
            </form>

            <?php if ($schedule_complete): ?>
                <!-- Hasil Interview Section - Hanya muncul jika jadwal sudah lengkap -->
                <div class="interview-result-section">
                    <h3 class="section-title">📋 Hasil Interview</h3>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success'] ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['warning'])): ?>
                        <div class="alert alert-warning">
                            <?= $_SESSION['warning'] ?>
                            <?php unset($_SESSION['warning']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="interview-schedule-info">
                        <p><strong>📅 Tanggal:</strong> <?= date('d F Y', strtotime($app['interview_date'])) ?></p>
                        <p><strong>🕐 Waktu:</strong> <?= date('H:i', strtotime($app['interview_time'])) ?> WIB</p>
                        <p><strong>📍 Lokasi:</strong> <?= htmlspecialchars($app['interview_location']) ?></p>
                        <?php if (!empty($app['interview_notes'])): ?>
                            <p><strong>📝 Catatan Jadwal:</strong> <?= nl2br(htmlspecialchars($app['interview_notes'])) ?></p>
                        <?php endif; ?>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Status Interview</label>
                            <select name="interview_result" class="form-control" required>
                                <option value="">- Pilih Hasil -</option>
                                <option value="accepted" <?= ($app['interview_result'] ?? '') === 'accepted' ? 'selected' : '' ?>>✅ Diterima</option>
                                <option value="rejected" <?= ($app['interview_result'] ?? '') === 'rejected' ? 'selected' : '' ?>>❌ Ditolak</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Feedback Hasil Interview</label>
                            <textarea name="interview_feedback" class="form-control" rows="5"
                                      placeholder="Masukkan feedback hasil interview untuk kandidat..."><?php 
                                if (!empty($app['interview_feedback'])) {
                                    echo htmlspecialchars($app['interview_feedback']);
                                }
                            ?></textarea>
                            <small class="form-hint">Feedback ini akan dikirim ke pelamar sebagai hasil interview. Disimpan terpisah dari catatan jadwal.</small>
                        </div>

                        <button type="submit" class="btn-submit-result">💾 Simpan Hasil Interview</button>
                    </form>

                    <?php if (!empty($app['interview_result'])): ?>
                        <div class="current-result">
                            <p><strong>Status Saat Ini:</strong> 
                                <?php 
                                $result = $app['interview_result'];
                                if ($result === 'accepted'): ?>
                                    <span class="result-badge accepted">✅ Diterima</span>
                                <?php elseif ($result === 'rejected'): ?>
                                    <span class="result-badge rejected">❌ Ditolak</span>
                                <?php else: ?>
                                    <span class="result-badge status-pending">⏳ Menunggu Hasil</span>
                                <?php endif; ?>
                            </p>

                            <?php if (!empty($app['interview_feedback'])): ?>
                                <p style="margin-top:10px;"><strong>🗒️ Feedback:</strong><br><?= nl2br(htmlspecialchars($app['interview_feedback'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Notifikasi jika jadwal belum lengkap -->
                <div class="alert alert-info" style="margin-top: 30px;">
                    ⚠️ Silakan isi form jadwal interview terlebih dahulu. Hasil interview akan tampil setelah jadwal tersimpan.
                </div>
            <?php endif; ?>
        </div>
    </div>

<style>
    :root {
        --primary: #4361ee;
        --danger: #ef233c;
        --success: #0e9f6e;
        --text-dark: #2d3748;
        --text-light: #718096;
        --bg-light: #f7fafc;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
        background: var(--bg-light);
    }

    .top-bar {
        background: linear-gradient(135deg, var(--primary), #2c44b3);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        box-sizing: border-box;
    }

    .nav-menu {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .nav-menu a {
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 15px;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-menu a:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }

    .nav-menu a.active {
        background: #6688ee;
        box-shadow: 0 2px 8px rgba(102, 136, 238, 0.3);
    }

    .admin-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 15px;
    }

    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }

    .content-box {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    }

    .content-title {
        font-size: 20px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary);
        color: var(--text-dark);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-dark);
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 15px;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .form-control:disabled {
        background: #f8fafc;
        color: var(--text-light);
        cursor: not-allowed;
        border-color: #cbd5e0;
    }

    .form-hint {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: var(--text-light);
        font-style: italic;
    }

    .btn-submit {
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .application-info {
        background: #f8fafc;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .application-info p {
        margin: 8px 0;
        color: var(--text-dark);
    }

    .interview-result-section {
        margin-top: 30px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .section-title {
        font-size: 18px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary);
        color: var(--text-dark);
        font-weight: 600;
    }

    .interview-schedule-info {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid var(--primary);
    }

    .interview-schedule-info p {
        margin: 8px 0;
        color: var(--text-dark);
    }

    .btn-submit-result {
        background: var(--success);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-submit-result:hover {
        background: #0b876a;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .current-result {
        margin-top: 20px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .current-result p {
        margin: 0;
        color: var(--text-dark);
    }

    .result-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        margin-left: 10px;
    }

    .result-badge.accepted {
        background: #d4edda;
        color: #155724;
    }

    .result-badge.rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .result-badge.status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
</style>
</body>
</html>
