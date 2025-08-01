<?php
// Cek apakah pengguna sudah login dan memiliki key_app ADMIN
if (!isset($_SESSION['key_app']) || $_SESSION['key_app'] != 'ADMIN') {
    // Redirect ke halaman login atau dashboard dengan pesan error
    $_SESSION['error_message'] = "Anda tidak memiliki akses ke halaman ini!";
    header("Location: " . $base_url . "dashboard.php");
    exit();
}