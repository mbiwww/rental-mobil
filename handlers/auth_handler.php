<?php
/**
 * Entry Point Handler Autentikasi — handlers/auth_handler.php
 *
 * File bootstrap tipis: hanya require class yang dibutuhkan
 * lalu mendelegasikan semua logika ke AuthHandler::dispatch().
 *
 * Tidak ada logika bisnis di file ini.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/handlers/BaseHandler.php';
require_once __DIR__ . '/../classes/handlers/AuthHandler.php';

$db = Database::getInstance()->getConnection();
(new AuthHandler($db))->dispatch();
