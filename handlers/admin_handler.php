<?php
/**
 * Handler Admin — Memproses semua aksi POST/GET untuk manajemen armada
 * 
 * Hanya berisi logika backend PHP untuk memproses database dan file, 
 * kemudian melakukan redirect kembali ke admin/armada.php dengan query status.
 */

require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Car.php';
require_once '../classes/CarType.php';
require_once '../classes/Rental.php';
require_once '../classes/Payment.php';
require_once '../classes/User.php';
require_once '../classes/BankAccount.php';

$db = Database::getInstance()->getConnection();
$carModel = new Car($db);
$typeModel = new CarType($db);
$rentalModel = new Rental($db);
$paymentModel = new Payment($db);
$userModel = new User($db);
$bankAccountModel = new BankAccount($db);

// Menentukan aksi yang dikirimkan (dari POST atau GET)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Helper untuk mengupload gambar mobil ke folder assets/uploads/cars/
 * 
 * @param array $file Array dari $_FILES['image']
 * @param string $uploadDir Jalur folder penyimpanan
 * @return string|array Mengembalikan nama file baru atau array berisi error
 */
function uploadImage($file, $uploadDir = '../assets/uploads/cars/')
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
    $plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
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
        'plate_number' => $plate_number,
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
    $plate_number = strtoupper(trim($_POST['plate_number'] ?? ''));
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
            $oldImagePath = '../assets/uploads/cars/' . $currentCar['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }

    $carData = [
        'type_id' => $type_id,
        'plate_number' => $plate_number,
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
        $imagePath = '../assets/uploads/cars/' . $currentCar['image'];
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

// ==========================================
// AKSI 8: UPDATE STATUS TRANSAKSI / RENTAL (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'update_rental_status') {
    $id = intval($_GET['id'] ?? 0);
    $status = $_GET['status'] ?? '';

    $allowedStatuses = ['confirmed', 'ongoing', 'completed', 'cancelled'];
    if ($id <= 0 || !in_array($status, $allowedStatuses)) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('ID atau Status tidak valid.'));
        exit;
    }

    // Get the rental details first
    $rental = $rentalModel->getById($id);
    if (!$rental) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Transaksi tidak ditemukan.'));
        exit;
    }

    $carId = (int)$rental['car_id'];
    $db->beginTransaction();

    try {
        // 1. Update Rental Status
        if ($status === 'ongoing') {
            // Mulai sewa — catat started_at (waktu mulai sewa)
            $rentalModel->startRental($id);
            // Ubah status mobil ke rented
            $carModel->updateStatus($carId, 'rented');

        } elseif ($status === 'completed') {
            // Selesaikan sewa — hitung denda jika terlambat
            $penaltyInfo = $rentalModel->calculatePenalty($rental);
            $penaltyFee  = (float) $penaltyInfo['penalty_fee'];

            // Simpan status completed + actual_return_at + penalty_fee
            $rentalModel->completeRental($id, $penaltyFee);

            // Ubah status mobil ke available
            $carModel->updateStatus($carId, 'available');

        } elseif ($status === 'confirmed') {
            $rentalModel->updateStatus($id, 'confirmed');
            // Konfirmasi pembayaran terkait jika ada
            $paymentModel->updateStatus($id, 'confirmed');

        } elseif ($status === 'cancelled') {
            // Jika rental sebelumnya cancel_requested (refund), update status refund menjadi approved
            if ($rental['status'] === 'cancel_requested') {
                $stmtRefund = $db->prepare("UPDATE refunds SET status = 'approved' WHERE rental_id = ? AND status = 'requested'");
                $stmtRefund->execute([$id]);
            }

            $rentalModel->updateStatus($id, 'cancelled');
            // Ubah status mobil ke available
            $carModel->updateStatus($carId, 'available');
            // Tolak pembayaran terkait jika ada
            $paymentModel->updateStatus($id, 'rejected');
        }

        $db->commit();
        header('Location: ../admin/transaksi.php?status=success&msg=' . urlencode('Status transaksi berhasil diperbarui!'));
    } catch (Exception $e) {
        $db->rollBack();
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Gagal memperbarui status transaksi: ' . $e->getMessage()));
    }
    exit;
}

// ==========================================
// AKSI 9: UPDATE HARGA MOBIL INDIVIDU (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_car_price') {
    $carId = intval($_POST['car_id'] ?? 0);
    $pricePerDay = floatval($_POST['price_per_day'] ?? 0);

    if ($carId <= 0 || $pricePerDay <= 0) {
        header('Location: ../admin/transaksi.php?tab=mobil&status=error&msg=' . urlencode('ID mobil atau harga tidak valid!'));
        exit;
    }

    $currentCar = $carModel->getById($carId);
    if (!$currentCar) {
        header('Location: ../admin/transaksi.php?tab=mobil&status=error&msg=' . urlencode('Data mobil tidak ditemukan.'));
        exit;
    }

    try {
        $stmt = $db->prepare("UPDATE cars SET price_per_day = ? WHERE id = ?");
        $stmt->execute([$pricePerDay, $carId]);
        header('Location: ../admin/transaksi.php?tab=mobil&status=success&msg=' . urlencode('Harga sewa mobil ' . $currentCar['brand'] . ' ' . $currentCar['model'] . ' berhasil diperbarui!'));
    } catch (Exception $e) {
        header('Location: ../admin/transaksi.php?tab=mobil&status=error&msg=' . urlencode('Gagal memperbarui harga sewa mobil: ' . $e->getMessage()));
    }
    exit;
}

// ==========================================
// AKSI 10: UPDATE PENGATURAN BIAYA & DRIVER (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_settings') {
    $driverCost = floatval($_POST['driver_cost_per_day'] ?? 0);
    $pickupFee = floatval($_POST['pickup_fee'] ?? 0);
    $dropoffFee = floatval($_POST['dropoff_fee'] ?? 0);
    $penaltyFee = floatval($_POST['penalty_fee_per_hour'] ?? 0);

    if ($driverCost < 0 || $pickupFee < 0 || $dropoffFee < 0 || $penaltyFee < 0) {
        header('Location: ../admin/transaksi.php?tab=layanan&status=error&msg=' . urlencode('Nilai biaya tidak boleh negatif!'));
        exit;
    }

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
        $stmt->execute([$driverCost, 'driver_cost_per_day']);
        $stmt->execute([$pickupFee, 'pickup_fee']);
        $stmt->execute([$dropoffFee, 'dropoff_fee']);
        $stmt->execute([$penaltyFee, 'penalty_fee_per_hour']);

        $db->commit();
        header('Location: ../admin/transaksi.php?tab=layanan&status=success&msg=' . urlencode('Pengaturan biaya layanan berhasil diperbarui!'));
    } catch (Exception $e) {
        $db->rollBack();
        header('Location: ../admin/transaksi.php?tab=layanan&status=error&msg=' . urlencode('Gagal memperbarui pengaturan biaya: ' . $e->getMessage()));
    }
    exit;
}

// ==========================================
// AKSI 11: HAPUS CUSTOMER (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete_customer') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../admin/customer.php?status=error&msg=' . urlencode('ID customer tidak valid.'));
        exit;
    }

    // Pastikan customer ada
    $customer = $userModel->findById($id);
    if (!$customer || $customer['role'] !== 'customer') {
        header('Location: ../admin/customer.php?status=error&msg=' . urlencode('Customer tidak ditemukan.'));
        exit;
    }

    // Cek apakah customer memiliki rental aktif
    $activeRentals = $userModel->countActiveRentals($id);
    if ($activeRentals > 0) {
        header('Location: ../admin/customer.php?status=error&msg=' . urlencode('Tidak bisa menghapus! Customer ini masih memiliki ' . $activeRentals . ' rental aktif.'));
        exit;
    }

    if ($userModel->deleteCustomer($id)) {
        header('Location: ../admin/customer.php?status=success&msg=' . urlencode('Customer "' . $customer['name'] . '" berhasil dihapus!'));
    } else {
        header('Location: ../admin/customer.php?status=error&msg=' . urlencode('Gagal menghapus customer dari database.'));
    }
    exit;
}

// ==========================================
// AKSI 12: PROSES PENGEMBALIAN MOBIL (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'return_rental') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../admin/pengembalian.php?status=error&msg=' . urlencode('ID rental tidak valid.'));
        exit;
    }

    $rental = $rentalModel->getById($id);
    if (!$rental) {
        header('Location: ../admin/pengembalian.php?status=error&msg=' . urlencode('Rental tidak ditemukan.'));
        exit;
    }

    if ($rental['status'] !== 'ongoing') {
        header('Location: ../admin/pengembalian.php?status=error&msg=' . urlencode('Rental ini tidak dalam status ongoing.'));
        exit;
    }

    $carId = (int)$rental['car_id'];
    $db->beginTransaction();

    try {
        // Hitung denda keterlambatan
        $penaltyInfo = $rentalModel->calculatePenalty($rental);
        $penaltyFee  = (float) $penaltyInfo['penalty_fee'];

        // Selesaikan rental
        $rentalModel->completeRental($id, $penaltyFee);

        // Ubah status mobil ke available
        $carModel->updateStatus($carId, 'available');

        $db->commit();

        $msg = 'Pengembalian mobil ' . $rental['brand'] . ' ' . $rental['model'] . ' berhasil diproses!';
        if ($penaltyFee > 0) {
            $msg .= ' Denda keterlambatan: Rp ' . number_format($penaltyFee, 0, ',', '.');
        }
        header('Location: ../admin/pengembalian.php?status=success&msg=' . urlencode($msg));
    } catch (Exception $e) {
        $db->rollBack();
        header('Location: ../admin/pengembalian.php?status=error&msg=' . urlencode('Gagal memproses pengembalian: ' . $e->getMessage()));
    }
    exit;
}

// ==========================================
// AKSI 13: TAMBAH REKENING BANK (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_bank_account') {
    $bank_name = trim($_POST['bank_name'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $account_holder = trim($_POST['account_holder'] ?? '');
    $is_active = intval($_POST['is_active'] ?? 1);

    if (empty($bank_name) || empty($account_number) || empty($account_holder)) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('Semua kolom rekening wajib diisi!'));
        exit;
    }

    $bankData = [
        'bank_name' => $bank_name,
        'account_number' => $account_number,
        'account_holder' => $account_holder,
        'is_active' => $is_active
    ];

    if ($bankAccountModel->create($bankData)) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=success&msg=' . urlencode('Rekening bank baru berhasil ditambahkan!'));
    } else {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('Gagal menambahkan rekening bank.'));
    }
    exit;
}

// ==========================================
// AKSI 14: EDIT REKENING BANK (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_bank_account') {
    $id = intval($_POST['id'] ?? 0);
    $bank_name = trim($_POST['bank_name'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $account_holder = trim($_POST['account_holder'] ?? '');
    $is_active = intval($_POST['is_active'] ?? 1);

    if ($id <= 0 || empty($bank_name) || empty($account_number) || empty($account_holder)) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('Semua kolom edit rekening wajib diisi!'));
        exit;
    }

    $existing = $bankAccountModel->getById($id);
    if (!$existing) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('Rekening bank tidak ditemukan.'));
        exit;
    }

    $bankData = [
        'bank_name' => $bank_name,
        'account_number' => $account_number,
        'account_holder' => $account_holder,
        'is_active' => $is_active
    ];

    if ($bankAccountModel->update($id, $bankData)) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=success&msg=' . urlencode('Data rekening bank berhasil diperbarui!'));
    } else {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('Gagal memperbarui rekening bank.'));
    }
    exit;
}

// ==========================================
// AKSI 15: TOGGLE STATUS REKENING (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'toggle_bank_account_status') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('ID rekening tidak valid.'));
        exit;
    }

    if ($bankAccountModel->toggleStatus($id)) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=success&msg=' . urlencode('Status rekening bank berhasil diubah!'));
    } else {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('Gagal mengubah status rekening bank.'));
    }
    exit;
}

// ==========================================
// AKSI 16: HAPUS REKENING BANK (GET)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete_bank_account') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('ID rekening tidak valid.'));
        exit;
    }

    if ($bankAccountModel->delete($id)) {
        header('Location: ../admin/transaksi.php?tab=rekening&status=success&msg=' . urlencode('Rekening bank berhasil dihapus!'));
    } else {
        header('Location: ../admin/transaksi.php?tab=rekening&status=error&msg=' . urlencode('Gagal menghapus rekening bank.'));
    }
    exit;
}

// ==========================================
// AKSI 17: SETUJUI REFUND DENGAN BUKTI TRANSFER (POST)
// ==========================================
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'approve_refund') {
    $id = intval($_POST['rental_id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('ID rental tidak valid.'));
        exit;
    }

    // Ambil data rental
    $rental = $rentalModel->getById($id);
    if (!$rental) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Transaksi tidak ditemukan.'));
        exit;
    }

    // Pastikan rental berstatus cancel_requested
    if ($rental['status'] !== 'cancel_requested') {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Transaksi ini tidak dalam status permintaan pembatalan.'));
        exit;
    }

    // Validasi file bukti transfer refund (wajib)
    if (!isset($_FILES['refund_proof']) || $_FILES['refund_proof']['error'] !== UPLOAD_ERR_OK) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Bukti transfer refund wajib diunggah!'));
        exit;
    }

    $file = $_FILES['refund_proof'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $fileType = mime_content_type($file['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Format gambar tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.'));
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Ukuran file gambar terlalu besar. Maksimal 5MB.'));
        exit;
    }

    $uploadDir = '../assets/uploads/refunds/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = uniqid('refund_') . '.' . $ext;
    $targetPath = $uploadDir . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Gagal mengunggah bukti transfer refund.'));
        exit;
    }

    $carId = (int)$rental['car_id'];
    $db->beginTransaction();

    try {
        // 1. Update refund record: set proof image dan status approved
        $stmtRefund = $db->prepare("UPDATE refunds SET refund_proof_image = ?, status = 'approved' WHERE rental_id = ? AND status = 'requested'");
        $stmtRefund->execute([$newFilename, $id]);

        // 2. Update rental status ke cancelled
        $rentalModel->updateStatus($id, 'cancelled');

        // 3. Ubah status mobil ke available
        $carModel->updateStatus($carId, 'available');

        // 4. Tolak pembayaran terkait
        $paymentModel->updateStatus($id, 'rejected');

        $db->commit();
        header('Location: ../admin/transaksi.php?status=success&msg=' . urlencode('Refund berhasil disetujui dan bukti transfer telah diunggah!'));
    } catch (Exception $e) {
        $db->rollBack();
        // Hapus file yang sudah terupload jika transaksi gagal
        if (file_exists($targetPath)) {
            @unlink($targetPath);
        }
        header('Location: ../admin/transaksi.php?status=error&msg=' . urlencode('Gagal memproses refund: ' . $e->getMessage()));
    }
    exit;
}

// Jika request tidak dikenali, redirect kembali
header('Location: ../admin/armada.php');
exit;
