<?php

class BankAccountHandler extends BaseHandler
{
    private BankAccount $bankAccountModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->bankAccountModel = new BankAccount($db);
    }

    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'add_bank_account':         $this->addBankAccount();         break;
            case 'edit_bank_account':        $this->editBankAccount();        break;
            case 'toggle_bank_account_status': $this->toggleStatus();          break;
            case 'delete_bank_account':      $this->deleteBankAccount();      break;
            default:                          $this->redirect('../admin/transaksi.php?tab=rekening');
        }
    }

    private function addBankAccount(): void
    {
        $this->requireMethod('POST', '../admin/transaksi.php?tab=rekening');

        $bank_name       = trim($_POST['bank_name'] ?? '');
        $account_number  = trim($_POST['account_number'] ?? '');
        $account_holder  = trim($_POST['account_holder'] ?? '');
        $is_active       = intval($_POST['is_active'] ?? 1);

        if (empty($bank_name) || empty($account_number) || empty($account_holder)) {
            $this->flashError('Semua kolom rekening wajib diisi!');
            $this->redirect('../admin/transaksi.php?tab=rekening');
        }

        $this->flashRedirect(
            $this->bankAccountModel->create([
                'bank_name'      => $bank_name,
                'account_number' => $account_number,
                'account_holder' => $account_holder,
                'is_active'      => $is_active,
            ]),
            'Rekening bank baru berhasil ditambahkan!',
            'Gagal menambahkan rekening bank.',
            '../admin/transaksi.php?tab=rekening'
        );
    }

    private function editBankAccount(): void
    {
        $this->requireMethod('POST', '../admin/transaksi.php?tab=rekening');

        $id              = intval($_POST['id'] ?? 0);
        $bank_name       = trim($_POST['bank_name'] ?? '');
        $account_number  = trim($_POST['account_number'] ?? '');
        $account_holder  = trim($_POST['account_holder'] ?? '');
        $is_active       = intval($_POST['is_active'] ?? 1);

        if ($id <= 0 || empty($bank_name) || empty($account_number) || empty($account_holder)) {
            $this->flashError('Semua kolom edit rekening wajib diisi!');
            $this->redirect('../admin/transaksi.php?tab=rekening');
        }

        if (!$this->bankAccountModel->getById($id)) {
            $this->flashError('Rekening bank tidak ditemukan.');
            $this->redirect('../admin/transaksi.php?tab=rekening');
        }

        $this->flashRedirect(
            $this->bankAccountModel->update($id, [
                'bank_name'      => $bank_name,
                'account_number' => $account_number,
                'account_holder' => $account_holder,
                'is_active'      => $is_active,
            ]),
            'Data rekening bank berhasil diperbarui!',
            'Gagal memperbarui rekening bank.',
            '../admin/transaksi.php?tab=rekening'
        );
    }

    private function toggleStatus(): void
    {
        $this->requireMethod('GET', '../admin/transaksi.php?tab=rekening');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID rekening tidak valid.');
            $this->redirect('../admin/transaksi.php?tab=rekening');
        }

        $this->flashRedirect(
            $this->bankAccountModel->toggleStatus($id),
            'Status rekening bank berhasil diubah!',
            'Gagal mengubah status rekening bank.',
            '../admin/transaksi.php?tab=rekening'
        );
    }

    private function deleteBankAccount(): void
    {
        $this->requireMethod('GET', '../admin/transaksi.php?tab=rekening');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID rekening tidak valid.');
            $this->redirect('../admin/transaksi.php?tab=rekening');
        }

        $this->flashRedirect(
            $this->bankAccountModel->delete($id),
            'Rekening bank berhasil dihapus!',
            'Gagal menghapus rekening bank.',
            '../admin/transaksi.php?tab=rekening'
        );
    }
}
