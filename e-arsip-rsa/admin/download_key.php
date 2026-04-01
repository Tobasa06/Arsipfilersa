<?php
session_start();
require_once '../includes/auth.php';
require_admin();

if (isset($_SESSION['private_key_temp'])) {
    $key_content = $_SESSION['private_key_temp'];
    // SESSION tidak langsung di-unset di sini karena beberapa browser/koneksi mengirimkan multi-request (HEAD lalu GET) yang bisa menyebabkan kegagalan download.

    // Set headers untuk force file download berwujud txt
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="KUNCI_PRIVAT_ARSIP_' . date('Ymd_His') . '.txt"');
    header('Content-Length: ' . strlen($key_content));

    // Output private key
    echo $key_content;
    exit;
} else {
    echo "Kunci Privat tidak ditemukan di memori atau kadaluarsa. Sistem Enkripsi ini tidak menyimpan Private Key Anda demi keamanan.";
}
?>