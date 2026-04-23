<?php
// admin/pages/armada.php - Manajemen Mobil RentalKu
include '../../config/database.php';
include '../../config/functions.php';
include '../../includes/header_admin.php'; 
?>

<div class="d-flex">
    <?php include '../../includes/sidebar_admin.php'; ?>

    <div class="content-main p-4 w-100 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Manajemen Armada</h4>
                <p class="text-muted small">Total 6 mobil terdaftar</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahMobil">
                <i class="bi bi-plus-lg"></i> Tambah Mobil
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-4 py-3 text-muted small">MOBIL</th>
                            <th class="py-3 text-muted small">KATEGORI</th>
                            <th class="py-3 text-muted small">TRANSMISI</th>
                            <th class="py-3 text-muted small">HARGA/HARI</th>
                            <th class="py-3 text-muted small">STATUS</th>
                            <th class="py-3 text-muted small text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="../../assets/img/mobil/fortuner.jpg" class="rounded-3" width="60" height="40" style="object-fit: cover;">
                                    <div>
                                        <p class="mb-0 fw-bold">Toyota Fortuner</p>
                                        <small class="text-muted">ID: MOB-001</small>
                                    </div>
                                </div>
                            </td>
                            <td>SUV</td>
                            <td>Automatic</td>
                            <td class="fw-bold text-primary">Rp 500.000</td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Available</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-light btn-sm text-primary me-1"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="../../assets/img/mobil/civic.jpg" class="rounded-3" width="60" height="40" style="object-fit: cover;">
                                    <div>
                                        <p class="mb-0 fw-bold">Honda Civic</p>
                                        <small class="text-muted">ID: MOB-002</small>
                                    </div>
                                </div>
                            </td>
                            <td>Sedan</td>
                            <td>Automatic</td>
                            <td class="fw-bold text-primary">Rp 400.000</td>
                            <td>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 text-dark">Rented</span>
                            </td>
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

<div class="modal fade" id="modalTambahMobil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Armada Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../modules/armada/proses_tambah.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Mobil</label>
                            <input type="text" name="nama_mobil" class="form-control" placeholder="Contoh: Toyota Avanza" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kategori</label>
                            <select name="kategori_id" class="form-select">
                                <option value="1">SUV</option>
                                <option value="2">Sedan</option>
                                <option value="3">MPV</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Transmisi</label>
                            <select name="transmisi" class="form-select">
                                <option value="Manual">Manual</option>
                                <option value="Automatic">Automatic</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Kapasitas Kursi</label>
                            <input type="number" name="kapasitas" class="form-control" placeholder="Contoh: 7" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Harga/Hari</label>
                            <input type="number" name="harga" class="form-control" placeholder="500000" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Foto Mobil</label>
                            <input type="file" name="foto" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Armada</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer_admin.php'; ?>