<?php
// Script ini murni men-serving/mendownloadkan file yang berhasil didekrip
// Lalu menghapusnya di server (agar Kunci Privat tetap penting dan tidak menyisakan salinan raw di sistem)
session_start();
require_once '../includes/auth.php';
require_user_role();

if (isset($_SESSION['download_path']) && file_exists($_SESSION['download_path'])) {
    $file_path = $_SESSION['download_path'];
    // Kami menggunakan basename() agar aman, lalu menambahkan prefix Arsip Desa
    $nama_download = 'DECRYPTED_ARSIP_'.basename($_SESSION['download_name']);
    
    // Membersihkan buffer server sebelum trigger download
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Header memaksa browser untuk mendownload attachment file bytes
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.$nama_download.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    
    // Baca file utuh yang telah didekripsi
    readfile($file_path);
    
    // Hancurkan Buffer File Plaintext di server segera setelah didownload untuk keamanan tingkat tinggi!
    // Ini memastikan bahwa pengguna HARUS selalu punya Private Key jika ingin membongkar lagi di masa depan!
    unlink($file_path);
    
    // Remove Session
    unset($_SESSION['download_path']);
    unset($_SESSION['download_name']);
    
    exit;
} else {
    // Jika path file kosong/telah didownload
    die("<h1>Sesi Kadaluarsa atau File Sudah Diberangus!</h1><p>Anda harus mendekripsi ulang dokumen tersebut dari awal.</p><a href='dashboard.php'>Kembali ke Dashboard</a>");
}
?>
