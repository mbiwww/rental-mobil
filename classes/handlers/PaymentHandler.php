<?php

require_once __DIR__ . '/../Payment.php';

class PaymentHandler extends BaseHandler
{
    private Rental  $rentalModel;
    private Payment $paymentModel;
    private Car     $carModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->rentalModel  = new Rental($db);
        $this->paymentModel = new Payment($db);
        $this->carModel     = new Car($db);
    }

    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'reject_payment':         $this->rejectPayment();         break;
            case 'confirm_penalty_payment': $this->confirmPenaltyPayment(); break;
            case 'reject_penalty_payment':  $this->rejectPenaltyPayment();  break;
            default:                        $this->redirect('../admin/transaksi.php');
        }
    }

    private function rejectPayment(): void
    {
        $this->requireMethod('GET', '../admin/transaksi.php');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID rental tidak valid.');
            $this->redirect('../admin/transaksi.php');
        }

        $rental = $this->rentalModel->getById($id);
        if (!$rental) {
            $this->flashError('Transaksi tidak ditemukan.');
            $this->redirect('../admin/transaksi.php');
        }

        if ($rental['status'] !== 'pending') {
            $this->flashError('Transaksi ini tidak sedang menunggu pembayaran.');
            $this->redirect('../admin/transaksi.php');
        }

        try {
            $this->paymentModel->updateStatus($id, 'rejected');
            $this->flashSuccess('Bukti pembayaran berhasil ditolak. Pelanggan harus mengunggah bukti baru.');
        } catch (Exception $e) {
            $this->flashError('Gagal menolak pembayaran: ' . $e->getMessage());
        }
        $this->redirect('../admin/transaksi.php');
    }

    private function confirmPenaltyPayment(): void
    {
        $this->requireMethod('GET', '../admin/transaksi.php');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID rental tidak valid.');
            $this->redirect('../admin/transaksi.php');
        }

        $rental = $this->rentalModel->getById($id);
        if (!$rental) {
            $this->flashError('Transaksi tidak ditemukan.');
            $this->redirect('../admin/transaksi.php');
        }

        try {
            $stmt = $this->db->prepare("UPDATE rentals SET penalty_payment_status = 'confirmed' WHERE id = ?");
            $stmt->execute([$id]);
            $this->flashSuccess('Pembayaran denda berhasil diverifikasi dan ditandai lunas!');
        } catch (Exception $e) {
            $this->flashError('Gagal memproses persetujuan denda: ' . $e->getMessage());
        }
        $this->redirect('../admin/transaksi.php');
    }

    private function rejectPenaltyPayment(): void
    {
        $this->requireMethod('GET', '../admin/transaksi.php');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID rental tidak valid.');
            $this->redirect('../admin/transaksi.php');
        }

        $rental = $this->rentalModel->getById($id);
        if (!$rental) {
            $this->flashError('Transaksi tidak ditemukan.');
            $this->redirect('../admin/transaksi.php');
        }

        try {
            $this->db->beginTransaction();

            if (!empty($rental['penalty_proof_image'])) {
                $oldPath = '../assets/uploads/payments/' . $rental['penalty_proof_image'];
                if (file_exists($oldPath)) @unlink($oldPath);
            }

            $stmt = $this->db->prepare("UPDATE rentals SET penalty_payment_status = 'rejected', penalty_proof_image = NULL WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            $this->flashSuccess('Bukti pembayaran denda ditolak. Pelanggan dapat mengunggah kembali bukti pembayaran denda yang valid.');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flashError('Gagal menolak denda: ' . $e->getMessage());
        }
        $this->redirect('../admin/transaksi.php');
    }
}
