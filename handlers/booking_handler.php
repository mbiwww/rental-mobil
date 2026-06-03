<?php
/**
 * Entry Point Handler Booking — handlers/booking_handler.php
 *
 * File bootstrap tipis: hanya require class yang dibutuhkan
 * lalu mendelegasikan semua logika ke BookingHandler::dispatch().
 *
 * Tidak ada logika bisnis di file ini.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/Car.php';
require_once __DIR__ . '/../classes/Rental.php';
require_once __DIR__ . '/../classes/handlers/BaseHandler.php';
require_once __DIR__ . '/../classes/handlers/BookingHandler.php';

$db = Database::getInstance()->getConnection();
(new BookingHandler($db))->dispatch();
