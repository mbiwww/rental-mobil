<?php

class SettingHandler extends BaseHandler
{
    private Setting $settingModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->settingModel = new Setting($db);
    }

    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'update_settings': $this->updateSettings(); break;
            default:                $this->redirect('../admin/transaksi.php');
        }
    }

    private function updateSettings(): void
    {
        $this->requireMethod('POST', '../admin/transaksi.php');

        $driverCost  = floatval($_POST['driver_cost_per_day'] ?? 0);
        $pickupFee   = floatval($_POST['pickup_fee'] ?? 0);
        $dropoffFee  = floatval($_POST['dropoff_fee'] ?? 0);
        $penaltyFee  = floatval($_POST['penalty_fee_per_hour'] ?? 0);

        if ($driverCost < 0 || $pickupFee < 0 || $dropoffFee < 0 || $penaltyFee < 0) {
            $this->flashError('Nilai biaya tidak boleh negatif!');
            $this->redirect('../admin/transaksi.php?tab=layanan');
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
            $stmt->execute([$driverCost, 'driver_cost_per_day']);
            $stmt->execute([$pickupFee, 'pickup_fee']);
            $stmt->execute([$dropoffFee, 'dropoff_fee']);
            $stmt->execute([$penaltyFee, 'penalty_fee_per_hour']);

            $this->db->commit();
            $this->flashSuccess('Pengaturan biaya layanan berhasil diperbarui!');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flashError('Gagal memperbarui pengaturan biaya: ' . $e->getMessage());
        }
        $this->redirect('../admin/transaksi.php?tab=layanan');
    }
}
