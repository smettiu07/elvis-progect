<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'message'=>'Method not allowed.'], 405);

$input = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($input)) json_response(['ok'=>false,'message'=>'Invalid request.'], 400);

$fname = clean((string)($input['fname'] ?? ''), 80);
$lname = clean((string)($input['lname'] ?? ''), 80);
$email = clean((string)($input['email'] ?? ''), 160);
$phone = clean((string)($input['phone'] ?? ''), 50);
$tour = clean((string)($input['tour'] ?? ''), 100);
$date = clean((string)($input['bookingDate'] ?? ''), 10);
$time = clean((string)($input['bookingTime'] ?? ''), 10);
$persons = (int)($input['persons'] ?? 0);
$message = clean((string)($input['message'] ?? ''), 2000);

if (!$fname || !$lname || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$phone || !isset(TOUR_CAPACITY[$tour]) || !$date || !$time || $persons < 1) {
    json_response(['ok'=>false,'message'=>'Please complete all required booking fields.'], 422);
}

$today = new DateTimeImmutable('today');
try { $bookingDate = new DateTimeImmutable($date); } catch (Throwable $e) { json_response(['ok'=>false,'message'=>'Invalid date.'], 422); }
if ($bookingDate < $today) json_response(['ok'=>false,'message'=>'Please choose a future date.'], 422);

$maxPersons = $tour === 'Private Full Day Charter' ? 25 : TOUR_CAPACITY[$tour];
if ($persons > $maxPersons) json_response(['ok'=>false,'message'=>"Maximum for this tour is {$maxPersons} people."], 422);

$file = data_file();
if (!is_dir(dirname($file))) mkdir(dirname($file), 0755, true);
$fp = fopen($file, 'c+');
if (!$fp) json_response(['ok'=>false,'message'=>'Booking storage is unavailable.'], 500);
flock($fp, LOCK_EX);
rewind($fp);
$raw = stream_get_contents($fp);
$bookings = json_decode($raw ?: '[]', true);
if (!is_array($bookings)) $bookings = [];

$used = 0;
foreach ($bookings as $booking) {
    if (($booking['date'] ?? '') !== $date || ($booking['time'] ?? '') !== $time || ($booking['status'] ?? '') === 'cancelled') continue;
    if (($booking['tour'] ?? '') !== $tour) continue;
    if ($tour === 'Private Full Day Charter') { $used = 1; break; }
    $used += (int)($booking['persons'] ?? 0);
}
$capacity = TOUR_CAPACITY[$tour];
if ($tour === 'Private Full Day Charter') {
    $available = $used === 0;
} else {
    $available = ($used + $persons) <= $capacity;
}
if (!$available) {
    flock($fp, LOCK_UN); fclose($fp);
    json_response(['ok'=>false,'message'=>'Sorry, this departure is full. Please choose another date or time.'], 409);
}

$reference = 'ACM-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$booking = [
    'reference' => $reference,
    'created_at' => date(DATE_ATOM),
    'status' => 'pending',
    'fname' => $fname,
    'lname' => $lname,
    'email' => $email,
    'phone' => $phone,
    'tour' => $tour,
    'date' => $date,
    'time' => $time,
    'persons' => $persons,
    'message' => $message,
];
$bookings[] = $booking;
rewind($fp); ftruncate($fp, 0); fwrite($fp, json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); fflush($fp);
flock($fp, LOCK_UN); fclose($fp);




$subject = "New booking request {$reference} — {$tour}";
$body = "New booking request\n\nReference: {$reference}\nName: {$fname} {$lname}\nEmail: {$email}\nPhone: {$phone}\nTour: {$tour}\nDate: {$date}\nTime: {$time}\nPersons: {$persons}\nMessage: " . ($message ?: '—');
$headers = 'From: website@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\nReply-To: {$email}\r\n";
@mail(OWNER_EMAIL, $subject, $body, $headers);
@mail($email, "Booking request received — {$reference}", "Thanks {$fname},\n\nWe received your request for {$tour} on {$date} at {$time}. Reference: {$reference}.\n\nYour booking is pending confirmation. We will contact you shortly.", 'From: ' . OWNER_EMAIL . "\r\n");

json_response(['ok'=>true,'reference'=>$reference]);
