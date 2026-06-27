<?php
require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/Rental.php';

$db = Database::getInstance()->getConnection();
$rentalModel = new Rental($db);

// Ambil filter dari GET
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';

// Ambil data laporan
$reportData = $rentalModel->getReportData($startDate, $endDate, $filterStatus);

// Set header untuk download Excel/CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_transaksi_' . date('Ymd_His') . '.csv');

// Buka output stream
$output = fopen('php://output', 'w');

// Tambahkan UTF-8 BOM agar Excel membaca karakter khusus / UTF-8 dengan benar
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header kolom
fputcsv($output, [
    'ID',
    'Tanggal',
    'Customer',
    'Mobil',
    'Durasi',
    'Denda (Rp)',
    'Total (Rp)',
    'Status'
]);

// Label status mapping
$statusLabels = [
    'pending' => 'Pending',
    'confirmed' => 'Dikonfirmasi',
    'ongoing' => 'Berjalan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
    'cancel_requested' => 'Minta Batal',
];

// Tulis data transaksi
foreach ($reportData as $r) {
    $days = max(1, (int)((strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400));
    $statusText = $statusLabels[$r['status']] ?? $r['status'];
    $grandTotal = (float)$r['total_price'] + (float)($r['penalty_fee'] ?? 0);
    
    fputcsv($output, [
        '#' . $r['id'],
        date('d M Y', strtotime($r['created_at'])),
        $r['user_name'] . ' (' . $r['user_phone'] . ')',
        $r['brand'] . ' ' . $r['model'] . ' (' . $r['year'] . ')',
        $days . ' hari',
        (float)($r['penalty_fee'] ?? 0),
        $grandTotal,
        $statusText
    ]);
}

fclose($output);
exit;
