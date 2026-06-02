<?php
/**
 * Handler Admin — Memproses semua aksi POST/GET untuk manajemen armada
 * 
 * Hanya berisi logika backend PHP untuk memproses database dan file, 
 * kemudian melakukan redirect kembali ke admin/armada.php dengan query status.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// MOCK SESSION ADMIN (Untuk kemudahan testing selama masa pengembangan)
// Hapus atau sesuaikan bagian ini jika handlers/auth_handler.php sudah diimplementasi
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
    $_SESSION['name'] = 'Admin Tester';
}

// Cek otentikasi admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Car.php';
require_once '../classes/CarType.php';

$db = Database::getInstance()->getConnection();
$carModel = new Car($db);
$typeModel = new CarType($db);

// Menentukan aksi yang dikirimkan (dari POST atau GET)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Helper untuk mengupload gambar mobil ke folder assets/uploads/
 * 
 * @param array $file Array dari $_FILES['image']
 * @param string $uploadDir Jalur folder penyimpanan
 * @return string|array Mengembalikan nama file baru atau array berisi error
 */
function uploadImage($file, $uploadDir = '../assets/uploads/')
{
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validasi tipe file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        return ['error' => 'Format gambar tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.'];
    }
    
    // Validasi ukuran file (maksimal 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['error' => 'Ukuran file gambar terlalu besar. Maksimal 2MB.'];
    }
    
    // Mengubah nama file dengan uniqid() + ekstensi asli
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = uniqid('car_') . '.' . $ext;
    $targetPath = $uploadDir . $newFilename;
    
    // Membuat direktori jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFilename;
    }
    
    return ['error' => 'Gagal mengunggah file ke server.'];
}

// ==========================================
// AKSI 1: TAMBAH MOBIL (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_car') {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $type_id = intval($_POST['type_id'] ?? 0);
    $engine_type = trim($_POST['engine_type'] ?? '');
    $transmission = $_POST['transmission'] ?? 'manual';
    $passenger_capacity = intval($_POST['passenger_capacity'] ?? 0);
    $luggage_capacity = intval($_POST['luggage_capacity'] ?? 0);
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $description = trim($_POST['description'] ?? '');

    // Validasi input wajib
    if (empty($brand) || empty($model) || $year <= 0 || $price_per_day <= 0) {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Harap isi semua kolom wajib (Merk, Model, Tahun, Harga)!'));
        exit;
    }

    // Proses upload gambar jika ada
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if (is_array($uploadResult) && isset($uploadResult['error'])) {
            header('Location: ../admin/armada.php?status=error&msg=' . urlencode($uploadResult['error']));
            exit;
        }
        $imageName = $uploadResult;
    }

    $carData = [
        'type_id' => $type_id,
        'brand' => $brand,
        'model' => $model,
        'year' => $year,
        'engine_type' => $engine_type,
        'transmission' => $transmission,
        'passenger_capacity' => $passenger_capacity,
        'luggage_capacity' => $luggage_capacity,
        'price_per_day' => $price_per_day,
        'status' => $status,
        'image' => $imageName,
        'description' => $description
    ];

    if ($carModel->create($carData)) {
        header('Location: ../admin/armada.php?status=success&msg=' . urlencode('Mobil baru berhasil ditambahkan!'));
    } else {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Gagal menyimpan data mobil ke database.'));
    }
    exit;
}

// ==========================================
// AKSI 2: EDIT MOBIL (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_car') {
    $id = intval($_POST['id'] ?? 0);
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $type_id = intval($_POST['type_id'] ?? 0);
    $engine_type = trim($_POST['engine_type'] ?? '');
    $transmission = $_POST['transmission'] ?? 'manual';
    $passenger_capacity = intval($_POST['passenger_capacity'] ?? 0);
    $luggage_capacity = intval($_POST['luggage_capacity'] ?? 0);
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $description = trim($_POST['description'] ?? '');

    // Validasi input wajib
    if ($id <= 0 || empty($brand) || empty($model) || $year <= 0 || $price_per_day <= 0) {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Harap isi semua kolom wajib dengan benar!'));
        exit;
    }

    // Cek keberadaan mobil
    $currentCar = $carModel->getById($id);
    if (!$currentCar) {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Data mobil tidak ditemukan.'));
        exit;
    }

    // Proses upload gambar baru jika ada
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if (is_array($uploadResult) && isset($uploadResult['error'])) {
            header('Location: ../admin/armada.php?status=error&msg=' . urlencode($uploadResult['error']));
            exit;
        }
        $imageName = $uploadResult;

        // Hapus file gambar lama jika ada agar tidak menumpuk di server
        if (!empty($currentCar['image'])) {
            $oldImagePath = '../assets/uploads/' . $currentCar['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }

    $carData = [
        'type_id' => $type_id,
        'brand' => $brand,
        'model' => $model,
        'year' => $year,
        'engine_type' => $engine_type,
        'transmission' => $transmission,
        'passenger_capacity' => $passenger_capacity,
        'luggage_capacity' => $luggage_capacity,
        'price_per_day' => $price_per_day,
        'status' => $status,
        'image' => $imageName, // Jika null, di class Car tidak akan diubah di SQL
        'description' => $description
    ];

    if ($carModel->update($id, $carData)) {
        header('Location: ../admin/armada.php?status=success&msg=' . urlencode('Data mobil berhasil diperbarui!'));
    } else {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Gagal memperbarui data mobil ke database.'));
    }
    exit;
}

// ==========================================
// AKSI 3: HAPUS MOBIL (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete_car') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('ID mobil tidak valid.'));
        exit;
    }

    // Cek keberadaan mobil
    $currentCar = $carModel->getById($id);
    if (!$currentCar) {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Data mobil tidak ditemukan.'));
        exit;
    }

    // Hapus file gambar dari server
    if (!empty($currentCar['image'])) {
        $imagePath = '../assets/uploads/' . $currentCar['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    if ($carModel->delete($id)) {
        header('Location: ../admin/armada.php?status=success&msg=' . urlencode('Mobil berhasil dihapus!'));
    } else {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Gagal menghapus mobil dari database.'));
    }
    exit;
}

// ==========================================
// AKSI 4: UPDATE STATUS INLINE (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'update_status') {
    $id = intval($_GET['id'] ?? 0);
    $status = $_GET['status'] ?? '';

    $allowedStatuses = ['available', 'rented', 'maintenance'];
    if ($id <= 0 || !in_array($status, $allowedStatuses)) {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('ID atau Status tidak valid.'));
        exit;
    }

    if ($carModel->updateStatus($id, $status)) {
        header('Location: ../admin/armada.php?status=success&msg=' . urlencode('Status mobil berhasil diperbarui!'));
    } else {
        header('Location: ../admin/armada.php?status=error&msg=' . urlencode('Gagal memperbarui status mobil.'));
    }
    exit;
}

// ==========================================
// AKSI 5: TAMBAH KATEGORI (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_kategori') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Nama kategori tidak boleh kosong!'));
        exit;
    }

    if ($typeModel->create($name)) {
        header('Location: ../admin/kategori.php?status=success&msg=' . urlencode('Kategori "' . $name . '" berhasil ditambahkan!'));
    } else {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Gagal menambahkan kategori. Nama mungkin sudah ada.'));
    }
    exit;
}

// ==========================================
// AKSI 6: EDIT KATEGORI (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_kategori') {
    $id   = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');

    if ($id <= 0 || empty($name)) {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Data tidak valid. ID dan nama wajib diisi.'));
        exit;
    }

    // Pastikan kategori ada
    $existing = $typeModel->getById($id);
    if (!$existing) {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Kategori tidak ditemukan.'));
        exit;
    }

    if ($typeModel->update($id, $name)) {
        header('Location: ../admin/kategori.php?status=success&msg=' . urlencode('Kategori berhasil diperbarui menjadi "' . $name . '"!'));
    } else {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Gagal memperbarui kategori. Nama mungkin sudah ada.'));
    }
    exit;
}

// ==========================================
// AKSI 7: HAPUS KATEGORI (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete_kategori') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('ID kategori tidak valid.'));
        exit;
    }

    // Pastikan kategori ada
    $existing = $typeModel->getById($id);
    if (!$existing) {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Kategori tidak ditemukan.'));
        exit;
    }

    // Cegah penghapusan jika masih ada mobil yang menggunakan kategori ini
    $carCount = $typeModel->countCarsInType($id);
    if ($carCount > 0) {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Tidak bisa menghapus! Kategori ini masih digunakan oleh ' . $carCount . ' mobil.'));
        exit;
    }

    if ($typeModel->delete($id)) {
        header('Location: ../admin/kategori.php?status=success&msg=' . urlencode('Kategori "' . $existing['name'] . '" berhasil dihapus!'));
    } else {
        header('Location: ../admin/kategori.php?status=error&msg=' . urlencode('Gagal menghapus kategori dari database.'));
    }
    exit;
}

// Jika request tidak dikenali, redirect kembali
header('Location: ../admin/armada.php');
exit;
