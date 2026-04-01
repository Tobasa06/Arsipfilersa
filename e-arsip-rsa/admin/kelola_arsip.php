<?php
/**
 * Halaman Kelola Arsip untuk Administrator
 * Berfungsi untuk melihat daftar seluruh arsip yang ada (ter-enkripsi)
 * dan memberikan opsi penghapusan jika diperlukan.
 */

// Memulai sesi agar dapat menggunakan variabel $_SESSION
session_start();

// Memuat file koneksi ke database dan helper autentikasi
require_once '../config/database.php';
require_once '../includes/auth.php';

// Memastikan hanya user dengan role 'admin' yang bisa mengakses halaman ini
require_admin();

/**
 * Query untuk mengambil semua data arsip dari tabel `arsip`
 * digabung (JOIN) dengan tabel `kategori` untuk mendapatkan nama nama_kategori
 * digabung (JOIN) dengan tabel `users` untuk mengetahui siapa pengunggahnya (Admin siapa)
 * Diurutkan berdasarkan waktu unggah paling terbaru (Descending)
 */
$query = "SELECT a.*, k.nama_kategori, u.nama_lengkap as pengunggah 
          FROM arsip a 
          JOIN kategori k ON a.id_kategori = k.id_kategori 
          JOIN users u ON a.id_user = u.id_user 
          ORDER BY a.tanggal_upload DESC";
$stmt = $pdo->query($query);

// Mengambil seluruh hasil query menjadi array asosiatif
$arsip_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Arsip - E-Arsip Desa (Admin)</title>
    <!-- Memuat library CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Area Navigasi (Navbar) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Admin E-Arsip</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="upload_arsip.php">Upload & Enkripsi</a></li>
        <li class="nav-item"><a class="nav-link active" href="kelola_arsip.php">Kelola Arsip</a></li>
        <li class="nav-item">
            <a class="nav-link text-warning fw-semibold" href="../logout.php">
                Logout (<?= htmlspecialchars($_SESSION['username']) ?>)
            </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Konten Utama: Tabel Daftar Arsip -->
<div class="container mt-4">
    <h3 class="fw-bold mb-4 text-primary">Kelola Dokumen Desa Terenkripsi</h3>
    
    <!-- Menampilkan pesan alert dari session (berasal dari hapus_arsip.php) -->
    <?php if (isset($_SESSION['pesan_hapus'])): ?>
        <?= $_SESSION['pesan_hapus'] ?>
        <!-- Langsung hapus sesi pesan agar tidak muncul terus saat halaman di-refresh -->
        <?php unset($_SESSION['pesan_hapus']); ?>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <!-- Tabel Bootstrap -->
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-dark text-center align-middle">
                    <tr>
                        <th width="5%">No</th>
                        <th>Judul Dokumen</th>
                        <th width="20%">Klasifikasi Arsip</th>
                        <th width="15%">Pengunggah</th>
                        <th width="15%">Waktu Upload</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <!-- Jika belum ada arsip satupun di database -->
                    <?php if (count($arsip_list) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted border-0 py-4">
                                Belum ada dokumen yang diunggah.
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <!-- Looping menampilkan data dari array $arsip_list -->
                    <?php $i=1; foreach($arsip_list as $arsip): ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($arsip['judul_arsip']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($arsip['deskripsi']) ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary"><?= htmlspecialchars($arsip['nama_kategori']) ?></span>
                        </td>
                        <td class="text-center"><?= htmlspecialchars($arsip['pengunggah']) ?></td>
                        <td class="text-center"><?= date('d M Y, H:i', strtotime($arsip['tanggal_upload'])) ?></td>
                        <td class="text-center">
                            <!-- Tombol ini diarahkan ke skrip hapus_arsip.php membawa parameter id arsip -->
                            <!-- Javascript "onclick" dipasang agar memunculkan dialog konfirmasi sebelum hapus sungguhan -->
                            <a href="hapus_arsip.php?id=<?= $arsip['id_arsip'] ?>" 
                               class="btn btn-outline-danger btn-sm fw-bold"
                               onclick="return confirm('Peringatan: Anda yakin ingin menghapus arsip \n\n<?= htmlspecialchars($arsip['judul_arsip']) ?>\n\nbeserta file fisik terenkripsinya secara permanen?');">
                               🗑️ Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
