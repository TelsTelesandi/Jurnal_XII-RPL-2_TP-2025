<?php
session_start();
require_once '../config/database.php';

// Get job and user details
$job_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT j.*, u.username, u.email, u.full_name
                       FROM job_postings j 
                       CROSS JOIN users u 
                       WHERE j.id = ? AND u.id = ?");
$stmt->bind_param('ii', $job_id, $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Add this check after getting user details
$check_stmt = $conn->prepare("SELECT id, job_id FROM applications WHERE user_id = ?");
$check_stmt->bind_param('i', $user_id);
$check_stmt->execute();
$existing = $check_stmt->get_result()->fetch_assoc();

if ($existing) {
    $_SESSION['error'] = "Anda sudah melamar pekerjaan lain. Tidak dapat melamar lebih dari satu lowongan.";
    header('Location: vacancies.php');
    exit();
}

$job_id = (int)($_GET['id'] ?? 0);
// ambil kuota dan hitung pelamar sekarang
$row = $conn->query("SELECT max_applicants FROM job_postings WHERE id = $job_id")->fetch_assoc();
$max = (int)($row['max_applicants'] ?? 0);
$app_count = $conn->query("SELECT COUNT(*) as total FROM applications WHERE job_id = $job_id")->fetch_assoc()['total'];

if ($max > 0 && $app_count >= $max) {
    $_SESSION['error'] = "Maaf, kuota untuk lowongan ini telah terpenuhi.";
    header('Location: vacancies.php');
    exit();
}

// lanjutkan insert aplikasi...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lamar - <?= htmlspecialchars($data['title']) ?></title>
</head>
<body>
    <div class="top-bar">
        <div class="menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="vacancies.php" class="active">💼 Lowongan</a>
            <a href="applications.php">📝 Lamaran</a>
        </div>
        <a href="../logout.php" class="logout">Keluar</a>
    </div>

    <div class="container">
        <div class="content-box">
            <h2 class="content-title">📝 Formulir Lamaran: <?= htmlspecialchars($data['title']) ?></h2>

            <div class="applicant-details">
                <div class="detail-group">
                    <label class="detail-label">Username:</label>
                    <div class="detail-value"><?= htmlspecialchars($data['username']) ?></div>
                </div>

                <div class="detail-group">
                    <label class="detail-label">Email:</label>
                    <div class="detail-value"><?= htmlspecialchars($data['email']) ?></div>
                </div>

                <div class="detail-group">
                    <label class="detail-label">Full name:</label>
                    <div class="detail-value"><?= htmlspecialchars($data['full_name']) ?></div>
                </div>
            </div>

            <form method="POST" action="submit_application.php" enctype="multipart/form-data">
                <input type="hidden" name="job_id" value="<?= $data['id'] ?>">
                
                <div class="form-group">
                    <label class="form-label">Cover Letter</label>
                    <textarea name="cover_letter" class="form-control" rows="6" required 
                              placeholder="Tuliskan cover letter Anda di sini..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto (Opsional)</label>
                    <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                    <small class="form-text">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                </div>
                
                <button type="submit" class="btn-submit">💼 Kirim Lamaran</button>
            </form>
        </div>
    </div>

    <style>
        :root {
            --primary: #4361ee;
            --danger: #ef233c;
            --text-dark: #2d3748;
            --text-light: #718096;
            --bg-light: #f7fafc;
        }
        
        body { 
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
        }

        .top-bar {
            background: linear-gradient(135deg, var(--primary), #2c44b3);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 64px;
            box-sizing: border-box;
        }

        .menu {
            display: flex;
            gap: 30px;
        }

        .menu a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .menu a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }

        .menu a.active {
            background: #6688ee;
            color: white;
            box-shadow: 0 2px 8px rgba(102, 136, 238, 0.3);
        }

        .menu a.active:hover {
            background: #7799ff;
            transform: translateY(-1px);
        }

        .logout {
            background: var(--danger);
            padding: 8px 20px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(239,35,60,0.2);
            border: none;
            cursor: pointer;
        }

        .logout:hover {
            background: #dc2f45;
            transform: translateY(-1px);
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
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid rgba(0,0,0,0.05);
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

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        input[type="text"], 
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            background-color: #f9fafb;
            box-sizing: border-box;
        }

        input[disabled] {
            background-color: #f3f4f6;
            color: var(--text-light);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #e2e8f0;
            border-radius: 6px;
            background-color: #f9fafb;
            cursor: pointer;
        }

        .note {
            font-size: 13px;
            color: var(--text-light);
            margin-top: 6px;
        }

        button[type="submit"] {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(67,97,238,0.2);
        }

        button[type="submit"]:hover {
            background: #2c44b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(67,97,238,0.3);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            background-color: #f9fafb;
            box-sizing: border-box;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(67,97,238,0.2);
        }

        .btn-submit:hover {
            background: #2c44b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(67,97,238,0.3);
        }

        .form-text {
            font-size: 13px;
            color: var(--text-light);
            margin-top: 6px;
        }

        .applicant-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-group {
            margin-bottom: 12px;
        }

        .info-group label {
            font-weight: 500;
            color: var(--text-dark);
            margin-right: 10px;
            min-width: 120px;
            display: inline-block;
        }

        .info-group span {
            color: var(--text-dark);
        }

        .applicant-details {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-group {
            margin-bottom: 12px;
        }

        .detail-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 5px;
            display: block;
        }

        .detail-value {
            color: var(--text-dark);
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
    </style>
</body>
</html>
