<?php
/**
 * pages/profil-perusahaan.php — Halaman Profil Perusahaan RentalQu
 *
 * Menampilkan informasi lengkap tentang perusahaan RentalQu:
 * - Tentang perusahaan
 * - Visi & misi
 * - Keunggulan layanan
 * - Armada (statistik dari database)
 * - Tim / nilai perusahaan
 * - Kontak
 */

session_start();

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Car.php';
require_once '../classes/CarType.php';

$pdo = Database::getInstance()->getConnection();
$carModel  = new Car($pdo);
$typeModel = new CarType($pdo);

// Ambil statistik armada dari database
$totalCars      = $carModel->countByStatus('available') + $carModel->countByStatus('rented') + $carModel->countByStatus('maintenance');
$availableCars  = $carModel->countByStatus('available');
$totalTypes     = count($typeModel->getAll());

// Hitung total pelanggan
$stmtCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$totalCustomers = (int) $stmtCustomers->fetchColumn();

// Hitung total transaksi selesai
$stmtCompleted = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'completed'");
$totalCompleted = (int) $stmtCompleted->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalQu &middot; Profil Perusahaan</title>
  <meta name="description" content="Profil perusahaan RentalQu — solusi sewa mobil terpercaya dengan armada terawat dan harga transparan di Indonesia.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/profil-perusahaan.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/flash.css">
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/flash.php'; ?>

  <!-- ═══ Hero Section ═══ -->
  <section class="pp-hero">
    <div class="pp-hero-bg"></div>
    <div class="container">
      <div class="pp-hero-content">
        <span class="pp-hero-badge"><i class="bi bi-building me-1"></i> Profil Perusahaan</span>
        <h1 class="pp-hero-title">RentalQu</h1>
        <p class="pp-hero-tagline">Solusi Sewa Mobil Terpercaya di Indonesia</p>
        <p class="pp-hero-desc">Menghadirkan pengalaman sewa mobil yang mudah, aman, dan transparan bagi setiap perjalanan Anda.</p>
      </div>
    </div>
  </section>

  <!-- ═══ Statistik ═══ -->
  <section class="pp-stats-section">
    <div class="container">
      <div class="pp-stats-grid">
        <div class="pp-stat-card">
          <div class="pp-stat-icon"><i class="bi bi-car-front-fill"></i></div>
          <div class="pp-stat-value"><?= $totalCars ?>+</div>
          <div class="pp-stat-label">Unit Armada</div>
        </div>
        <div class="pp-stat-card">
          <div class="pp-stat-icon"><i class="bi bi-grid-fill"></i></div>
          <div class="pp-stat-value"><?= $totalTypes ?></div>
          <div class="pp-stat-label">Tipe Kendaraan</div>
        </div>
        <div class="pp-stat-card">
          <div class="pp-stat-icon"><i class="bi bi-people-fill"></i></div>
          <div class="pp-stat-value"><?= $totalCustomers ?>+</div>
          <div class="pp-stat-label">Pelanggan Terdaftar</div>
        </div>
        <div class="pp-stat-card">
          <div class="pp-stat-icon"><i class="bi bi-check-circle-fill"></i></div>
          <div class="pp-stat-value"><?= $totalCompleted ?>+</div>
          <div class="pp-stat-label">Transaksi Berhasil</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ Tentang Kami ═══ -->
  <section class="pp-section" id="tentang">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          <div class="pp-about-image">
            <div class="pp-about-image-inner">
              <i class="bi bi-car-front-fill"></i>
            </div>
            <div class="pp-about-image-accent"></div>
          </div>
        </div>
        <div class="col-lg-6">
          <span class="pp-label">Tentang Kami</span>
          <h2 class="pp-heading">Perjalanan Anda, <br>Tanggung Jawab Kami</h2>
          <p class="pp-text">
            <strong>RentalQu</strong> adalah platform penyewaan mobil berbasis digital yang berkomitmen memberikan layanan terbaik kepada pelanggan. Kami hadir sebagai jawaban atas kebutuhan transportasi yang fleksibel, terjangkau, dan terpercaya.
          </p>
          <p class="pp-text">
            Didirikan dengan semangat untuk memudahkan masyarakat Indonesia dalam menyewa kendaraan, RentalQu menyediakan berbagai pilihan mobil mulai dari city car, sedan, MPV, hingga SUV. Setiap kendaraan dalam armada kami dirawat secara berkala untuk memastikan keamanan dan kenyamanan selama perjalanan.
          </p>
          <p class="pp-text">
            Melalui platform online kami, proses pemesanan dapat dilakukan dalam hitungan menit. Dari pemilihan kendaraan, penentuan jadwal, hingga pembayaran — semuanya dirancang agar mudah dan efisien.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ Visi & Misi ═══ -->
  <section class="pp-section pp-section-alt" id="visi-misi">
    <div class="container">
      <div class="text-center mb-5">
        <span class="pp-label">Visi & Misi</span>
        <h2 class="pp-heading">Arah & Tujuan Kami</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="pp-vm-card pp-vm-visi">
            <div class="pp-vm-icon">
              <i class="bi bi-eye-fill"></i>
            </div>
            <h3 class="pp-vm-title">Visi</h3>
            <p class="pp-vm-text">
              Menjadi platform penyewaan mobil terdepan di Indonesia yang dikenal karena kualitas layanan, transparansi harga, dan kepuasan pelanggan.
            </p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="pp-vm-card pp-vm-misi">
            <div class="pp-vm-icon">
              <i class="bi bi-bullseye"></i>
            </div>
            <h3 class="pp-vm-title">Misi</h3>
            <ul class="pp-vm-list">
              <li>Menyediakan armada kendaraan berkualitas tinggi yang terawat dan aman</li>
              <li>Memberikan kemudahan proses booking melalui platform digital</li>
              <li>Menawarkan harga sewa yang transparan tanpa biaya tersembunyi</li>
              <li>Memberikan layanan pelanggan yang responsif dan profesional</li>
              <li>Terus berinovasi dalam meningkatkan pengalaman pengguna</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ Keunggulan ═══ -->
  <section class="pp-section" id="keunggulan">
    <div class="container">
      <div class="text-center mb-5">
        <span class="pp-label">Keunggulan Kami</span>
        <h2 class="pp-heading">Mengapa Memilih RentalQu?</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="pp-feature-card">
            <div class="pp-feature-icon">
              <i class="bi bi-shield-check"></i>
            </div>
            <h4 class="pp-feature-title">Aman & Terpercaya</h4>
            <p class="pp-feature-text">Seluruh kendaraan diasuransikan dan menjalani pemeriksaan berkala. Keselamatan Anda adalah prioritas utama kami.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="pp-feature-card">
            <div class="pp-feature-icon">
              <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <h4 class="pp-feature-title">Proses Cepat</h4>
            <p class="pp-feature-text">Booking online dalam hitungan menit. Pilih mobil, tentukan jadwal, bayar, dan kendaraan siap untuk Anda.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="pp-feature-card">
            <div class="pp-feature-icon">
              <i class="bi bi-tag-fill"></i>
            </div>
            <h4 class="pp-feature-title">Harga Transparan</h4>
            <p class="pp-feature-text">Tanpa biaya tersembunyi. Semua biaya sewa, supir, pickup, dan dropoff ditampilkan dengan jelas sebelum Anda memesan.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="pp-feature-card">
            <div class="pp-feature-icon">
              <i class="bi bi-person-check-fill"></i>
            </div>
            <h4 class="pp-feature-title">Opsi Supir Profesional</h4>
            <p class="pp-feature-text">Tersedia layanan supir berpengalaman untuk kenyamanan Anda. Atau pilih lepas kunci jika ingin berkendara sendiri.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="pp-feature-card">
            <div class="pp-feature-icon">
              <i class="bi bi-truck"></i>
            </div>
            <h4 class="pp-feature-title">Antar-Jemput Kendaraan</h4>
            <p class="pp-feature-text">Layanan pickup dan dropoff ke alamat Anda. Tidak perlu repot datang ke kantor untuk mengambil kendaraan.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="pp-feature-card">
            <div class="pp-feature-icon">
              <i class="bi bi-headset"></i>
            </div>
            <h4 class="pp-feature-title">Dukungan Pelanggan</h4>
            <p class="pp-feature-text">Tim customer service kami siap membantu melalui WhatsApp, telepon, atau email kapan pun Anda membutuhkan.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ Nilai Perusahaan ═══ -->
  <section class="pp-section pp-section-alt" id="nilai">
    <div class="container">
      <div class="text-center mb-5">
        <span class="pp-label">Nilai Kami</span>
        <h2 class="pp-heading">Prinsip yang Kami Pegang</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-3 col-6">
          <div class="pp-value-card">
            <div class="pp-value-icon"><i class="bi bi-heart-fill"></i></div>
            <h5 class="pp-value-title">Integritas</h5>
            <p class="pp-value-text">Jujur dan transparan dalam setiap transaksi</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="pp-value-card">
            <div class="pp-value-icon"><i class="bi bi-star-fill"></i></div>
            <h5 class="pp-value-title">Kualitas</h5>
            <p class="pp-value-text">Standar tinggi dalam layanan dan armada</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="pp-value-card">
            <div class="pp-value-icon"><i class="bi bi-emoji-smile-fill"></i></div>
            <h5 class="pp-value-title">Kepuasan</h5>
            <p class="pp-value-text">Pelanggan puas adalah tujuan utama kami</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="pp-value-card">
            <div class="pp-value-icon"><i class="bi bi-arrow-repeat"></i></div>
            <h5 class="pp-value-title">Inovasi</h5>
            <p class="pp-value-text">Terus berkembang untuk layanan yang lebih baik</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ Kontak ═══ -->
  <section class="pp-section" id="kontak">
    <div class="container">
      <div class="text-center mb-5">
        <span class="pp-label">Hubungi Kami</span>
        <h2 class="pp-heading">Kontak Kami</h2>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
          <div class="pp-contact-card">
            <h4 class="pp-contact-title"><i class="bi bi-geo-alt-fill me-2"></i>Kantor Pusat</h4>
            <div class="pp-contact-list">
              <div class="pp-contact-item">
                <div class="pp-contact-icon"><i class="bi bi-pin-map-fill"></i></div>
                <div>
                  <strong>Alamat</strong>
                  <span>Jl. Sudirman No. 10, Jakarta Pusat, DKI Jakarta 10110</span>
                </div>
              </div>
              <div class="pp-contact-item">
                <div class="pp-contact-icon"><i class="bi bi-telephone-fill"></i></div>
                <div>
                  <strong>Telepon</strong>
                  <a href="tel:+6281234567890">+62 812-3456-7890</a>
                </div>
              </div>
              <div class="pp-contact-item">
                <div class="pp-contact-icon"><i class="bi bi-envelope-fill"></i></div>
                <div>
                  <strong>Email</strong>
                  <a href="mailto:info@rentalqu.id">info@rentalqu.id</a>
                </div>
              </div>
              <div class="pp-contact-item">
                <div class="pp-contact-icon"><i class="bi bi-clock-fill"></i></div>
                <div>
                  <strong>Jam Operasional</strong>
                  <span>Senin - Sabtu: 08.00 - 17.00 WIB</span>
                </div>
              </div>
            </div>
            <a href="https://wa.me/6281234567890" class="pp-wa-btn" target="_blank">
              <i class="bi bi-whatsapp me-2"></i>Chat via WhatsApp
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ CTA ═══ -->
  <section class="pp-cta-section">
    <div class="container">
      <div class="pp-cta-card">
        <div class="pp-cta-content">
          <h2 class="pp-cta-title">Siap Memulai Perjalanan?</h2>
          <p class="pp-cta-desc">Temukan mobil yang cocok untuk kebutuhan Anda dan nikmati pengalaman sewa yang mudah bersama RentalQu.</p>
          <a href="katalog.php" class="pp-cta-btn">
            <i class="bi bi-grid-3x3-gap-fill me-2"></i>Lihat Katalog Mobil
          </a>
        </div>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Animasi counter saat elemen terlihat
    (function() {
      var observed = false;
      var statValues = document.querySelectorAll('.pp-stat-value');

      function animateCount(el) {
        var text = el.textContent.trim();
        var suffix = text.replace(/[0-9]/g, '');
        var target = parseInt(text);
        if (isNaN(target)) return;

        var duration = 1500;
        var start = 0;
        var startTime = null;

        function step(timestamp) {
          if (!startTime) startTime = timestamp;
          var progress = Math.min((timestamp - startTime) / duration, 1);
          var eased = 1 - Math.pow(1 - progress, 3);
          el.textContent = Math.floor(eased * target) + suffix;
          if (progress < 1) {
            requestAnimationFrame(step);
          }
        }

        el.textContent = '0' + suffix;
        requestAnimationFrame(step);
      }

      var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting && !observed) {
            observed = true;
            statValues.forEach(animateCount);
          }
        });
      }, { threshold: 0.3 });

      var statsSection = document.querySelector('.pp-stats-section');
      if (statsSection) observer.observe(statsSection);
    })();
  </script>

  <?php include '../includes/footer.php'; ?>
</body>

</html>
