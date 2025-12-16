<?php
session_start();
require_once '../config/database.php';

// Cek login admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM job_postings WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$vacancy = $stmt->get_result()->fetch_assoc();

if (!$vacancy) {
    header('Location: vacancies.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    $max_applicants = max(0, (int)($_POST['max_applicants'] ?? 0)); // new field

    $update = $conn->prepare("UPDATE job_postings SET title=?, description=?, requirements=?, is_visible=?, max_applicants=? WHERE id=?");
    $update->bind_param('sssiii', $title, $description, $requirements, $is_visible, $max_applicants, $id);
    $update->execute();

    header('Location: vacancies.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Lowongan</title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            $menus = [
                'dashboard.php' => ['📊', 'Dashboard'],
                'applications.php' => ['📝', 'Lamaran'],
                'vacancies.php' => ['💼', 'Lowongan']
            ];
            foreach ($menus as $file => $item) {
                $isActive = $current_page === $file ? 'active' : '';
                echo "<a href='$file' class='$isActive'>{$item[0]} {$item[1]}</a>";
            }
            ?>
        </div>
        <div class="user-menu">
            <span class="admin-badge">👑 Admin</span>
        </div>
    </div>

    <div class="container">
        <div class="content-box">
            <div class="content-title">
                <span>✏️ Edit Lowongan</span>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Judul Posisi</label>
                    <input type="text" name="title" class="form-control" 
                        value="<?php echo htmlspecialchars($vacancy['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Pekerjaan</label>
                    <textarea name="description" class="form-control" required><?php 
                        echo htmlspecialchars($vacancy['description']); 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Persyaratan</label>
                    <textarea name="requirements" class="form-control" required><?php 
                        echo isset($vacancy['requirements']) ? htmlspecialchars($vacancy['requirements']) : ''; 
                    ?></textarea>
                </div>

                <!-- NEW: batas pelamar -->
                <div class="form-group">
                    <label class="form-label">Batas Pelamar (0 = tanpa batas)</label>
                    <input type="number" name="max_applicants" class="form-control" min="0"
                        value="<?php echo htmlspecialchars($vacancy['max_applicants'] ?? 0); ?>">
                    <small style="color:var(--text-light);">Masukkan jumlah maksimal pelamar untuk lowongan ini. Isi 0 jika tidak ingin membatasi.</small>
                </div>

                <div class="checkbox">
                    <input type="checkbox" name="is_visible" id="is_visible" 
                        <?php echo $vacancy['is_visible'] ? 'checked' : ''; ?>>
                    <label for="is_visible">Aktifkan lowongan ini</label>
                </div>

                <button type="submit" class="btn-save">💾 Simpan Perubahan</button>

                <!-- Tombol kembali pindah ke bawah -->
                <a href="vacancies.php" class="btn-back">← Kembali</a>
            </form>
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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-light);
        }

        /* ==== NAVBAR ==== */
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
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }

        .nav-menu a.active {
            background: #6688ee;
            box-shadow: 0 2px 8px rgba(102, 136, 238, 0.3);
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .admin-badge {
            background: rgba(255,255,255,0.1);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 15px;
        }

        /* ==== CONTENT ==== */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .content-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .content-title {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            color: var(--text-dark);
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Tombol kembali (bawah) */
        .btn-back {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-back:hover {
            background: #365fba;
        }

        /* ==== FORM ==== */
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
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }

        textarea.form-control {
            min-height: 130px;
            resize: vertical;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-save {
            background: var(--success);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background: #0b876a;
        }
    </style>
</body>
</html>
