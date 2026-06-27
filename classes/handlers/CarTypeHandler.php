<?php

class CarTypeHandler extends BaseHandler
{
    private CarType $typeModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->typeModel = new CarType($db);
    }

    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'add_kategori':    $this->addKategori();    break;
            case 'edit_kategori':   $this->editKategori();   break;
            case 'delete_kategori': $this->deleteKategori(); break;
            default:                $this->redirect('../admin/kategori.php');
        }
    }

    private function addKategori(): void
    {
        $this->requireMethod('POST', '../admin/kategori.php');

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $this->flashError('Nama kategori tidak boleh kosong!');
            $this->redirect('../admin/kategori.php');
        }

        $this->flashRedirect(
            $this->typeModel->create($name),
            'Kategori "' . $name . '" berhasil ditambahkan!',
            'Gagal menambahkan kategori. Nama mungkin sudah ada.',
            '../admin/kategori.php'
        );
    }

    private function editKategori(): void
    {
        $this->requireMethod('POST', '../admin/kategori.php');

        $id   = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0 || empty($name)) {
            $this->flashError('Data tidak valid. ID dan nama wajib diisi.');
            $this->redirect('../admin/kategori.php');
        }

        if (!$this->typeModel->getById($id)) {
            $this->flashError('Kategori tidak ditemukan.');
            $this->redirect('../admin/kategori.php');
        }

        $this->flashRedirect(
            $this->typeModel->update($id, $name),
            'Kategori berhasil diperbarui menjadi "' . $name . '"!',
            'Gagal memperbarui kategori. Nama mungkin sudah ada.',
            '../admin/kategori.php'
        );
    }

    private function deleteKategori(): void
    {
        $this->requireMethod('GET', '../admin/kategori.php');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->flashError('ID kategori tidak valid.');
            $this->redirect('../admin/kategori.php');
        }

        $existing = $this->typeModel->getById($id);
        if (!$existing) {
            $this->flashError('Kategori tidak ditemukan.');
            $this->redirect('../admin/kategori.php');
        }

        $carCount = $this->typeModel->countCarsInType($id);
        if ($carCount > 0) {
            $this->flashError('Tidak bisa menghapus! Kategori ini masih digunakan oleh ' . $carCount . ' mobil.');
            $this->redirect('../admin/kategori.php');
        }

        $this->flashRedirect(
            $this->typeModel->delete($id),
            'Kategori "' . $existing['name'] . '" berhasil dihapus!',
            'Gagal menghapus kategori dari database.',
            '../admin/kategori.php'
        );
    }
}
