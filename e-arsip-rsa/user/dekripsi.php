<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/rsa_helper.php';
require_user_role();

if (!isset($_GET['id'])) {
    die("ID Arsip tidak terdeteksi!");
}

$id_arsip = $_GET['id'];
$stmt = $pdo->prepare("SELECT a.*, k.nama_kategori FROM arsip a JOIN kategori k ON a.id_kategori = k.id_kategori WHERE a.id_arsip = ?");
$stmt->execute([$id_arsip]);
$arsip = $stmt->fetch();

if (!$arsip) {
    die("Arsip Tersebut Tidak Ditemukan Di Server!");
}

$pesan = '';

// Jika tombol Dekripsi ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['private_key_input'])) {
    $private_key = trim($_POST['private_key_input']);
    
    // Alamat asli dari File Terenkripsi pada Sistem kita
    $source_path = '../assets/uploads/' . $arsip['nama_file_enkripsi'];
    
    if (!file_exists($source_path)) {
       $pesan = "<div class='alert alert-danger'>Gagal! Basis data memiliki record arsip ini, namun file aslinya hilang dari root uploads!</div>";
    } else {
       // Melakukan manipulasi penamaan untuk file Output agar mendekati bentuk arsip aslinya sebelum dienkripsi
       // Asumsi file tersimpan sebagai "[Timestamp]___[NamaAsli.PDF].enc"
       $potongan_nama = explode('___', $arsip['nama_file_enkripsi']);
       $nama_asli = isset($potongan_nama[1]) ? $potongan_nama[1] : 'File_Arsip';
       $nama_asli = str_replace('.enc', '', $nama_asli); // Buang .enc nya
       
       // File dekripsi akan disimpan ke dalam buffer/temp untuk sekejap di server
       $temp_file = '../assets/uploads/DECRYPT_BUFFER_' . time() . '_' . $nama_asli;
       
       // TAHAP 5: EKSEKUSI PROSEDUR DEKRIPSI RSA MURNI
       $status_dekripsi = rsa_decrypt_file($source_path, $temp_file, $private_key);
       
       if ($status_dekripsi === true) {
           // Dekripsi sukses, kita arahkan user untuk download filenya melalui routing aman buffer ke memori RAM
           $_SESSION['download_path'] = $temp_file;
           $_SESSION['download_name'] = $nama_asli;
           
           header("Location: download_script.php");
           exit;
       } else {
           $pesan = "<div class='alert alert-danger'>
                        <strong>X</strong> Kunci Privat tidak cocok dengan Sidik Jari Kredensial Algoritma File Ini! Server menolak memproses dekripsi, atau file Anda dinyatakan Corrupt!
                     </div>";
       }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dekripsi File: <?= htmlspecialchars($arsip['judul_arsip']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Portlet Penduduk/Pegawai</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Kembali Ke Daftar Arsip</a></li>
        <li class="nav-item"><a class="nav-link text-warning fw-semibold" href="../logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 mb-3">
                <div class="card-body text-center bg-white rounded">
                    <h4 class="mb-1 text-primary fw-bold"><?= htmlspecialchars($arsip['judul_arsip']) ?></h4>
                    <span class="badge bg-secondary mb-3"><?= htmlspecialchars($arsip['nama_kategori']) ?></span>
                    <p class="text-muted mb-0">Dokumen Arsip Sistem Informasi Desa.</p>
                </div>
            </div>
            
            <div class="card shadow border-0 border-top border-4 border-success">
                <div class="card-header bg-white fw-bold fs-5"><i class="text-success">🔐</i> Buka Berkas Terkunci (Decrypt)</div>
                <div class="card-body p-4 bg-light">
                    <?= $pesan ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-danger fw-bold">Silakan Masukkan Private Key Anda (Buka file .txt yang diberikan Admin menggunakan Notepad dan Copas ke Boks ini):</label>
                            <textarea name="private_key_input" class="form-control" rows="8" placeholder="-----BEGIN PRIVATE KEY----- ... -----END PRIVATE KEY-----" required></textarea>
                            <small class="text-muted">Proses dekripsi mungkin memakan waktu bergantung pada ukuran file arsip (karena RSA sangat mengonsumsi CPU). Mohon agar tidak merefresh page saat diproses berjalan...</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2 fs-5 fw-bold">Verifikasi Kunci & Akses Unduh Dokumen Asli</button>
                        <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>
