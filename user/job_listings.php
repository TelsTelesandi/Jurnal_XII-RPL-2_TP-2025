<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// pastikan koneksi mysqli
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection error.');
}

// cek login role user
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: ../login.php');
    exit();
}

// ambil semua job postings (tampilkan semua, tapi disable apply jika hidden)
$sql = "SELECT * FROM job_postings ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result === false) $result = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Daftar Lowongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h3>Lowongan yg tersedia</h3>
        <div class="row">
            <div class="col-12">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($job = mysqli_fetch_assoc($result)): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>

                                <?php if ((int)$job['is_visible'] === 1): ?>
                                    <a href="apply_job.php?job_id=<?php echo (int)$job['id']; ?>" class="btn btn-primary">Apply Now</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled title="Lowongan tidak tersedia">Apply (Not available)</button>
                                    <span class="badge bg-secondary ms-2">Hidden</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No job positions available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>