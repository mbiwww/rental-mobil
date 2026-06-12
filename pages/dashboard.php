<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Dashboard Saya</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <style type="text/tailwindcss">
    @import "tailwindcss";

    .dash-container {
      width: 100%;
      margin-right: auto;
      margin-left: auto;
      padding-right: 1rem; 
      padding-left: 1rem;
    }

    @media (width >= 576px) {
      .dash-container {
        max-width: 540px;
      }
    }

    @media (width >= 768px) {
      .dash-container {
        max-width: 720px;
      }
    }

    @media (width >= 992px) {
      .dash-container {
        max-width: 960px;
      }
    }

    @media (width >= 1200px) {
      .dash-container {
        max-width: 1140px;
      }
    }

    @media (width >= 1400px) {
      .dash-container {
        max-width: 1320px;
      }
    }
  </style>
</head>

<body class="bg-[#f5f7fa] font-['Inter',system-ui,-apple-system,sans-serif]">
  <?php
  include '../includes/navbar.php';
  ?>
  <div class="dash-container">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold tracking-tight text-[#0b2b4a] pt-6">Dashboard Saya</h1>
      <p class="text-slate-500 mt-1">Kelola pesanan dan profil Anda</p>
    </div>

    <!-- Section 1: Total Summary -->
    <h2 class="text-base font-bold text-[#0b2b4a] mb-4">Total Ringkasan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

      <div class="bg-white p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.04)] border border-black/5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
          <i class="bi bi-car-front-fill"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-slate-500">Total Sewa</div>
          <div class="text-xl font-bold text-[#0b2b4a]">12 Sewa</div>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.04)] border border-black/5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
          <i class="bi bi-clock-history"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-slate-500">Rata-rata Durasi</div>
          <div class="text-xl font-bold text-[#0b2b4a]">2.5 Hari</div>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.04)] border border-black/5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
          <i class="bi bi-wallet2"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-slate-500">Total Pengeluaran</div>
          <div class="text-xl font-bold text-[#0b2b4a]">Rp 8.500.000</div>
        </div>
      </div>

    </div>

    <!-- Tabs -->
    <div class="flex border-b border-slate-200 mb-6" id="dashboardTab" role="tablist">
      <button class="px-6 py-3 font-semibold text-blue-600 border-b-[3px] border-blue-600 -mb-[2px] transition-all" id="riwayat-tab" data-tab="riwayat" type="button" role="tab">
        Riwayat Sewa
      </button>
      <button class="px-6 py-3 font-semibold text-slate-500 border-b-[3px] border-transparent hover:text-slate-700 transition-all" id="profil-tab" data-tab="profil" type="button" role="tab">
        Profil Saya
      </button>
    </div>

    <div class="tab-content" id="dashboardTabContent">

      <!-- ===== Riwayat Sewa ===== -->
      <div class="tab-pane fade show active" id="riwayat" role="tabpanel">

        <!-- Empty state -->
        <div id="empty-history" class="bg-white p-12 rounded-3xl shadow-sm text-center text-slate-500 mt-4 mb-4">
          <i class="bi bi-car-front block text-5xl text-slate-300 mb-4"></i>
          <h5 class="text-lg font-semibold text-slate-600 mb-1">Belum Ada Riwayat Sewa</h5>
          <p>Mulai menyewa mobil untuk melihat riwayat di sini</p>
        </div>

        <!-- Rental list -->
        <div id="rental-list" class="hidden">

          <!-- 1. Menunggu Verifikasi -->
          <div class="bg-[#fafbfc] p-4 rounded-xl border-l-4 border-l-amber-400 mt-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <div>
                <h6 class="text-base font-bold text-[#0b2b4a] mb-1">Toyota Avanza 2023</h6>
                <div class="text-sm text-slate-500"><i class="bi bi-calendar3 mr-1"></i> 02 Mei 2026 - 03 Mei 2026 (1 hari)</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 mt-1">Menunggu Verifikasi</span>
              </div>
              <div class="text-left md:text-right">
                <button class="px-4 py-1.5 rounded-full bg-[#0b2b4a] text-white text-sm font-semibold shadow-md transition-all opacity-50 pointer-events-none" disabled>Refund</button>
                <small class="block text-xs text-slate-400 mt-1">Menunggu konfirmasi admin</small>
              </div>
            </div>
          </div>

          <!-- 2. Terkonfirmasi (Refund aktif) -->
          <div class="bg-[#fafbfc] p-4 rounded-xl border-l-4 border-l-blue-500 mt-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <div>
                <h6 class="text-base font-bold text-[#0b2b4a] mb-1">Honda Civic 2024</h6>
                <div class="text-sm text-slate-500"><i class="bi bi-calendar3 mr-1"></i> 28 Apr 2026 - 30 Apr 2026 (2 hari)</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 mt-1">Terkonfirmasi</span>
              </div>
              <div class="text-left md:text-right">
                <button class="px-4 py-1.5 rounded-full bg-[#0b2b4a] text-white text-sm font-semibold shadow-md hover:bg-[#1e3a6f] hover:-translate-y-0.5 transition-all btn-refund">Refund</button>
                <small class="block text-xs text-slate-400 mt-1">Mobil belum diambil</small>
              </div>
            </div>
          </div>

          <!-- 3. Sedang Disewa -->
          <div class="bg-[#fafbfc] p-4 rounded-xl border-l-4 border-l-indigo-500 mt-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <div>
                <h6 class="text-base font-bold text-[#0b2b4a] mb-1">Toyota Fortuner 2023</h6>
                <div class="text-sm text-slate-500"><i class="bi bi-calendar3 mr-1"></i> 27 Apr 2026 - 29 Apr 2026 (2 hari)</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 mt-1">Sedang Disewa</span>
              </div>
              <div class="text-left md:text-right">
                <button class="px-4 py-1.5 rounded-full bg-[#0b2b4a] text-white text-sm font-semibold shadow-md transition-all opacity-50 pointer-events-none" disabled>Refund</button>
                <small class="block text-xs text-slate-400 mt-1">Mobil sudah diambil</small>
              </div>
            </div>
          </div>

          <!-- 4. Selesai -->
          <div class="bg-[#fafbfc] p-4 rounded-xl border-l-4 border-l-emerald-500 mt-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <div>
                <h6 class="text-base font-bold text-[#0b2b4a] mb-1">Honda Brio 2024</h6>
                <div class="text-sm text-slate-500"><i class="bi bi-calendar3 mr-1"></i> 20 Apr 2026 - 22 Apr 2026 (2 hari)</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mt-1">Selesai</span>
              </div>
              <div class="text-left md:text-right">
                <button class="px-4 py-1.5 rounded-full bg-[#0b2b4a] text-white text-sm font-semibold shadow-md transition-all opacity-50 pointer-events-none" disabled>Refund</button>
                <small class="block text-xs text-slate-400 mt-1">Sewa telah selesai</small>
              </div>
            </div>
          </div>

          <!-- 5. Dibatalkan -->
          <div class="bg-[#fafbfc] p-4 rounded-xl border-l-4 border-l-slate-400 mt-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <div>
                <h6 class="text-base font-bold text-[#0b2b4a] mb-1">Mitsubishi Pajero Sport</h6>
                <div class="text-sm text-slate-500"><i class="bi bi-calendar3 mr-1"></i> 18 Apr 2026 - 19 Apr 2026 (1 hari)</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 mt-1">Dibatalkan</span>
              </div>
              <div class="text-left md:text-right">
                <button class="px-4 py-1.5 rounded-full bg-[#0b2b4a] text-white text-sm font-semibold shadow-md transition-all opacity-50 pointer-events-none" disabled>Refund</button>
                <small class="block text-xs text-slate-400 mt-1">Pesanan dibatalkan</small>
              </div>
            </div>
          </div>

          <!-- 6. Refund Diproses -->
          <div class="bg-[#fafbfc] p-4 rounded-xl border-l-4 border-l-orange-500 mt-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <div>
                <h6 class="text-base font-bold text-[#0b2b4a] mb-1">Toyota Camry 2024</h6>
                <div class="text-sm text-slate-500"><i class="bi bi-calendar3 mr-1"></i> 10 Apr 2026 - 12 Apr 2026 (2 hari)</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 mt-1">Refund Diproses</span>
              </div>
              <div class="text-left md:text-right">
                <button class="px-4 py-1.5 rounded-full bg-[#0b2b4a] text-white text-sm font-semibold shadow-md transition-all opacity-50 pointer-events-none" disabled>Refund</button>
                <small class="block text-xs text-slate-400 mt-1">Refund sedang diproses</small>
              </div>
            </div>
          </div>

          <!-- 7. Refund Berhasil -->
          <div class="bg-[#fafbfc] p-4 rounded-xl border-l-4 border-l-purple-500 mt-4 mb-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <div>
                <h6 class="text-base font-bold text-[#0b2b4a] mb-1">Honda Jazz 2024</h6>
                <div class="text-sm text-slate-500"><i class="bi bi-calendar3 mr-1"></i> 05 Apr 2026 - 06 Apr 2026 (1 hari)</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 mt-1">Refund Berhasil</span>
              </div>
              <div class="text-left md:text-right">
                <button class="px-4 py-1.5 rounded-full bg-[#0b2b4a] text-white text-sm font-semibold shadow-md transition-all opacity-50 pointer-events-none" disabled>Refund</button>
                <small class="block text-xs text-slate-400 mt-1">Dana sudah dikembalikan</small>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ===== Profil Saya ===== -->
      <div class="tab-pane fade" id="profil" role="tabpanel">
        <div class="bg-white p-6 rounded-3xl shadow-sm mt-4 mb-4">
          <h5 class="text-lg font-bold text-[#0b2b4a] mb-4">Informasi Profil</h5>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Nama Lengkap</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayName">John Doe</div>
            </div>
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">NIK</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayNik">3273012345678901</div>
            </div>
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Email</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayEmail">john@example.com</div>
            </div>
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">No. HP</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayPhone">0812-3456-7890</div>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Alamat</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayAddress">Jl. Sudirman No. 10, Jakarta</div>
            </div>
            <div class="md:col-span-2 mt-1">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">
                <i class="bi bi-check-circle-fill mr-1"></i>Identitas Terverifikasi
              </span>
            </div>
          </div>
          <button class="inline-flex items-center px-5 py-2.5 rounded-full bg-[#0b2b4a] text-white font-semibold shadow-md hover:bg-[#1e3a6f] hover:-translate-y-0.5 transition-all mt-4" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="bi bi-pencil-square mr-2"></i>Edit Profil
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal Edit Profil -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-2xl shadow-lg border-0">
        <div class="modal-header border-0 px-4 pt-4">
          <h5 class="text-lg font-bold text-[#0b2b4a]" id="editProfileModalLabel">Edit Profil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 pb-4">

          <!-- Modal pills tab -->
          <div class="flex gap-2 mb-4" id="profileModalTab" role="tablist">
            <button class="px-4 py-2 rounded-xl text-sm font-medium bg-[#0b2b4a] text-white transition-all" id="data-diri-tab" data-modal-tab="dataDiri" type="button" role="tab">
              Data Diri
            </button>
            <button class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 hover:text-[#0b2b4a] transition-all" id="ganti-password-tab" data-modal-tab="gantiPassword" type="button" role="tab">
              Ganti Password
            </button>
          </div>

          <div class="tab-content">
            <!-- Data Diri -->
            <div class="tab-pane fade show active" id="dataDiri" role="tabpanel">
              <form id="dataDiriForm">
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Nama Lengkap</label>
                  <input type="text" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputName" value="John Doe">
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">NIK</label>
                  <input type="text" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputNik" value="3273012345678901">
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Email</label>
                  <input type="email" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputEmail" value="john@example.com">
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">No. HP</label>
                  <input type="tel" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputPhone" value="0812-3456-7890">
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Alamat</label>
                  <textarea class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputAddress" rows="2">Jl. Sudirman No. 10, Jakarta</textarea>
                </div>
                <button type="submit" class="w-full px-5 py-3 rounded-full bg-[#0b2b4a] text-white font-semibold shadow-md hover:bg-[#1e3a6f] hover:-translate-y-0.5 transition-all">
                  <i class="bi bi-save mr-2"></i>Simpan Perubahan
                </button>
              </form>
            </div>

            <!-- Ganti Password -->
            <div class="tab-pane fade" id="gantiPassword" role="tabpanel">
              <form id="passwordForm">
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Password Lama</label>
                  <input type="password" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="••••••••">
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Password Baru</label>
                  <input type="password" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Minimal 8 karakter">
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Konfirmasi Password Baru</label>
                  <input type="password" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Ulangi password baru">
                </div>
                <button type="submit" class="w-full px-5 py-3 rounded-full bg-[#0b2b4a] text-white font-semibold shadow-md hover:bg-[#1e3a6f] hover:-translate-y-0.5 transition-all">
                  <i class="bi bi-shield-lock mr-2"></i>Ganti Password
                </button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/tailwind.js"></script>
  <script>
    // Tab controller — dashboard utama
    (function() {
      var container = document.getElementById('dashboardTab');
      if (!container) return;
      var tabs = container.querySelectorAll('[data-tab]');
      function activateTab(id) {
        var panel, btn, i;
        for (i = 0; i < tabs.length; i++) {
          btn = tabs[i];
          if (btn.getAttribute('data-tab') === id) {
            btn.classList.add('text-blue-600', 'border-blue-600');
            btn.classList.remove('text-slate-500', 'border-transparent');
          } else {
            btn.classList.remove('text-blue-600', 'border-blue-600');
            btn.classList.add('text-slate-500', 'border-transparent');
          }
        }
        var panels = ['riwayat', 'profil'];
        for (i = 0; i < panels.length; i++) {
          panel = document.getElementById(panels[i]);
          if (!panel) continue;
          if (panels[i] === id) {
            panel.classList.add('show', 'active');
            panel.classList.remove('hidden');
          } else {
            panel.classList.remove('show', 'active');
            panel.classList.add('hidden');
          }
        }
      }
      for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function() {
          activateTab(this.getAttribute('data-tab'));
        });
      }
      activateTab('riwayat');
    })();

    // Tab controller — modal profil
    (function() {
      var container = document.getElementById('profileModalTab');
      if (!container) return;
      var pills = container.querySelectorAll('[data-modal-tab]');
      function activatePill(id) {
        var panel, btn, i;
        for (i = 0; i < pills.length; i++) {
          btn = pills[i];
          if (btn.getAttribute('data-modal-tab') === id) {
            btn.classList.remove('text-slate-600');
            btn.classList.add('bg-[#0b2b4a]', 'text-white');
          } else {
            btn.classList.remove('bg-[#0b2b4a]', 'text-white');
            btn.classList.add('text-slate-600');
          }
        }
        var panels = ['dataDiri', 'gantiPassword'];
        for (i = 0; i < panels.length; i++) {
          panel = document.getElementById(panels[i]);
          if (!panel) continue;
          if (panels[i] === id) {
            panel.classList.add('show', 'active');
            panel.classList.remove('hidden');
          } else {
            panel.classList.remove('show', 'active');
            panel.classList.add('hidden');
          }
        }
      }
      for (var i = 0; i < pills.length; i++) {
        pills[i].addEventListener('click', function() {
          activatePill(this.getAttribute('data-modal-tab'));
        });
      }
      activatePill('dataDiri');
    })();

    // Tampilkan riwayat (sembunyikan empty state)
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('empty-history').classList.add('hidden');
      document.getElementById('rental-list').classList.remove('hidden');
    });

    // Logika tombol Refund
    document.querySelectorAll('#riwayat .btn-refund').forEach(function(btn) {
      btn.addEventListener('click', function() {
        if (!this.disabled) {
          alert('Permintaan refund akan diproses. Status akan berubah menjadi Refund Diproses. (Simulasi)');
        }
      });
    });

    // Update tampilan profil setelah simpan
    document.getElementById('dataDiriForm').addEventListener('submit', function(e) {
      e.preventDefault();
      document.getElementById('displayName').textContent = document.getElementById('inputName').value;
      document.getElementById('displayNik').textContent = document.getElementById('inputNik').value;
      document.getElementById('displayEmail').textContent = document.getElementById('inputEmail').value;
      document.getElementById('displayPhone').textContent = document.getElementById('inputPhone').value;
      document.getElementById('displayAddress').textContent = document.getElementById('inputAddress').value;
      alert('Profil berhasil diperbarui.');
      bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
    });

    // Ganti password
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
      e.preventDefault();
      var inputs = this.querySelectorAll('input[type="password"]');
      if (inputs[1].value !== inputs[2].value) {
        alert('Konfirmasi password baru tidak cocok.');
        return;
      }
      if (inputs[1].value.length < 8) {
        alert('Password baru minimal 8 karakter.');
        return;
      }
      alert('Password berhasil diubah. (Simulasi)');
    });
  </script>
  <?php include '../includes/footer.php'; ?>
</body>

</html>
