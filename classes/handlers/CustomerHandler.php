<?php

class CustomerHandler extends BaseHandler
{
    private User $userModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->userModel = new User($db);
    }

    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'delete_customer': $this->deleteCustomer(); break;
            default:                $this->redirect('../admin/customer.php');
        }
    }

    private function deleteCustomer(): void
    {
        $this->requireMethod('GET', '../admin/customer.php');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID customer tidak valid.');
            $this->redirect('../admin/customer.php');
        }

        $customer = $this->userModel->findById($id);
        if (!$customer || $customer['role'] !== 'customer') {
            $this->flashError('Customer tidak ditemukan.');
            $this->redirect('../admin/customer.php');
        }

        $activeRentals = $this->userModel->countActiveRentals($id);
        if ($activeRentals > 0) {
            $this->flashError('Tidak bisa menghapus! Customer ini masih memiliki ' . $activeRentals . ' rental aktif.');
            $this->redirect('../admin/customer.php');
        }

        $this->flashRedirect(
            $this->userModel->deleteCustomer($id),
            'Customer "' . $customer['name'] . '" berhasil dihapus!',
            'Gagal menghapus customer dari database.',
            '../admin/customer.php'
        );
    }
}
