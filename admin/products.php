<?php
/**
 * File: admin/products.php
 * Fungsi: Kelola Produk (CRUD) dengan Upload Gambar
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Handle Create/Update/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $imagePath = '';
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Debug info
            error_log("File upload detected: " . print_r($_FILES['image'], true));
            $uploadDir = '../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $newFilename = 'product_' . uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $newFilename;
            
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = 'uploads/products/' . $newFilename;
                    error_log("File moved successfully to: " . $targetPath);
                } else {
                    error_log("Failed to move file. Check permissions. Temp: " . $_FILES['image']['tmp_name'] . " -> " . $targetPath);
                }
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, category, description, price, unit, image, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['description'],
            $_POST['price'],
            $_POST['unit'],
            $imagePath
        ]);
        setFlashMessage('Produk berhasil ditambahkan!', 'success');
    } 
    elseif ($action === 'update') {
        $updateFields = [];
        $params = [];
        
        // Handle file upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $newFilename = 'product_' . uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $newFilename;
            
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $updateFields[] = 'image = ?';
                    $params[] = 'uploads/products/' . $newFilename;
                    
                    // Delete old image if exists
                    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                    $stmt->execute([$_POST['product_id']]);
                    $oldImage = $stmt->fetchColumn();
                    if ($oldImage && file_exists('../' . $oldImage)) {
                        unlink('../' . $oldImage);
                    }
                }
            }
        }
        
        // Add other fields to update
        $updateFields[] = 'name = ?';
        $updateFields[] = 'category = ?';
        $updateFields[] = 'description = ?';
        $updateFields[] = 'price = ?';
        $updateFields[] = 'unit = ?';
        
        $params[] = $_POST['name'];
        $params[] = $_POST['category'];
        $params[] = $_POST['description'];
        $params[] = $_POST['price'];
        $params[] = $_POST['unit'];
        $params[] = $_POST['product_id'];
        
        $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        setFlashMessage('Produk berhasil diupdate!', 'success');
    } 
    elseif ($action === 'delete') {
        // Get image path before deleting
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$_POST['product_id']]);
        $imagePath = $stmt->fetchColumn();
        
        // Delete product
        $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->execute([$_POST['product_id']]);
        
        // Delete image file if exists
        if ($imagePath && file_exists('../' . $imagePath)) {
            unlink('../' . $imagePath);
        }
        
        setFlashMessage('Produk berhasil dihapus!', 'success');
    }
    
    header('Location: products.php');
    exit;
}

// Get only active products (hide deleted/inactive ones)
$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC");
$products = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - RollMate Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
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
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900">Kelola Produk</h2>
                        <p class="mt-1 text-sm text-slate-600">Tambah, edit, atau hapus produk laminasi</p>
                    </div>
                    <button onclick="showCreateModal()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Produk
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

                <!-- Products Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-lg hover:border-blue-300 transition duration-300">
                        <!-- Product Image -->
                        <div class="h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($product['image']) && file_exists('../' . $product['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-20 h-20 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-6">
                            <div class="mb-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                    <?php echo htmlspecialchars($product['category']); ?>
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <p class="text-sm text-slate-600 mb-4 line-clamp-2">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>
                            <div class="flex items-end justify-between mb-4">
                                <div>
                                    <p class="text-2xl font-bold text-slate-900">
                                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">per <?php echo htmlspecialchars($product['unit']); ?></p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick='editProduct(<?php echo json_encode($product); ?>)' class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition duration-150">
                                    Edit
                                </button>
                                <form method="POST" class="flex-1">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" onclick="return confirm('Yakin ingin menghapus produk ini? Produk yang dihapus tidak dapat dikembalikan.')" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition duration-150">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h3 id="modalTitle" class="text-xl font-bold text-slate-900 mb-6">Tambah Produk Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="product_id" id="productId">
                
                <!-- Image Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Gambar Produk</label>
                    <div class="mt-1 flex items-center">
                        <div class="w-24 h-24 rounded-lg border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden" id="imagePreviewContainer">
                            <img id="imagePreview" src="" alt="Preview" class="hidden w-full h-full object-cover">
                            <svg id="defaultImageIcon" class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <label class="cursor-pointer bg-white py-2 px-3 border border-slate-300 rounded-md shadow-sm text-sm leading-4 font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Pilih Gambar
                                <input type="file" name="image" id="productImage" class="sr-only" accept="image/*">
                            </label>
                            <p class="mt-1 text-xs text-slate-500">Format: JPG, PNG, GIF, WebP (maks. 2MB)</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="productName" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="category" id="productCategory" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Deskripsi</label>
                    <textarea name="description" id="productDescription" rows="3" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Harga <span class="text-red-500">*</span></label>
                        <input type="number" name="price" id="productPrice" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 mb-2">Satuan <span class="text-red-500">*</span></label>
                        <input type="text" name="unit" id="productUnit" required placeholder="roll, meter, kg, dll" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex space-x-3 mt-8">
                    <button type="button" onclick="hideProductModal()" class="flex-1 px-4 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition duration-150">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Produk Baru';
            document.getElementById('formAction').value = 'create';
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productCategory').value = '';
            document.getElementById('productDescription').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productUnit').value = '';
            
            // Reset image preview
            const imagePreview = document.getElementById('imagePreview');
            const defaultImageIcon = document.getElementById('defaultImageIcon');
            imagePreview.src = '';
            imagePreview.classList.add('hidden');
            defaultImageIcon.classList.remove('hidden');
            document.getElementById('productImage').value = '';
            
            document.getElementById('productModal').classList.remove('hidden');
        }
        
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Produk';
            document.getElementById('formAction').value = 'update';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productUnit').value = product.unit;
            
            // Set image preview if exists
            const imagePreview = document.getElementById('imagePreview');
            const defaultImageIcon = document.getElementById('defaultImageIcon');
            
            if (product.image) {
                imagePreview.src = '../' + product.image;
                imagePreview.classList.remove('hidden');
                defaultImageIcon.classList.add('hidden');
            } else {
                imagePreview.src = '';
                imagePreview.classList.add('hidden');
                defaultImageIcon.classList.remove('hidden');
            }
            
            document.getElementById('productModal').classList.remove('hidden');
        }
        
        function hideProductModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        // Image preview functionality
        document.getElementById('productImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const imagePreview = document.getElementById('imagePreview');
                const defaultImageIcon = document.getElementById('defaultImageIcon');
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                    defaultImageIcon.classList.add('hidden');
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>