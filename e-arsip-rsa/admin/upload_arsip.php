<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/rsa_helper.php';

require_admin();

$pesan = '';
$berhasil = false;

// Ambil Kategori untuk combobox dropdown
$stmt = $pdo->query("SELECT * FROM kategori");
$kategoris = $stmt->fetchAll();

// Menangani unggahan file!
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_dinas'])) {
    $judul_arsip = $_POST['judul_arsip'];
    $deskripsi   = $_POST['deskripsi'];
    $id_kategori = $_POST['id_kategori'];
    $id_user     = $_SESSION['user_id'];
    
    $file_tmp  = $_FILES['file_dinas']['tmp_name'];
    $file_name = $_FILES['file_dinas']['name']; // Nama file asli
    
    if (empty($file_tmp)) {
        $pesan = "<div class='alert alert-danger'>Pilih file yang akan diupload!</div>";
    } else {
        // TAHAP 1: Generate Sepasang Kunci RSA!
        // Ini adalah prasyarat Sistem E-Arsip menggunakan Keamanan Kriptografi RSA!
        $kunci_rsa = rsa_generate_keys();
        
        if (!$kunci_rsa) {
            $pesan = "<div class='alert alert-danger'>Kesalahan Sistem: Gagal membuat algoritma RSA. Pastikan ekstensi OpenSSL aktif!</div>";
        } else {
            // TAHAP 2: Buat Folder uploads jika belum ada
            $direktori_upload = '../assets/uploads/';
            if (!is_dir($direktori_upload)) {
                mkdir($direktori_upload, 0777, true);
            }
            
            // Nama file unik terenkripsi (Disimpan sebagai file .enc)
            // Kami menyisipkan nama asli dalam string unik sebelum ekstensi, agar kita tahu meta aslinya nanti saat dekripsi.
            // Contoh format: 169xxxx_file_pdf.enc
            $safe_filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file_name);
            $nama_file_enkripsi = time() . '___' . $safe_filename . '.enc';
            $dest_path = $direktori_upload . $nama_file_enkripsi;
            
            // TAHAP 3: Lakukan Enkripsi Potong-Potong RSA MURNI ke dest_path!
            $status_enkripsi = rsa_encrypt_file($file_tmp, $dest_path, $kunci_rsa['public_key']);
            
            if ($status_enkripsi) {
                // TAHAP 4: Catat Rekam Jejak (Log) dan Simpan Basis Data
                $q_insert = "INSERT INTO arsip (id_kategori, id_user, judul_arsip, deskripsi, nama_file_enkripsi) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $pdo->prepare($q_insert);
                $stmt_insert->execute([$id_kategori, $id_user, $judul_arsip, $deskripsi, $nama_file_enkripsi]);
                
                $berhasil = true;
                
                // Demi keamanan, simpan isi Kunci Privat di SESSION yang mana nanti langsung didownload kemudian dihancurkan.
                // Private Key TIDAK BOLEH disimpan di Database!
                $_SESSION['private_key_temp'] = $kunci_rsa['private_key'];
                
                $pesan = "<div class='alert alert-success fs-5'>
                            <strong>Enkripsi Berhasil!</strong> Arsip <u>{$judul_arsip}</u> telah diproteksi Algoritma RSA. 
                            <hr />
                            <p class='mb-0 text-danger'><i class='fw-bold'>PERINGATAN!</i> Harap unduh dan simpan KUNCI PRIVAT ini. File arsip tidak akan bisa dibuka tanpanya.</p>
                            <a href='download_key.php' class='btn btn-warning mt-3 fw-bold shadow' target='_blank'>⬇️ Unduh File Kunci Privat ($judul_arsip).txt</a>
                         </div>";
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal Melakukan Enkripsi RSA. File terlalu besar atau OpenSSL error.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Arsip & Enkripsi - E-Arsip Desa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Admin E-Arsip</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="upload_arsip.php">Upload & Enkripsi</a></li>
        <li class="nav-item"><a class="nav-link" href="kelola_arsip.php">Kelola Arsip</a></li>
        <li class="nav-item"><a class="nav-link text-warning fw-semibold" href="../logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white fw-bold">Unggah & Enkripsi Data Arsip</div>
                <div class="card-body">
                    <?= $pesan ?>
                    
                    <?php if (!$berhasil): ?>
                    <form action="upload_arsip.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul/Nama Arsip</label>
                            <input type="text" name="judul_arsip" class="form-control" placeholder="Contoh: SK Kades 2026 No.5" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Klasifikasi (Kategori) Arsip</label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">- Pilih Kategori Dokumen -</option>
                                <?php foreach($kategoris as $kat): ?>
                                    <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi / Keterangan Tambahan</label>
                            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-danger">File Dokumen Asli (PDF / DOC / JPG)</label>
                            <input type="file" name="file_dinas" class="form-control" required>
                            <small class="text-muted">File ini akan diproses melalui enkripsi dan hanya akan tersimpan dalam wujud "ciphertext" (.enc) pada server arsip.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fs-5 fw-bold">🔏 Proses Enkripsi dan Upload</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
