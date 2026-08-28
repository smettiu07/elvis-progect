<?php
declare(strict_types=1);
require __DIR__ . '/api/config.php';
session_start();
if (isset($_POST['password'])) {
    if (hash_equals(ADMIN_PASSWORD, (string)$_POST['password'])) $_SESSION['booking_admin'] = true;
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
if (empty($_SESSION['booking_admin'])) {
?><!doctype html><html><head><meta charset="utf-8"><title>Booking Admin</title><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{font-family:Arial,sans-serif;background:#faf5ea;padding:40px}.box{max-width:420px;margin:10vh auto;background:#fff;padding:30px;border-radius:16px;box-shadow:0 15px 40px #0002}input,button{width:100%;padding:12px;margin-top:10px}button{background:#0e3554;color:#fff;border:0;border-radius:8px}</style></head><body><div class="box"><h1>Booking Admin</h1><p>Enter the admin password configured in <code>api/config.php</code>.</p><form method="post"><input type="password" name="password" required><button>Login</button></form></div></body></html><?php exit; }

if (isset($_POST['reference'], $_POST['status'])) {
    $bookings = load_bookings();
    foreach ($bookings as &$b) if (($b['reference'] ?? '') === $_POST['reference']) $b['status'] = in_array($_POST['status'], ['pending','confirmed','cancelled'], true) ? $_POST['status'] : $b['status'];
    unset($b); save_bookings($bookings);
}
$bookings = array_reverse(load_bookings());
?><!doctype html><html lang="en"><head><meta charset="utf-8"><title>Booking Admin — Adventure Cruises Malta</title><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{font-family:Arial,sans-serif;background:#faf5ea;color:#0a1f30;margin:0}.top{background:#07223a;color:#fff;padding:22px 30px;display:flex;justify-content:space-between}.wrap{padding:25px;overflow:auto}table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden}th,td{padding:12px;border-bottom:1px solid #ddd;text-align:left;vertical-align:top}th{background:#0e3554;color:#fff;font-size:12px}select,button{padding:7px}.status{font-weight:bold}.pending{color:#b56a00}.confirmed{color:#16836f}.cancelled{color:#b23b2f}.muted{color:#667}</style></head><body><div class="top"><strong>Adventure Cruises — Bookings</strong><a href="?logout=1" style="color:#fff">Logout</a></div><div class="wrap"><table><tr><th>Reference</th><th>Date / time</th><th>Customer</th><th>Tour</th><th>People</th><th>Status</th><th>Message</th></tr><?php foreach($bookings as $b): ?><tr><td><strong><?=htmlspecialchars($b['reference'])?></strong><br><span class="muted"><?=htmlspecialchars($b['created_at'] ?? '')?></span></td><td><?=htmlspecialchars($b['date'])?><br><?=htmlspecialchars($b['time'])?></td><td><?=htmlspecialchars($b['fname'].' '.$b['lname'])?><br><?=htmlspecialchars($b['email'])?><br><?=htmlspecialchars($b['phone'])?></td><td><?=htmlspecialchars($b['tour'])?></td><td><?=htmlspecialchars((string)$b['persons'])?></td><td><form method="post"><input type="hidden" name="reference" value="<?=htmlspecialchars($b['reference'])?>"><select name="status" onchange="this.form.submit()"><option value="pending" <?=$b['status']==='pending'?'selected':''?>>Pending</option><option value="confirmed" <?=$b['status']==='confirmed'?'selected':''?>>Confirmed</option><option value="cancelled" <?=$b['status']==='cancelled'?'selected':''?>>Cancelled</option></select></form></td><td><?=nl2br(htmlspecialchars($b['message'] ?? '—'))?></td></tr><?php endforeach; ?></table></div></body></html>
