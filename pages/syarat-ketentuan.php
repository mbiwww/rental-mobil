<?php
/**
 * pages/syarat-ketentuan.php — Halaman Syarat dan Ketentuan RentalQu
 *
 * Menampilkan syarat dan ketentuan layanan sewa mobil RentalQu.
 * Konten disesuaikan dengan fitur dan kebijakan yang ada di sistem:
 * - Booking & pembayaran via transfer bank
 * - Opsi supir / tanpa supir
 * - Pickup & dropoff
 * - Kebijakan denda keterlambatan
 * - Pembatalan & refund
 */

session_start();

require_once '../config/database.php';
require_once '../classes/Database.php';

$pdo = Database::getInstance()->getConnection();

// Ambil setting dari database untuk menampilkan info biaya aktual
$stmtSettings = $pdo->query("SELECT key_name, value FROM settings");
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);
$driverFee   = isset($settings['driver_cost_per_day']) ? (float)$settings['driver_cost_per_day'] : 200000.0;
$pickupFee   = isset($settings['pickup_fee']) ? (float)$settings['pickup_fee'] : 50000.0;
$dropoffFee  = isset($settings['dropoff_fee']) ? (float)$settings['dropoff_fee'] : 50000.0;
$penaltyFee  = isset($settings['penalty_fee_per_hour']) ? (float)$settings['penalty_fee_per_hour'] : 40000.0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalQu &middot; Syarat dan Ketentuan</title>
  <meta name="description" content="Syarat dan ketentuan layanan sewa mobil RentalQu. Baca sebelum melakukan pemesanan.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/syarat-ketentuan.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/flash.css">
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/flash.php'; ?>

  <!-- Hero Banner -->
  <div class="tnc-hero">
    <div class="container">
      <div class="tnc-hero-content">
        <div class="tnc-hero-icon">
          <i class="bi bi-shield-check"></i>
        </div>
        <h1 class="tnc-hero-title">Syarat dan Ketentuan</h1>
        <p class="tnc-hero-desc">Harap baca dengan saksama sebelum menggunakan layanan sewa mobil RentalQu.</p>
      </div>
    </div>
  </div>

  <div class="container tnc-container">
    <div class="row g-4">

      <!-- Sidebar Navigation -->
      <div class="col-lg-3">
        <div class="tnc-sidebar" id="tncSidebar">
          <h6 class="tnc-sidebar-title">Daftar Isi</h6>
          <nav class="tnc-nav">
            <a href="#ketentuan-umum" class="tnc-nav-link active"><i class="bi bi-check-circle"></i> Ketentuan Umum</a>
            <a href="#persyaratan-penyewa" class="tnc-nav-link"><i class="bi bi-person-badge"></i> Persyaratan Penyewa</a>
            <a href="#pemesanan" class="tnc-nav-link"><i class="bi bi-calendar-check"></i> Pemesanan</a>
            <a href="#pembayaran" class="tnc-nav-link"><i class="bi bi-credit-card"></i> Pembayaran</a>
            <a href="#supir" class="tnc-nav-link"><i class="bi bi-person-workspace"></i> Layanan Supir</a>
            <a href="#pickup-dropoff" class="tnc-nav-link"><i class="bi bi-geo-alt"></i> Pickup & Dropoff</a>
            <a href="#penggunaan" class="tnc-nav-link"><i class="bi bi-car-front"></i> Penggunaan Kendaraan</a>
            <a href="#pengembalian" class="tnc-nav-link"><i class="bi bi-arrow-return-left"></i> Pengembalian</a>
            <a href="#denda" class="tnc-nav-link"><i class="bi bi-exclamation-triangle"></i> Denda Keterlambatan</a>
            <a href="#pembatalan" class="tnc-nav-link"><i class="bi bi-x-circle"></i> Pembatalan & Refund</a>
            <a href="#tanggung-jawab" class="tnc-nav-link"><i class="bi bi-shield-exclamation"></i> Tanggung Jawab</a>
            <a href="#kontak" class="tnc-nav-link"><i class="bi bi-telephone"></i> Kontak</a>
          </nav>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-lg-9">
        <div class="tnc-last-updated">
          <i class="bi bi-clock-history me-1"></i> Terakhir diperbarui: 28 Juni 2026
        </div>

        <!-- 1. Ketentuan Umum -->
        <section id="ketentuan-umum" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">1</div>
              <h2 class="tnc-section-title">Ketentuan Umum</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Syarat dan ketentuan ini berlaku untuk seluruh layanan sewa mobil yang disediakan oleh <strong>RentalQu</strong>.</li>
                <li>Dengan melakukan pemesanan melalui platform RentalQu, penyewa dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang berlaku.</li>
                <li>RentalQu berhak mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan terlebih dahulu. Perubahan berlaku sejak dipublikasikan di platform.</li>
                <li>Penyewa wajib mematuhi seluruh peraturan lalu lintas dan hukum yang berlaku di wilayah Indonesia selama masa sewa kendaraan.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 2. Persyaratan Penyewa -->
        <section id="persyaratan-penyewa" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">2</div>
              <h2 class="tnc-section-title">Persyaratan Penyewa</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Penyewa harus berusia minimal <strong>17 tahun</strong> dan memiliki Surat Izin Mengemudi (SIM) yang masih berlaku sesuai jenis kendaraan yang disewa.</li>
                <li>Penyewa wajib mendaftar akun di platform RentalQu dengan mengisi data yang valid, termasuk:
                  <ul>
                    <li>Nama lengkap sesuai identitas</li>
                    <li>Nomor Induk Kependudukan (NIK)</li>
                    <li>Alamat email aktif</li>
                    <li>Nomor telepon aktif</li>
                    <li>Alamat tempat tinggal</li>
                  </ul>
                </li>
                <li>RentalQu berhak menolak pemesanan apabila data penyewa tidak valid atau tidak lengkap.</li>
                <li>Penyewa bertanggung jawab penuh atas keamanan akun dan kata sandi mereka.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 3. Pemesanan -->
        <section id="pemesanan" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">3</div>
              <h2 class="tnc-section-title">Pemesanan</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Pemesanan dilakukan melalui platform RentalQu dengan memilih kendaraan, tanggal mulai, dan tanggal selesai sewa.</li>
                <li>Durasi sewa minimum adalah <strong>1 hari</strong> (terhitung dari tanggal mulai hingga tanggal selesai).</li>
                <li>Pemesanan hanya dapat dilakukan untuk kendaraan dengan status <strong>"Tersedia"</strong>.</li>
                <li>Pemesanan yang sudah dibuat akan berstatus <strong>Pending</strong> sampai pembayaran dikonfirmasi oleh admin.</li>
                <li>RentalQu berhak menolak atau membatalkan pemesanan yang dianggap mencurigakan atau melanggar ketentuan.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 4. Pembayaran -->
        <section id="pembayaran" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">4</div>
              <h2 class="tnc-section-title">Pembayaran</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Pembayaran dilakukan melalui <strong>transfer bank</strong> ke rekening resmi RentalQu yang tertera di halaman pembayaran.</li>
                <li>Penyewa wajib mengunggah <strong>bukti transfer</strong> yang jelas dan valid melalui halaman pembayaran.</li>
                <li>Pembayaran harus dilakukan secara <strong>penuh</strong> (full payment) sesuai total tagihan yang tercantum.</li>
                <li>Konfirmasi pembayaran dilakukan oleh admin dalam waktu <strong>1 × 24 jam</strong> kerja setelah bukti transfer diunggah.</li>
                <li>Pemesanan yang tidak dibayar dalam waktu yang ditentukan akan otomatis dibatalkan.</li>
              </ol>
              <div class="tnc-info-box">
                <i class="bi bi-info-circle-fill"></i>
                <span>Pastikan nominal transfer sesuai dengan total tagihan dan cantumkan kode booking pada berita transfer (jika memungkinkan).</span>
              </div>
            </div>
          </div>
        </section>

        <!-- 5. Layanan Supir -->
        <section id="supir" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">5</div>
              <h2 class="tnc-section-title">Layanan Supir</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>RentalQu menyediakan opsi sewa <strong>dengan supir</strong> atau <strong>tanpa supir</strong> (lepas kunci).</li>
                <li>Biaya supir dikenakan sebesar <strong>Rp <?= number_format($driverFee, 0, ',', '.') ?>/hari</strong> di luar biaya sewa kendaraan.</li>
                <li>Layanan dengan supir sudah termasuk biaya pickup dan dropoff secara <strong>gratis</strong>.</li>
                <li>Untuk sewa tanpa supir, penyewa wajib memiliki SIM yang sesuai dengan jenis kendaraan dan bertanggung jawab penuh atas kendaraan selama masa sewa.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 6. Pickup & Dropoff -->
        <section id="pickup-dropoff" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">6</div>
              <h2 class="tnc-section-title">Pickup & Dropoff</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Untuk sewa <strong>tanpa supir</strong>, penyewa dapat memilih:
                  <div class="tnc-option-grid">
                    <div class="tnc-option-card">
                      <div class="tnc-option-icon"><i class="bi bi-building"></i></div>
                      <div>
                        <strong>Ambil di Kantor</strong>
                        <span class="tnc-badge-free">Gratis</span>
                        <p>Ambil kendaraan langsung di kantor RentalQu.</p>
                      </div>
                    </div>
                    <div class="tnc-option-card">
                      <div class="tnc-option-icon"><i class="bi bi-truck"></i></div>
                      <div>
                        <strong>Diantar ke Alamat</strong>
                        <span class="tnc-badge-fee">+Rp <?= number_format($pickupFee, 0, ',', '.') ?></span>
                        <p>Kendaraan diantar ke alamat yang ditentukan.</p>
                      </div>
                    </div>
                  </div>
                </li>
                <li>Untuk <strong>dropoff</strong> (pengembalian), penyewa dapat memilih:
                  <div class="tnc-option-grid">
                    <div class="tnc-option-card">
                      <div class="tnc-option-icon"><i class="bi bi-building"></i></div>
                      <div>
                        <strong>Antar ke Kantor</strong>
                        <span class="tnc-badge-free">Gratis</span>
                        <p>Kembalikan kendaraan langsung ke kantor RentalQu.</p>
                      </div>
                    </div>
                    <div class="tnc-option-card">
                      <div class="tnc-option-icon"><i class="bi bi-geo-alt-fill"></i></div>
                      <div>
                        <strong>Dijemput di Alamat</strong>
                        <span class="tnc-badge-fee">+Rp <?= number_format($dropoffFee, 0, ',', '.') ?></span>
                        <p>Kendaraan dijemput oleh tim RentalQu di lokasi Anda.</p>
                      </div>
                    </div>
                  </div>
                </li>
                <li>Untuk sewa <strong>dengan supir</strong>, layanan pickup dan dropoff sudah <strong>termasuk dalam biaya supir</strong> tanpa biaya tambahan.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 7. Penggunaan Kendaraan -->
        <section id="penggunaan" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">7</div>
              <h2 class="tnc-section-title">Penggunaan Kendaraan</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Kendaraan hanya boleh digunakan oleh penyewa yang namanya tercantum dalam pemesanan.</li>
                <li>Penyewa <strong>dilarang</strong> menggunakan kendaraan untuk:
                  <ul>
                    <li>Kegiatan ilegal atau melanggar hukum</li>
                    <li>Balapan atau kegiatan berbahaya</li>
                    <li>Mengangkut barang terlarang</li>
                    <li>Disewakan kembali kepada pihak ketiga</li>
                    <li>Digunakan di luar wilayah yang disepakati tanpa izin</li>
                  </ul>
                </li>
                <li>Penyewa wajib menjaga kebersihan dan kondisi kendaraan selama masa sewa.</li>
                <li>Biaya bahan bakar selama masa sewa menjadi <strong>tanggung jawab penyewa</strong>.</li>
                <li>Kendaraan harus dikembalikan dengan kondisi bahan bakar minimal sama seperti saat diterima.</li>
              </ol>
              <div class="tnc-warning-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Pelanggaran terhadap ketentuan penggunaan kendaraan dapat mengakibatkan penghentian sewa secara sepihak tanpa pengembalian biaya sewa.</span>
              </div>
            </div>
          </div>
        </section>

        <!-- 8. Pengembalian -->
        <section id="pengembalian" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">8</div>
              <h2 class="tnc-section-title">Pengembalian Kendaraan</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Kendaraan harus dikembalikan <strong>tepat waktu</strong> sesuai tanggal selesai yang tertera dalam pemesanan.</li>
                <li>Pengembalian dilakukan ke lokasi yang telah disepakati (kantor RentalQu atau lokasi dropoff yang dipilih).</li>
                <li>Kendaraan wajib dikembalikan dalam kondisi yang sama seperti saat diterima. Kerusakan di luar pemakaian wajar menjadi tanggung jawab penyewa.</li>
                <li>Pengecekan kondisi kendaraan akan dilakukan oleh tim RentalQu pada saat pengembalian.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 9. Denda Keterlambatan -->
        <section id="denda" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">9</div>
              <h2 class="tnc-section-title">Denda Keterlambatan</h2>
            </div>
            <div class="tnc-content">
              <div class="tnc-highlight-box">
                <div class="tnc-highlight-icon">
                  <i class="bi bi-clock-fill"></i>
                </div>
                <div class="tnc-highlight-content">
                  <h5>Biaya Denda</h5>
                  <p class="tnc-highlight-amount">Rp <?= number_format($penaltyFee, 0, ',', '.') ?> <span>/ jam</span></p>
                  <p class="tnc-highlight-note">Dihitung per jam penuh keterlambatan. Toleransi 60 menit pertama tidak dikenakan denda.</p>
                </div>
              </div>
              <ol class="tnc-list">
                <li>Keterlambatan pengembalian kendaraan akan dikenakan denda sebesar <strong>Rp <?= number_format($penaltyFee, 0, ',', '.') ?> per jam</strong>.</li>
                <li>Terdapat <strong>toleransi 60 menit pertama</strong> setelah waktu pengembalian yang dijadwalkan. Keterlambatan di bawah 60 menit <strong>tidak dikenakan denda</strong>.</li>
                <li>Jika keterlambatan melebihi 60 menit, denda dihitung <strong>per jam penuh</strong> dengan pembulatan ke bawah. Sisa menit di bawah 60 tidak dihitung.</li>
                <li>Denda akan ditambahkan ke tagihan penyewa secara terpisah dari biaya sewa.</li>
                <li>Pembayaran denda harus dilakukan sebelum penyewa dapat melakukan pemesanan baru.</li>
                <li>Penyewa dapat mengunggah bukti pembayaran denda melalui dashboard akun.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 10. Pembatalan & Refund -->
        <section id="pembatalan" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">10</div>
              <h2 class="tnc-section-title">Pembatalan & Refund</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Penyewa dapat mengajukan pembatalan pemesanan melalui <strong>dashboard akun</strong>.</li>
                <li>Pembatalan pemesanan yang belum dibayar dapat dilakukan kapan saja tanpa biaya.</li>
                <li>Untuk pemesanan yang sudah dibayar dan dikonfirmasi, penyewa dapat mengajukan <strong>permintaan refund</strong> dengan menyertakan alasan pembatalan.</li>
                <li>Pengajuan refund akan diproses oleh admin dan keputusan akhir berada di pihak RentalQu.</li>
                <li>Alasan pembatalan yang diterima meliputi:
                  <ul>
                    <li>Perubahan rencana perjalanan</li>
                    <li>Kondisi darurat keluarga</li>
                    <li>Mobil tidak sesuai ekspektasi</li>
                    <li>Masalah pembayaran</li>
                    <li>Alasan lainnya (wajib disertai penjelasan)</li>
                  </ul>
                </li>
                <li>Proses refund akan dilakukan melalui transfer bank ke rekening penyewa yang terdaftar.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 11. Tanggung Jawab -->
        <section id="tanggung-jawab" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">11</div>
              <h2 class="tnc-section-title">Tanggung Jawab</h2>
            </div>
            <div class="tnc-content">
              <ol class="tnc-list">
                <li>Penyewa bertanggung jawab penuh atas kendaraan selama masa sewa, termasuk kerusakan, kehilangan, dan kecelakaan yang terjadi.</li>
                <li>Segala bentuk pelanggaran lalu lintas (tilang) selama masa sewa menjadi tanggung jawab penyewa.</li>
                <li>RentalQu tidak bertanggung jawab atas barang pribadi penyewa yang tertinggal di dalam kendaraan.</li>
                <li>Dalam hal kecelakaan, penyewa wajib segera melapor kepada pihak kepolisian dan RentalQu.</li>
                <li>RentalQu berhak menuntut ganti rugi atas kerusakan kendaraan yang disebabkan oleh kelalaian penyewa.</li>
              </ol>
            </div>
          </div>
        </section>

        <!-- 12. Kontak -->
        <section id="kontak" class="tnc-section">
          <div class="tnc-card">
            <div class="tnc-section-header">
              <div class="tnc-section-number">12</div>
              <h2 class="tnc-section-title">Kontak</h2>
            </div>
            <div class="tnc-content">
              <p>Untuk pertanyaan, keluhan, atau informasi lebih lanjut mengenai syarat dan ketentuan ini, silakan hubungi kami melalui:</p>
              <div class="tnc-contact-grid">
                <div class="tnc-contact-item">
                  <div class="tnc-contact-icon"><i class="bi bi-envelope-fill"></i></div>
                  <div>
                    <strong>Email</strong>
                    <a href="mailto:info@rentalqu.id">info@rentalqu.id</a>
                  </div>
                </div>
                <div class="tnc-contact-item">
                  <div class="tnc-contact-icon"><i class="bi bi-telephone-fill"></i></div>
                  <div>
                    <strong>Telepon</strong>
                    <a href="tel:+6281234567890">+62 812-3456-7890</a>
                  </div>
                </div>
                <div class="tnc-contact-item">
                  <div class="tnc-contact-icon"><i class="bi bi-whatsapp"></i></div>
                  <div>
                    <strong>WhatsApp</strong>
                    <a href="https://wa.me/6281234567890" target="_blank">Chat via WhatsApp</a>
                  </div>
                </div>
                <div class="tnc-contact-item">
                  <div class="tnc-contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                  <div>
                    <strong>Alamat Kantor</strong>
                    <span>Jl. Sudirman No. 10, Jakarta</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- CTA -->
        <div class="tnc-cta">
          <p>Dengan menggunakan layanan RentalQu, Anda dianggap telah menyetujui seluruh syarat dan ketentuan di atas.</p>
          <a href="katalog.php" class="btn btn-rental">
            <i class="bi bi-grid-3x3-gap-fill me-2"></i>Lihat Katalog Mobil
          </a>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function() {
      // Smooth scroll untuk sidebar nav
      document.querySelectorAll('.tnc-nav-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          var targetId = this.getAttribute('href').substring(1);
          var target = document.getElementById(targetId);
          if (target) {
            var offset = 90;
            var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top: top, behavior: 'smooth' });
          }
        });
      });

      // Active state saat scroll
      var sections = document.querySelectorAll('.tnc-section');
      var navLinks = document.querySelectorAll('.tnc-nav-link');

      function updateActiveNav() {
        var scrollPos = window.scrollY + 120;
        sections.forEach(function(section) {
          var top = section.offsetTop;
          var bottom = top + section.offsetHeight;
          var id = section.getAttribute('id');
          if (scrollPos >= top && scrollPos < bottom) {
            navLinks.forEach(function(link) {
              link.classList.remove('active');
              if (link.getAttribute('href') === '#' + id) {
                link.classList.add('active');
              }
            });
          }
        });
      }

      window.addEventListener('scroll', updateActiveNav);
      updateActiveNav();
    })();
  </script>

  <?php include '../includes/footer.php'; ?>
</body>

</html>
