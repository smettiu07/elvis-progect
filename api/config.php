<?php
declare(strict_types=1);

// Change these values before publishing the website.
const OWNER_EMAIL = 'mattyfavoloso@gmail.com';
const ADMIN_PASSWORD = '1234';

// Maximum passengers per public departure. A private charter occupies the boat.
const TOUR_CAPACITY = [
    'Private Full Day Charter' => 1,
    'Crystal Lagoon & Blue Lagoon Full Day' => 40,
    'Special Night Harbour Cruise' => 40,
    'Other / Personalised Tour' => 25,
];

function data_file(): string {
    return dirname(__DIR__) . '/data/bookings.json';
}

function load_bookings(): array {
    $file = data_file();
    if (!is_file($file)) return [];
    $raw = file_get_contents($file);
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function save_bookings(array $bookings): void {
    $file = data_file();
    file_put_contents($file, json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function clean(string $value, int $max = 500): string {
    $value = trim($value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}
