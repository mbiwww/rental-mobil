<?php
/**
 * Handler Autentikasi — handlers/auth_handler.php
 *
 * Menangani aksi-aksi yang berkaitan dengan autentikasi user:
 * - login       : validasi kredensial dan buat session
 * - register    : daftarkan user baru ke database
 * - update_profil  : perbarui data diri customer
 * - change_password: ganti password customer
 * - reset_password : reset password via verifikasi 4 data
 * - logout      : hancurkan session dan redirect ke login
 *
 * Aturan: file ini hanya berisi logika PHP (tidak ada output HTML).
 * Setelah setiap proses, selalu redirect dan exit.
 */

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

$pdo       = Database::getInstance()->getConnection();
$userModel = new User($pdo);

// Baca action dari GET atau POST
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // -------------------------------------------------------
    // LOGOUT
    // -------------------------------------------------------
    case 'logout':
        // Hancurkan seluruh session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: ../pages/login.php');
        exit;

    // -------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../pages/login.php');
            exit;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validasi input dasar
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email dan password wajib diisi.';
            header('Location: ../pages/login.php');
            exit;
        }

        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Email atau password salah.';
            header('Location: ../pages/login.php');
            exit;
        }

        // Buat session user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        // Redirect berdasarkan role
        if ($user['role'] === 'admin') {
            header('Location: ../admin/index.php');
        } else {
            header('Location: ../pages/dashboard.php');
        }
        exit;

    // -------------------------------------------------------
    // REGISTER
    // -------------------------------------------------------
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../pages/register.php');
            exit;
        }

        $name     = trim($_POST['name']     ?? '');
        $nik      = trim($_POST['nik']      ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $address  = trim($_POST['address']  ?? '');
        $password = trim($_POST['password'] ?? '');
        $konfirm  = trim($_POST['konfirmasi_password'] ?? '');

        // Validasi field wajib
        if (empty($name) || empty($nik) || empty($email) || empty($phone) || empty($address) || empty($password)) {
            $_SESSION['error'] = 'Semua field wajib diisi.';
            header('Location: ../pages/register.php');
            exit;
        }

        // Validasi panjang password
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password minimal 8 karakter.';
            header('Location: ../pages/register.php');
            exit;
        }

        // Validasi konfirmasi password
        if ($password !== $konfirm) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
            header('Location: ../pages/register.php');
            exit;
        }

        // Cek apakah NIK atau email sudah terdaftar
        if ($userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Email sudah terdaftar.';
            header('Location: ../pages/register.php');
            exit;
        }
        if ($userModel->findByNik($nik)) {
            $_SESSION['error'] = 'NIK sudah terdaftar.';
            header('Location: ../pages/register.php');
            exit;
        }

        $result = $userModel->create([
            'name'     => $name,
            'nik'      => $nik,
            'email'    => $email,
            'phone'    => $phone,
            'address'  => $address,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role'     => 'customer',
        ]);

        if ($result) {
            $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
            header('Location: ../pages/login.php');
        } else {
            $_SESSION['error'] = 'Registrasi gagal. Coba lagi.';
            header('Location: ../pages/register.php');
        }
        exit;

    // -------------------------------------------------------
    // UPDATE PROFIL
    // -------------------------------------------------------
    case 'update_profil':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: ../pages/dashboard.php');
            exit;
        }

        $userId  = (int)$_SESSION['user_id'];
        $name    = trim($_POST['name']    ?? '');
        $nik     = trim($_POST['nik']     ?? '');
        $email   = trim($_POST['email']   ?? '');
        $phone   = trim($_POST['phone']   ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name) || empty($nik) || empty($email) || empty($phone) || empty($address)) {
            $_SESSION['error'] = 'Semua field profil wajib diisi.';
            header('Location: ../pages/dashboard.php');
            exit;
        }

        $result = $userModel->updateProfile($userId, [
            'name'    => $name,
            'nik'     => $nik,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
        ]);

        if ($result) {
            // Perbarui data session
            $_SESSION['name']  = $name;
            $_SESSION['email'] = $email;
            $_SESSION['success'] = 'Profil berhasil diperbarui.';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui profil.';
        }
        header('Location: ../pages/dashboard.php');
        exit;

    // -------------------------------------------------------
    // GANTI PASSWORD
    // -------------------------------------------------------
    case 'change_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: ../pages/dashboard.php');
            exit;
        }

        $userId      = (int)$_SESSION['user_id'];
        $oldPassword = trim($_POST['old_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $konfirm     = trim($_POST['konfirmasi_password'] ?? '');

        if (empty($oldPassword) || empty($newPassword) || empty($konfirm)) {
            $_SESSION['error'] = 'Semua field password wajib diisi.';
            header('Location: ../pages/dashboard.php');
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Password baru minimal 8 karakter.';
            header('Location: ../pages/dashboard.php');
            exit;
        }

        if ($newPassword !== $konfirm) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
            header('Location: ../pages/dashboard.php');
            exit;
        }

        $user = $userModel->findById($userId);
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            $_SESSION['error'] = 'Password lama tidak benar.';
            header('Location: ../pages/dashboard.php');
            exit;
        }

        $result = $userModel->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        $_SESSION[$result ? 'success' : 'error'] = $result
            ? 'Password berhasil diubah.'
            : 'Gagal mengubah password.';

        header('Location: ../pages/dashboard.php');
        exit;

    // -------------------------------------------------------
    // RESET PASSWORD (verifikasi 4 data tanpa token/email)
    // -------------------------------------------------------
    case 'reset_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../pages/resetpassword.php');
            exit;
        }

        $name        = trim($_POST['name']         ?? '');
        $nik         = trim($_POST['nik']          ?? '');
        $email       = trim($_POST['email']        ?? '');
        $phone       = trim($_POST['phone']        ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $konfirm     = trim($_POST['konfirmasi_password'] ?? '');

        if (empty($name) || empty($nik) || empty($email) || empty($phone) || empty($newPassword)) {
            $_SESSION['error'] = 'Semua field wajib diisi.';
            header('Location: ../pages/resetpassword.php');
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Password baru minimal 8 karakter.';
            header('Location: ../pages/resetpassword.php');
            exit;
        }

        if ($newPassword !== $konfirm) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
            header('Location: ../pages/resetpassword.php');
            exit;
        }

        // Verifikasi 4 data sekaligus
        $user = $userModel->verifyIdentity($name, $nik, $email, $phone);
        if (!$user) {
            $_SESSION['error'] = 'Data yang Anda masukkan tidak cocok dengan data kami.';
            header('Location: ../pages/resetpassword.php');
            exit;
        }

        $result = $userModel->updatePassword($user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
        if ($result) {
            $_SESSION['success'] = 'Password berhasil direset. Silakan login dengan password baru.';
            header('Location: ../pages/login.php');
        } else {
            $_SESSION['error'] = 'Gagal mereset password.';
            header('Location: ../pages/resetpassword.php');
        }
        exit;

    // -------------------------------------------------------
    // DEFAULT: action tidak dikenal
    // -------------------------------------------------------
    default:
        header('Location: ../index.php');
        exit;
}
