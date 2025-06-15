<?php
// File: process_transaction.php

// Aktifkan output buffering di awal
ob_start();

// Set header JSON pertama
header('Content-Type: application/json');

// Mulai session hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

$response = ['status' => 'error', 'message' => ''];

try {
    // ... (logika proses transaksi tetap sama) ...
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Bersihkan buffer dan kirim response
ob_end_clean();
echo json_encode($response);
exit;
?>