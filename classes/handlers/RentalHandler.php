<?php

class RentalHandler extends BaseHandler
{
    private Rental  $rentalModel;
    private Car     $carModel;
    private Payment $paymentModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->rentalModel  = new Rental($db);
        $this->carModel     = new Car($db);
        $this->paymentModel = new Payment($db);
    }

    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'update_rental_status': $this->updateRentalStatus(); break;
            case 'return_rental':        $this->returnRental();       break;
            case 'approve_refund':       $this->approveRefund();      break;
            default:                     $this->redirect('../admin/transaksi.php');
        }
    }

    private function updateRentalStatus(): void
    {
        $this->requireMethod('GET', '../admin/transaksi.php');

        $id     = intval($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? '';

        $allowedStatuses = ['confirmed', 'ongoing', 'completed', 'cancelled'];
        if ($id <= 0 || !in_array($status, $allowedStatuses)) {
            $this->flashError('ID atau Status tidak valid.');
            $this->redirect('../admin/transaksi.php');
        }

        $rental = $this->rentalModel->getById($id);
        if (!$rental) {
            $this->flashError('Transaksi tidak ditemukan.');
            $this->redirect('../admin/transaksi.php');
        }

        $carId = (int)$rental['car_id'];

        try {
            $this->db->beginTransaction();

            if ($status === 'ongoing') {
                $this->rentalModel->startRental($id);
                $this->carModel->updateStatus($carId, 'rented');
            } elseif ($status === 'completed') {
                $penaltyInfo = $this->rentalModel->calculatePenalty($rental);

                if ($penaltyInfo['is_overdue'] && $rental['penalty_payment_status'] !== 'confirmed') {
                    $this->db->rollBack();
                    $this->flashError('Penyewaan tidak dapat diselesaikan karena customer terlambat mengembalikan mobil dan denda belum lunas diverifikasi.');
                    $this->redirect('../admin/transaksi.php');
                }

                $penaltyFee = ($rental['penalty_fee'] > 0) ? (float)$rental['penalty_fee'] : (float)$penaltyInfo['penalty_fee'];
                $this->rentalModel->completeRental($id, $penaltyFee);
                $this->carModel->updateStatus($carId, 'available');
            } elseif ($status === 'confirmed') {
                $this->rentalModel->updateStatus($id, 'confirmed');
                $this->paymentModel->updateStatus($id, 'confirmed');
            } elseif ($status === 'cancelled') {
                if ($rental['status'] === 'cancel_requested') {
                    $stmt = $this->db->prepare("UPDATE refunds SET status = 'approved' WHERE rental_id = ? AND status = 'requested'");
                    $stmt->execute([$id]);
                }
                $this->rentalModel->updateStatus($id, 'cancelled');
                $this->carModel->updateStatus($carId, 'available');
                $this->paymentModel->updateStatus($id, 'rejected');
            }

            $this->db->commit();
            $this->flashSuccess('Status transaksi berhasil diperbarui!');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flashError('Gagal memperbarui status transaksi: ' . $e->getMessage());
        }
        $this->redirect('../admin/transaksi.php');
    }

    private function returnRental(): void
    {
        $this->requireMethod('GET', '../admin/pengembalian.php');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID rental tidak valid.');
            $this->redirect('../admin/pengembalian.php');
        }

        $rental = $this->rentalModel->getById($id);
        if (!$rental) {
            $this->flashError('Rental tidak ditemukan.');
            $this->redirect('../admin/pengembalian.php');
        }

        if ($rental['status'] !== 'ongoing') {
            $this->flashError('Rental ini tidak dalam status ongoing.');
            $this->redirect('../admin/pengembalian.php');
        }

        $carId = (int)$rental['car_id'];

        try {
            $this->db->beginTransaction();

            $penaltyInfo = $this->rentalModel->calculatePenalty($rental);

            if ($penaltyInfo['is_overdue'] && $rental['penalty_payment_status'] !== 'confirmed') {
                $this->db->rollBack();
                $this->flashError('Pengembalian tidak dapat diproses karena customer terlambat mengembalikan mobil dan denda belum lunas diverifikasi.');
                $this->redirect('../admin/pengembalian.php');
            }

            $penaltyFee = ($rental['penalty_fee'] > 0) ? (float)$rental['penalty_fee'] : (float)$penaltyInfo['penalty_fee'];
            $this->rentalModel->completeRental($id, $penaltyFee);
            $this->carModel->updateStatus($carId, 'available');

            $this->db->commit();

            $msg = 'Pengembalian mobil ' . $rental['brand'] . ' ' . $rental['model'] . ' berhasil diproses!';
            if ($penaltyFee > 0) {
                $msg .= ' Denda keterlambatan: Rp ' . number_format($penaltyFee, 0, ',', '.');
            }
            $this->flashSuccess($msg);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flashError('Gagal memproses pengembalian: ' . $e->getMessage());
        }
        $this->redirect('../admin/pengembalian.php');
    }

    private function approveRefund(): void
    {
        $this->requireMethod('POST', '../admin/transaksi.php');

        $id = intval($_POST['rental_id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID rental tidak valid.');
            $this->redirect('../admin/transaksi.php');
        }

        $rental = $this->rentalModel->getById($id);
        if (!$rental) {
            $this->flashError('Transaksi tidak ditemukan.');
            $this->redirect('../admin/transaksi.php');
        }

        if ($rental['status'] !== 'cancel_requested') {
            $this->flashError('Transaksi ini tidak dalam status permintaan pembatalan.');
            $this->redirect('../admin/transaksi.php');
        }

        if (!isset($_FILES['refund_proof']) || $_FILES['refund_proof']['error'] !== UPLOAD_ERR_OK) {
            $this->flashError('Bukti transfer refund wajib diunggah!');
            $this->redirect('../admin/transaksi.php');
        }

        $file = $_FILES['refund_proof'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $this->flashError('Format gambar tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.');
            $this->redirect('../admin/transaksi.php');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->flashError('Ukuran file gambar terlalu besar. Maksimal 5MB.');
            $this->redirect('../admin/transaksi.php');
        }

        $uploadDir = '../assets/uploads/refunds/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('refund_') . '.' . $ext;
        $targetPath = $uploadDir . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->flashError('Gagal mengunggah bukti transfer refund.');
            $this->redirect('../admin/transaksi.php');
        }

        $carId = (int)$rental['car_id'];

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE refunds SET refund_proof_image = ?, status = 'approved' WHERE rental_id = ? AND status = 'requested'");
            $stmt->execute([$newFilename, $id]);

            $this->rentalModel->updateStatus($id, 'cancelled');
            $this->carModel->updateStatus($carId, 'available');
            $this->paymentModel->updateStatus($id, 'rejected');

            $this->db->commit();
            $this->flashSuccess('Refund berhasil disetujui dan bukti transfer telah diunggah!');
        } catch (Exception $e) {
            $this->db->rollBack();
            if (file_exists($targetPath)) @unlink($targetPath);
            $this->flashError('Gagal memproses refund: ' . $e->getMessage());
        }
        $this->redirect('../admin/transaksi.php');
    }
}
