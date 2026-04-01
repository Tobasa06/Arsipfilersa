<?php
/**
 * Skrip Penghapusan Arsip (Backend Processing)
 * Skrip ini tidak memiliki antarmuka (UI). Skrip ini hanya akan memproses `GET` id_arsip,
 * menghapus file fisik di penyimpanan sistem operasi, 
 * menghapus salinan data rekam jejak di database MySQL,
 * lalu mengarahkan ulang admin mundur ke kelola_arsip.php
 */

// Menjalankan memori sesi
session_start();

// Memuat database & modul keamanan
require_once '../config/database.php';
require_once '../includes/auth.php';

// Fitur berbahaya (perusak server). Hanya boleh diakses apabila User adalah Admin!
require_admin();

// Menangkap parameter "id" dari URL yang ditekan di kelola_arsip.php (contoh: hapus_arsip.php?id=5)
if (isset($_GET['id'])) {
    
    // Konversi tipe data string URL ke format bilangan bulat murni untuk mencegah serangan injeksi SQL sederhana
    $id_arsip = (int) $_GET['id'];
    
    /**
     * TAHAP 1: Menemukan Nama File Fisik (File .enc)
     * Sebelum baris dihapus, kita wajib memeriksa tabel arsip
     * untuk mendapatkan `nama_file_enkripsi` agar bisa menargetkan file sungguhannya.
     */
    $stmt_find = $pdo->prepare("SELECT judul_arsip, nama_file_enkripsi FROM arsip WHERE id_arsip = ?");
    $stmt_find->execute([$id_arsip]);
    $file_target = $stmt_find->fetch();
    
    // Logika pengaman: Hanya dilanjutkan JIKA arsip yang dicari memang eksis
    if ($file_target) {
        
        // Membangun direktori path utuh menuju lokasi tempat file enkripsi bertengger pada server
        // Sesuaikan dengan letak pengunggahan aslinya di skrip upload_arsip.php ('../assets/uploads/')
        $lokasi_fisik = '../assets/uploads/' . $file_target['nama_file_enkripsi'];
        
        /**
         * TAHAP 2: Pembasmian Arsip pada Hard-drive
         * Memastikan bahwa file ada di hard disk. Jika ada, gunakan fungsi unlink() bawaan PHP
         * untuk merobek dan menghapusnya selamanya. (Ini membebaskan Storage)
         */
        if (file_exists($lokasi_fisik)) {
            unlink($lokasi_fisik); 
        }
        
        /**
         * TAHAP 3: Pencoretan Database MySQL
         * Menghapus referensi arsip dari daftar tabel arsip.
         */
        $stmt_delete = $pdo->prepare("DELETE FROM arsip WHERE id_arsip = ?");
        $stmt_delete->execute([$id_arsip]);
        
        /**
         * TAHAP 4: Mengembalikan Feedback ke Halaman Utama dengan Pesan Berhasil (Success)
         * Menggunakan peringatan HTML Bootstrap hijau (Alert-Success) 
         */
        $_SESSION['pesan_hapus'] = "
            <div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'>
                <strong>Penghapusan Tuntas:</strong> Dokumen <strong>\"" . htmlspecialchars($file_target['judul_arsip']) . "\"</strong> beserta wujud enkripsinya (.enc) telah musnah selamanya dari penyimpanan Server.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Tutup'></button>
            </div>";
            
    } else {
        // Jika parameter ID ternyata tidak mewakili arsip apa-apa di database
        $_SESSION['pesan_hapus'] = "<div class='alert alert-danger'>Data arsip yang ingin Anda musnahkan justru tidak ditemukan.</div>";
    }
}

// Redirect paksa mundur kembali ke kelola arsip, dan script akan berakhir di baris ini.
header("Location: kelola_arsip.php");
exit;
?>
