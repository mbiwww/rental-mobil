<?php
/**
 * Class BaseHandler — Parent class semua handler (abstract)
 *
 * Menyediakan method utilitas umum yang diwarisi semua handler:
 * - redirect()        : redirect + exit
 * - flashSuccess()    : set session success
 * - flashError()      : set session error
 * - flashRedirect()   : flash + redirect sekaligus
 * - requireMethod()   : validasi HTTP method
 * - requireAuth()     : cek session + role
 * - isPost() / isGet(): cek method request
 * - getAction()       : baca $_POST['action'] atau $_GET['action']
 *
 * Setiap child class wajib mengimplementasikan method dispatch().
 */
abstract class BaseHandler
{
    // Koneksi database
    protected PDO $db;

    /**
     * Constructor — Menginjeksi koneksi PDO
     *
     * @param PDO $db Instance koneksi PDO
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Dispatch — method utama yang harus diimplementasikan child class
     * Membaca action dan meneruskan ke method yang sesuai.
     */
    abstract public function dispatch(): void;

    // -------------------------------------------------------
    // Method Navigasi
    // -------------------------------------------------------

    /**
     * Redirect ke URL tertentu lalu hentikan eksekusi
     *
     * @param string $url URL tujuan redirect
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    // -------------------------------------------------------
    // Method Flash Message
    // -------------------------------------------------------

    /**
     * Set pesan sukses ke session
     *
     * @param string $message Pesan sukses
     */
    protected function flashSuccess(string $message): void
    {
        $_SESSION['success'] = $message;
    }

    /**
     * Set pesan error ke session
     *
     * @param string $message Pesan error
     */
    protected function flashError(string $message): void
    {
        $_SESSION['error'] = $message;
    }

    /**
     * Set flash message (sukses atau error) lalu redirect
     *
     * @param bool   $condition  True = sukses, False = error
     * @param string $successMsg Pesan jika sukses
     * @param string $errorMsg   Pesan jika gagal
     * @param string $url        URL tujuan redirect
     */
    protected function flashRedirect(bool $condition, string $successMsg, string $errorMsg, string $url): void
    {
        if ($condition) {
            $this->flashSuccess($successMsg);
        } else {
            $this->flashError($errorMsg);
        }
        $this->redirect($url);
    }

    // -------------------------------------------------------
    // Method Validasi Request
    // -------------------------------------------------------

    /**
     * Validasi HTTP method — redirect jika tidak sesuai
     *
     * @param string $method   Method yang diharapkan ('POST' atau 'GET')
     * @param string $fallback URL redirect jika method tidak cocok
     */
    protected function requireMethod(string $method, string $fallback): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->redirect($fallback);
        }
    }

    /**
     * Cek apakah user sudah login dan punya role yang dibutuhkan
     * Redirect ke halaman login jika belum/tidak sesuai
     *
     * @param string $role     Role yang dibutuhkan ('customer' atau 'admin')
     * @param string $loginUrl URL halaman login
     */
    protected function requireAuth(string $role, string $loginUrl): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
            $this->redirect($loginUrl);
        }
    }

    /**
     * Cek apakah user sudah login (tanpa memedulikan role)
     * Redirect ke halaman login jika belum login
     *
     * @param string $loginUrl URL halaman login
     */
    protected function requireLogin(string $loginUrl): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect($loginUrl);
        }
    }

    // -------------------------------------------------------
    // Method Helper
    // -------------------------------------------------------

    /**
     * Cek apakah request method adalah POST
     *
     * @return bool
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Cek apakah request method adalah GET
     *
     * @return bool
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Baca action dari POST atau GET
     *
     * @return string Nilai action, atau string kosong jika tidak ada
     */
    protected function getAction(): string
    {
        return $_POST['action'] ?? $_GET['action'] ?? '';
    }
}
