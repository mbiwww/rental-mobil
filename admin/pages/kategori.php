<?php
// admin/pages/kategori.php
include '../../config/database.php';
include '../../includes/header_admin.php'; 
?>

<div class="d-flex">
    <?php include '../../includes/sidebar_admin.php'; ?>

    <div class="content-main p-4 w-100 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Kategori Mobil</h4>
                <p class="text-muted small">Kelola jenis armada yang tersedia</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </button>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-4 py-3 text-muted small">NO</th>
                                <th class="py-3 text-muted small">NAMA KATEGORI</th>
                                <th class="py-3 text-muted small text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr>
                                <td class="px-4">1</td>
                                <td class="fw-bold">SUV</td>
                                <td class="text-center">
                                    <button class="btn btn-light btn-sm text-primary me-1"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4">2</td>
                                <td class="fw-bold">Sedan</td>
                                <td class="text-center">
                                    <button class="btn btn-light btn-sm text-primary me-1"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../modules/kategori/proses_tambah.php" method="POST">
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold">Nama Kategori</label>
                    <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: MPV" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer_admin.php'; ?>