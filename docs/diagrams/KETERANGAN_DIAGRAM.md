# Bagian 2.2.5 Activity Diagram

## Activity Diagram 1: Login
**Gambar 2.1 - Activity Diagram Login**

Diagram ini menggambarkan alur autentikasi pengguna (customer/admin) ke dalam sistem. Pengguna memasukkan email dan password, kemudian sistem memvalidasi input dan mencocokkan kredensial dengan database. Jika valid, session dibuat dan pengguna diarahkan ke dashboard. Jika tidak, pesan error ditampilkan dan pengguna kembali ke form login.

---

## Activity Diagram 2: Registrasi Akun
**Gambar 2.2 - Activity Diagram Registrasi Akun**

Diagram ini menggambarkan proses pendaftaran akun baru oleh customer. Customer mengisi form registrasi yang terdiri dari nama, NIK, email, nomor telepon, alamat, dan password. Sistem memvalidasi input dan memeriksa duplikasi NIK serta email. Jika tidak ada duplikat, data disimpan dan customer diarahkan ke halaman login. Jika terjadi duplikasi atau validasi gagal, pesan error ditampilkan.

---

## Activity Diagram 3: Pemesanan Mobil
**Gambar 2.3 - Activity Diagram Pemesanan Mobil**

Diagram ini menggambarkan alur pemesanan mobil oleh customer. Customer memilih mobil dari katalog, melihat detail, dan mengisi form pemesanan yang mencakup tanggal sewa, opsi sopir, serta layanan pickup/dropoff. Sistem menghitung total biaya secara real-time. Setelah customer mengkonfirmasi, sistem memeriksa ketersediaan mobil. Jika tersedia, data rental disimpan dengan status "pending" dan status mobil diubah menjadi "rented", kemudian customer diarahkan ke halaman pembayaran.

---

## Activity Diagram 4: Pembayaran & Konfirmasi
**Gambar 2.4 - Activity Diagram Pembayaran dan Konfirmasi Pembayaran**

Diagram ini menggambarkan alur pembayaran yang dilakukan customer dan konfirmasi oleh admin. Customer mentransfer biaya sewa ke rekening admin dan mengunggah bukti transfer. Admin meninjau bukti transfer dan memutuskan untuk mengkonfirmasi atau menolaknya. Jika dikonfirmasi, status rental menjadi "confirmed" dan payment menjadi "confirmed". Jika ditolak, customer mendapat notifikasi untuk mengunggah ulang bukti transfer.

---

## Activity Diagram 5: Pengembalian Mobil
**Gambar 2.5 - Activity Diagram Pengembalian Mobil**

Diagram ini menggambarkan proses pengembalian mobil oleh admin. Admin memilih rental yang sedang berlangsung (status "ongoing") dan memproses pengembalian. Sistem mencatat tanggal pengembalian aktual dan menghitung selisih waktu. Jika terjadi keterlambatan (melebihi grace period 30 menit), sistem menghitung denda per jam. Jika denda belum dibayar, proses pengembalian ditolak hingga customer melunasi denda. Setelah semua terpenuhi, status rental diubah menjadi "completed" dan mobil kembali tersedia.

---

## Activity Diagram 6: Refund / Pembatalan
**Gambar 2.6 - Activity Diagram Refund (Pembatalan Sewa)**

Diagram ini menggambarkan alur pengajuan refund oleh customer dan keputusan oleh admin. Customer yang memiliki rental berstatus "confirmed" dapat mengajukan refund dengan menyertakan alasan pembatalan dan data rekening tujuan. Sistem menyimpan data refund dengan status "requested" dan mengubah status rental menjadi "cancel_requested". Admin meninjau pengajuan dan memutuskan untuk menyetujui (dengan mengunggah bukti transfer refund) atau menolak. Dalam kedua kasus, status rental diubah menjadi "cancelled" dan mobil kembali tersedia.
