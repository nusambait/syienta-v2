<?php
// Pastikan tidak ada whitespace sebelum <?php
ob_start(); // Mulai output buffering
if (!isset($_SESSION)) {
    session_start();
}

// Set error reporting (opsional)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config utama
require_once __DIR__ . '/config.php';

// Fungsi redirect yang aman
function safeRedirect($url)
{
    ob_end_clean();
    header("Location: " . $url);
    exit();
}

// Cek autentikasi (kecuali untuk halaman login)
$current_file = basename($_SERVER['PHP_SELF']);
if ($current_file != '../index.php' && $current_file != 'dashboard.php') {
    if (!isset($_SESSION['username'])) {
        safeRedirect($base_url . "../index.php");
    }
}