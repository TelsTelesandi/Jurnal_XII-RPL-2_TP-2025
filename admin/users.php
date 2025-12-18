<?php
/**
 * File: admin/users.php
 * Fungsi: Kelola User (Pelanggan & Staff)
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Handle Create/Update/Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, email, phone, company_name, address, role, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $_POST['username'],
            $hashedPassword,
            $_POST['full_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['company_name'],
            $_POST['address'],
            $_POST['role']
        ]);
        setFlashMessage('User berhasil ditambahkan!', 'success');
    } elseif ($action === 'update') {
        if (!empty($_POST['password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, phone = ?, company_name = ?, address = ?, role = ?, password = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['company_name'],
                $_POST['address'],
                $_POST['role'],
                $hashedPassword,
                $_POST['user_id']
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, phone = ?, company_name = ?, address = ?, role = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['company_name'],
                $_POST['address'],
                $_POST['role'],
                $_POST['user_id']
            ]);
        }
        setFlashMessage('User berhasil diupdate!', 'success');
    } elseif ($action === 'deactivate') {
        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
        setFlashMessage('User berhasil dinonaktifkan!', 'success');
    } elseif ($action === 'activate') {
        $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
        setFlashMessage('User berhasil diaktifkan!', 'success');
    }
    
    header('Location: users.php');
    exit;
}

// Filter
$roleFilter = $_GET['role'] ?? 'all';

// Get users
$sql = "SELECT * FROM users WHERE role != 'admin'";
if ($roleFilter !== 'all') {
    $sql .= " AND role = :role";
}
$sql .= " ORDER BY is_active DESC, created_at DESC";

$stmt = $pdo->prepare($sql);
if ($roleFilter !== 'all') {
    $stmt->bindValue(':role', $roleFilter);
}
$stmt->execute();
$users = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - RollMate Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-white">

    <div class="flex h-screen overflow-hidden">
        
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header -->
            <header class="flex-shrink-0 border-b border-slate-200 bg-white">
                <div class="px-8 py-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900">Kelola User</h2>
                        <p class="mt-1 text-sm text-slate-600">Manajemen pelanggan dan staff</p>
                    </div>
                    <button onclick="showCreateModal()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah User
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto bg-slate-50 p-8">

                <!-- Flash Message -->
                <?php if ($flashMessage): ?>
                <div class="mb-6 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100'; ?> border p-4">
                    <p class="text-sm font-medium <?php echo $flashMessage['type'] === 'success' ? 'text-green-800' : 'text-blue-800'; ?>">
                        <?php echo htmlspecialchars($flashMessage['message']); ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Filter -->
                <div class="mb-6 bg-white rounded-xl border border-slate-200 p-6">
                    <form method="GET" class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-slate-800">Filter Role:</label>
                        <select name="role" onchange="this.form.submit()" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>Semua</option>
                            <option value="pelanggan" <?php echo $roleFilter === 'pelanggan' ? 'selected' : ''; ?>>Pelanggan</option>
                            <option value="staff" <?php echo $roleFilter === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">User Info</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Kontak</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Perusahaan</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $u): ?>
                                    <tr class="hover:bg-slate-50 transition duration-150 <?php echo !$u['is_active'] ? 'opacity-60' : ''; ?>">
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($u['full_name']); ?></p>
                                            <p class="text-xs text-slate-500">@<?php echo htmlspecialchars($u['username']); ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-slate-900"><?php echo htmlspecialchars($u['email']); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($u['phone'] ?: '-'); ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-slate-900"><?php echo htmlspecialchars($u['company_name'] ?: '-'); ?></p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $u['role'] === 'pelanggan' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700'; ?>">
                                                <?php echo ucfirst($u['role']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                                <?php echo $u['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button onclick='editUser(<?php echo json_encode($u); ?>)' class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition duration-150">
                                                    Edit
                                                </button>
                                                <?php if ($u['is_active']): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" onclick="return confirm('Yakin ingin menonaktifkan user ini?')" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition duration-150">
                                                        Nonaktifkan
                                                    </button>
                                                </form>
                                                <?php else: ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="action" value="activate">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded transition duration-150">
                                                        Aktifkan
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <p class="text-sm text-slate-500">Tidak ada user ditemukan</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>

        </div>

    </div>

    <!-- Create/Edit Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h3 id="modalTitle" class="text-xl font-bold text-slate-900 mb-6">Tambah User Baru</h3>
            <form method="POST">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="user_id" id="userId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="username" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Password <span id="passwordRequired" class="text-red-500">*</span></label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <p id="passwordHint" class="hidden text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" id="fullName" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">No. Telepon</label>
                        <input type="text" name="phone" id="phone" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Perusahaan</label>
                        <input type="text" name="company_name" id="companyName" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="pelanggan">Pelanggan</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Alamat</label>
                    <textarea name="address" id="address" rows="2" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex space-x-3 mt-8">
                    <button type="button" onclick="hideUserModal()" class="flex-1 px-4 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition duration-150">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
                        Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateModal() {
            document.getElementById('modalTitle').textContent = 'Tambah User Baru';
            document.getElementById('formAction').value = 'create';
            document.getElementById('userId').value = '';
            document.getElementById('username').value = '';
            document.getElementById('username').readOnly = false;
            document.getElementById('password').value = '';
            document.getElementById('password').required = true;
            document.getElementById('passwordRequired').classList.remove('hidden');
            document.getElementById('passwordHint').classList.add('hidden');
            document.getElementById('fullName').value = '';
            document.getElementById('email').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('companyName').value = '';
            document.getElementById('role').value = 'pelanggan';
            document.getElementById('address').value = '';
            document.getElementById('userModal').classList.remove('hidden');
        }
        
        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('formAction').value = 'update';
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('username').readOnly = true;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('passwordRequired').classList.add('hidden');
            document.getElementById('passwordHint').classList.remove('hidden');
            document.getElementById('fullName').value = user.full_name;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('companyName').value = user.company_name || '';
            document.getElementById('role').value = user.role;
            document.getElementById('address').value = user.address || '';
            document.getElementById('userModal').classList.remove('hidden');
        }
        
        function hideUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
    </script>

</body>
</html>
