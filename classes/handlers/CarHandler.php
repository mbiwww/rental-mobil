<?php

class CarHandler extends BaseHandler
{
    private Car     $carModel;
    private CarType $typeModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->carModel  = new Car($db);
        $this->typeModel = new CarType($db);
    }

    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'add_car':          $this->addCar();          break;
            case 'edit_car':         $this->editCar();         break;
            case 'delete_car':       $this->deleteCar();       break;
            case 'update_status':    $this->updateStatus();    break;
            case 'update_car_price': $this->updateCarPrice();  break;
            default:                 $this->redirect('../admin/armada.php');
        }
    }

    private function addCar(): void
    {
        $this->requireMethod('POST', '../admin/armada.php');

        $data = [
            'brand'       => trim($_POST['brand'] ?? ''),
            'model'       => trim($_POST['model'] ?? ''),
            'year'        => intval($_POST['year'] ?? 0),
            'type_id'     => intval($_POST['type_id'] ?? 0),
            'plate_number' => strtoupper(trim($_POST['plate_number'] ?? '')),
            'engine_type' => trim($_POST['engine_type'] ?? ''),
            'transmission' => $_POST['transmission'] ?? 'manual',
            'passenger_capacity' => intval($_POST['passenger_capacity'] ?? 0),
            'luggage_capacity'   => intval($_POST['luggage_capacity'] ?? 0),
            'price_per_day' => floatval($_POST['price_per_day'] ?? 0),
            'status'       => $_POST['status'] ?? 'available',
            'description'  => trim($_POST['description'] ?? ''),
        ];

        if (empty($data['brand']) || empty($data['model']) || $data['year'] <= 0 || $data['price_per_day'] <= 0) {
            $this->flashError('Harap isi semua kolom wajib (Merk, Model, Tahun, Harga)!');
            $this->redirect('../admin/armada.php');
        }

        $uploadResult = $this->uploadImage($_FILES['image'] ?? []);
        if (is_string($uploadResult)) {
            $data['image'] = $uploadResult;
        } elseif (is_array($uploadResult)) {
            $this->flashError($uploadResult['error']);
            $this->redirect('../admin/armada.php');
        } else {
            $data['image'] = null;
        }

        $this->flashRedirect(
            $this->carModel->create($data),
            'Mobil baru berhasil ditambahkan!',
            'Gagal menyimpan data mobil ke database.',
            '../admin/armada.php'
        );
    }

    private function editCar(): void
    {
        $this->requireMethod('POST', '../admin/armada.php');

        $id = intval($_POST['id'] ?? 0);
        $data = [
            'brand'       => trim($_POST['brand'] ?? ''),
            'model'       => trim($_POST['model'] ?? ''),
            'year'        => intval($_POST['year'] ?? 0),
            'type_id'     => intval($_POST['type_id'] ?? 0),
            'plate_number' => strtoupper(trim($_POST['plate_number'] ?? '')),
            'engine_type' => trim($_POST['engine_type'] ?? ''),
            'transmission' => $_POST['transmission'] ?? 'manual',
            'passenger_capacity' => intval($_POST['passenger_capacity'] ?? 0),
            'luggage_capacity'   => intval($_POST['luggage_capacity'] ?? 0),
            'price_per_day' => floatval($_POST['price_per_day'] ?? 0),
            'status'       => $_POST['status'] ?? 'available',
            'description'  => trim($_POST['description'] ?? ''),
        ];

        if ($id <= 0 || empty($data['brand']) || empty($data['model']) || $data['year'] <= 0 || $data['price_per_day'] <= 0) {
            $this->flashError('Harap isi semua kolom wajib dengan benar!');
            $this->redirect('../admin/armada.php');
        }

        $currentCar = $this->carModel->getById($id);
        if (!$currentCar) {
            $this->flashError('Data mobil tidak ditemukan.');
            $this->redirect('../admin/armada.php');
        }

        $uploadResult = $this->uploadImage($_FILES['image'] ?? []);
        if (is_string($uploadResult)) {
            $data['image'] = $uploadResult;
            if (!empty($currentCar['image'])) {
                $oldPath = '../assets/uploads/cars/' . $currentCar['image'];
                if (file_exists($oldPath)) @unlink($oldPath);
            }
        } elseif (is_array($uploadResult)) {
            $this->flashError($uploadResult['error']);
            $this->redirect('../admin/armada.php');
        }

        $this->flashRedirect(
            $this->carModel->update($id, $data),
            'Data mobil berhasil diperbarui!',
            'Gagal memperbarui data mobil ke database.',
            '../admin/armada.php'
        );
    }

    private function deleteCar(): void
    {
        $this->requireMethod('GET', '../admin/armada.php');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID mobil tidak valid.');
            $this->redirect('../admin/armada.php');
        }

        $currentCar = $this->carModel->getById($id);
        if (!$currentCar) {
            $this->flashError('Data mobil tidak ditemukan.');
            $this->redirect('../admin/armada.php');
        }

        if (!empty($currentCar['image'])) {
            $imagePath = '../assets/uploads/cars/' . $currentCar['image'];
            if (file_exists($imagePath)) @unlink($imagePath);
        }

        $this->flashRedirect(
            $this->carModel->delete($id),
            'Mobil berhasil dihapus!',
            'Gagal menghapus mobil dari database.',
            '../admin/armada.php'
        );
    }

    private function updateStatus(): void
    {
        $this->requireMethod('GET', '../admin/armada.php');

        $id     = intval($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? '';

        $allowed = ['available', 'rented', 'maintenance'];
        if ($id <= 0 || !in_array($status, $allowed)) {
            $this->flashError('ID atau Status tidak valid.');
            $this->redirect('../admin/armada.php');
        }

        $this->flashRedirect(
            $this->carModel->updateStatus($id, $status),
            'Status mobil berhasil diperbarui!',
            'Gagal memperbarui status mobil.',
            '../admin/armada.php'
        );
    }

    private function updateCarPrice(): void
    {
        $this->requireMethod('POST', '../admin/transaksi.php');

        $carId       = intval($_POST['car_id'] ?? 0);
        $pricePerDay = floatval($_POST['price_per_day'] ?? 0);

        if ($carId <= 0 || $pricePerDay <= 0) {
            $this->flashError('ID mobil atau harga tidak valid!');
            $this->redirect('../admin/transaksi.php?tab=mobil');
        }

        $currentCar = $this->carModel->getById($carId);
        if (!$currentCar) {
            $this->flashError('Data mobil tidak ditemukan.');
            $this->redirect('../admin/transaksi.php?tab=mobil');
        }

        try {
            $stmt = $this->db->prepare("UPDATE cars SET price_per_day = ? WHERE id = ?");
            $stmt->execute([$pricePerDay, $carId]);
            $this->flashSuccess('Harga sewa mobil ' . $currentCar['brand'] . ' ' . $currentCar['model'] . ' berhasil diperbarui!');
        } catch (Exception $e) {
            $this->flashError('Gagal memperbarui harga sewa mobil: ' . $e->getMessage());
        }
        $this->redirect('../admin/transaksi.php?tab=mobil');
    }

    private function uploadImage(array $file): string|array|null
    {
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            return ['error' => 'Format gambar tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.'];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['error' => 'Ukuran file gambar terlalu besar. Maksimal 2MB.'];
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('car_') . '.' . $ext;
        $targetPath = '../assets/uploads/cars/' . $newFilename;

        if (!is_dir('../assets/uploads/cars/')) {
            mkdir('../assets/uploads/cars/', 0755, true);
        }

        return move_uploaded_file($file['tmp_name'], $targetPath) ? $newFilename : ['error' => 'Gagal mengunggah file ke server.'];
    }
}
