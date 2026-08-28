<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['ok'=>false,'message'=>'Method not allowed.'], 405);

$tour = clean((string)($_GET['tour'] ?? ''), 100);
$date = clean((string)($_GET['date'] ?? ''), 10);
$time = clean((string)($_GET['time'] ?? ''), 10);

if (!isset(TOUR_CAPACITY[$tour]) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    json_response(['ok'=>false,'message'=>'Invalid availability request.'], 422);
}

$bookings = load_bookings();
$used = 0;
foreach ($bookings as $booking) {
    if (($booking['date'] ?? '') !== $date || ($booking['time'] ?? '') !== $time || ($booking['status'] ?? '') === 'cancelled') continue;
    if (($booking['tour'] ?? '') !== $tour) continue;
    if ($tour === 'Private Full Day Charter') { $used = 1; break; }
    $used += (int)($booking['persons'] ?? 0);
}
$capacity = TOUR_CAPACITY[$tour];
$remaining = max(0, $capacity - $used);
json_response(['ok'=>true,'available'=>$remaining > 0,'remaining'=>$remaining,'capacity'=>$capacity]);
