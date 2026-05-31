<?php
/**
 * Class AuthHandler — Handler autentikasi user
 *
 * Menangani semua aksi yang berkaitan dengan autentikasi:
 * - login          : validasi kredensial dan buat session
 * - register       : daftarkan customer baru
 * - logout         : hancurkan session dan redirect ke login
 * - update_profil  : perbarui data diri customer
 * - change_password: ganti password customer (verifikasi password lama)
 * - reset_password : reset password via verifikasi 4 data
 *
 * Aturan: tidak ada output HTML di class ini, selalu redirect+exit.
 */
class AuthHandler extends BaseHandler
{
    // Model user untuk operasi database
    private User $userModel;

    /**
     * Constructor — Inisialisasi model User
     *
     * @param PDO $db Instance koneksi PDO
     */
    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->userModel = new User($db);
    }

    /**
     * Dispatch — Routing aksi berdasarkan nilai action
     */
    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'login':           $this->login();          break;
            case 'register':        $this->register();       break;
            case 'logout':          $this->logout();         break;
            case 'update_profil':   $this->updateProfil();   break;
            case 'change_password': $this->changePassword(); break;
            case 'reset_password':  $this->resetPassword();  break;
            default:                $this->redirect('../index.php');
        }
    }

    // -------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------

    /**
     * Proses login user — validasi email + password, buat session
     * Setelah berhasil, redirect ke URL asal (jika ada dan aman), atau ke dashboard.
     */
    private function login(): void
    {
        $this->requireMethod('POST', '../pages/login.php');

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validasi field wajib
        if (empty($email) || empty($password)) {
            $this->flashError('Email dan password wajib diisi.');
            $this->redirect('../pages/login.php');
        }

        $user = $this->userModel->findByEmail($email);

        // Cek user ada dan password cocok
        if (!$user || !password_verify($password, $user['password'])) {
            $this->flashError('Email atau password salah.');
            $this->redirect('../pages/login.php');
        }

        // Buat session user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        // Admin selalu ke dashboard admin, tanpa mengikuti redirect
        if ($user['role'] === 'admin') {
            $this->flashSuccess('Selamat datang kembali, ' . $user['name'] . '!');
            $this->redirect('../admin/index.php');
        }

        // Baca URL redirect dari form (dikirim sebagai hidden input)
        $redirectTo = trim($_POST['redirect'] ?? '');

        // Set flash sebelum redirect (berlaku untuk semua jalur)
        $this->flashSuccess('Login berhasil! Selamat datang, ' . $user['name'] . '.');

        // Validasi keamanan: tolak URL dengan skema eksternal (http/https) untuk cegah open redirect
        if (!empty($redirectTo) && !preg_match('/^https?:\/\//i', $redirectTo)) {
            $this->redirect($redirectTo);
        }

        // Default: ke halaman utama
        $this->redirect('../index.php');
    }

    // -------------------------------------------------------
    // REGISTER
    // -------------------------------------------------------

    /**
     * Proses registrasi customer baru
     */
    private function register(): void
    {
        $this->requireMethod('POST', '../pages/register.php');

        $name    = trim($_POST['name']                 ?? '');
        $nik     = trim($_POST['nik']                  ?? '');
        $email   = trim($_POST['email']                ?? '');
        $phone   = trim($_POST['phone']                ?? '');
        $address = trim($_POST['address']              ?? '');
        $password = trim($_POST['password']            ?? '');
        $konfirm  = trim($_POST['konfirmasi_password'] ?? '');

        // Validasi field wajib
        if (empty($name) || empty($nik) || empty($email) || empty($phone) || empty($address) || empty($password)) {
            $this->flashError('Semua field wajib diisi.');
            $this->redirect('../pages/register.php');
        }

        // Validasi kekuatan password (5 syarat wajib)
        if (strlen($password) < 8) {
            $this->flashError('Password minimal 8 karakter.');
            $this->redirect('../pages/register.php');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->flashError('Password harus mengandung huruf besar (A–Z).');
            $this->redirect('../pages/register.php');
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->flashError('Password harus mengandung huruf kecil (a–z).');
            $this->redirect('../pages/register.php');
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->flashError('Password harus mengandung angka (0–9).');
            $this->redirect('../pages/register.php');
        }
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $this->flashError('Password harus mengandung karakter khusus (! @ # $ % ^ & *).');
            $this->redirect('../pages/register.php');
        }

        // Validasi konfirmasi password
        if ($password !== $konfirm) {
            $this->flashError('Konfirmasi password tidak cocok.');
            $this->redirect('../pages/register.php');
        }

        // Cek duplikasi email
        if ($this->userModel->findByEmail($email)) {
            $this->flashError('Email sudah terdaftar.');
            $this->redirect('../pages/register.php');
        }

        // Cek duplikasi NIK
        if ($this->userModel->findByNik($nik)) {
            $this->flashError('NIK sudah terdaftar.');
            $this->redirect('../pages/register.php');
        }

        // Simpan ke database (password di-hash di dalam create())
        $result = $this->userModel->create([
            'name'     => $name,
            'nik'      => $nik,
            'email'    => $email,
            'phone'    => $phone,
            'address'  => $address,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role'     => 'customer',
        ]);

        // Teruskan redirect URL ke halaman login agar user bisa kembali ke halaman asal setelah login
        $redirectParam = '';
        if (!empty($_POST['redirect'])) {
            $redirectTo = $_POST['redirect'];
            if (preg_match('/^\/[^\/]/', $redirectTo)) {
                $redirectParam = '?redirect=' . urlencode($redirectTo);
            }
        }

        if ($result) {
            $this->flashSuccess('Registrasi berhasil! Silakan login.');
            $this->redirect('../pages/login.php' . $redirectParam);
        } else {
            $this->flashError('Registrasi gagal. Coba lagi.');
            $this->redirect('../pages/register.php');
        }
    }

    // -------------------------------------------------------
    // LOGOUT
    // -------------------------------------------------------

    /**
     * Proses logout — hancurkan session dan redirect ke login
     */
    private function logout(): void
    {
        // Regenerate session ID agar session lama tidak bisa dipakai kembali,
        // kemudian bersihkan semua data user dari session
        session_regenerate_id(true);

        // Hapus semua data user, sisakan hanya flash message
        $_SESSION = [];
        $_SESSION['success'] = 'Kamu berhasil keluar. Sampai jumpa! 👋';

        $this->redirect('../pages/login.php');
    }

    // -------------------------------------------------------
    // UPDATE PROFIL
    // -------------------------------------------------------

    /**
     * Proses update data diri customer
     */
    private function updateProfil(): void
    {
        $this->requireMethod('POST', '../pages/dashboard.php');
        $this->requireLogin('../pages/login.php');

        $userId  = (int)$_SESSION['user_id'];
        $name    = trim($_POST['name']    ?? '');
        $nik     = trim($_POST['nik']     ?? '');
        $email   = trim($_POST['email']   ?? '');
        $phone   = trim($_POST['phone']   ?? '');
        $address = trim($_POST['address'] ?? '');

        // Validasi field wajib
        if (empty($name) || empty($nik) || empty($email) || empty($phone) || empty($address)) {
            $this->flashError('Semua field profil wajib diisi.');
            $this->redirect('../pages/dashboard.php');
        }

        $result = $this->userModel->updateProfile($userId, [
            'name'    => $name,
            'nik'     => $nik,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
        ]);

        // Perbarui data session jika berhasil
        if ($result) {
            $_SESSION['name']  = $name;
            $_SESSION['email'] = $email;
        }

        $this->flashRedirect(
            $result,
            'Profil berhasil diperbarui.',
            'Gagal memperbarui profil.',
            '../pages/dashboard.php'
        );
    }

    // -------------------------------------------------------
    // GANTI PASSWORD
    // -------------------------------------------------------

    /**
     * Proses ganti password customer (harus tahu password lama)
     */
    private function changePassword(): void
    {
        $this->requireMethod('POST', '../pages/dashboard.php');
        $this->requireLogin('../pages/login.php');

        $userId      = (int)$_SESSION['user_id'];
        $oldPassword = trim($_POST['old_password']         ?? '');
        $newPassword = trim($_POST['new_password']         ?? '');
        $konfirm     = trim($_POST['konfirmasi_password']  ?? '');

        // Validasi field wajib
        if (empty($oldPassword) || empty($newPassword) || empty($konfirm)) {
            $this->flashError('Semua field password wajib diisi.');
            $this->redirect('../pages/dashboard.php');
        }

        // Validasi panjang password baru
        if (strlen($newPassword) < 8) {
            $this->flashError('Password baru minimal 8 karakter.');
            $this->redirect('../pages/dashboard.php');
        }

        // Validasi konfirmasi
        if ($newPassword !== $konfirm) {
            $this->flashError('Konfirmasi password tidak cocok.');
            $this->redirect('../pages/dashboard.php');
        }

        // Verifikasi password lama
        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            $this->flashError('Password lama tidak benar.');
            $this->redirect('../pages/dashboard.php');
        }

        $result = $this->userModel->updatePassword(
            $userId,
            password_hash($newPassword, PASSWORD_DEFAULT)
        );

        $this->flashRedirect(
            $result,
            'Password berhasil diubah.',
            'Gagal mengubah password.',
            '../pages/dashboard.php'
        );
    }

    // -------------------------------------------------------
    // RESET PASSWORD
    // -------------------------------------------------------

    /**
     * Proses reset password via verifikasi 4 data (tanpa token/email)
     */
    private function resetPassword(): void
    {
        $this->requireMethod('POST', '../pages/resetpassword.php');

        $name        = trim($_POST['name']                 ?? '');
        $nik         = trim($_POST['nik']                  ?? '');
        $email       = trim($_POST['email']                ?? '');
        $phone       = trim($_POST['phone']                ?? '');
        $newPassword = trim($_POST['new_password']         ?? '');
        $konfirm     = trim($_POST['konfirmasi_password']  ?? '');

        // Validasi field wajib
        if (empty($name) || empty($nik) || empty($email) || empty($phone) || empty($newPassword)) {
            $this->flashError('Semua field wajib diisi.');
            $this->redirect('../pages/resetpassword.php');
        }

        // Validasi panjang password baru
        if (strlen($newPassword) < 8) {
            $this->flashError('Password baru minimal 8 karakter.');
            $this->redirect('../pages/resetpassword.php');
        }

        // Validasi konfirmasi
        if ($newPassword !== $konfirm) {
            $this->flashError('Konfirmasi password tidak cocok.');
            $this->redirect('../pages/resetpassword.php');
        }

        // Verifikasi 4 data sekaligus
        $user = $this->userModel->verifyIdentity($name, $nik, $email, $phone);
        if (!$user) {
            $this->flashError('Data yang Anda masukkan tidak cocok dengan data kami.');
            $this->redirect('../pages/resetpassword.php');
        }

        $result = $this->userModel->updatePassword(
            $user['id'],
            password_hash($newPassword, PASSWORD_DEFAULT)
        );

        $this->flashRedirect(
            $result,
            'Password berhasil direset. Silakan login dengan password baru.',
            'Gagal mereset password.',
            $result ? '../pages/login.php' : '../pages/resetpassword.php'
        );
    }
}
