<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_user_role(); // Hanya boleh user biasa

// Mengambil list daftar Arsip Lengkap
$stmt = $pdo->query("SELECT a.*, k.nama_kategori, u.nama_lengkap as pengunggah 
                     FROM arsip a 
                     JOIN kategori k ON a.id_kategori = k.id_kategori 
                     JOIN users u ON a.id_user = u.id_user 
                     ORDER BY a.tanggal_upload DESC");
$arsip_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Dokumen Arsip - E-Arsip Desa (USER)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Portlet Penduduk/Pegawai</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Daftar Arsip</a></li>
        <li class="nav-item"><a class="nav-link text-warning fw-semibold" href="../logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h3 class="fw-bold mb-4 text-success">Daftar Dokumen Desa Terenkripsi (RSA)</h3>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-dark text-center align-middle">
                    <tr>
                        <th>No</th>
                        <th>Judul Dokumen</th>
                        <th>Klasifikasi Arsip</th>
                        <th>Diunggah Oleh</th>
                        <th>Tanggal</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php if (count($arsip_list) == 0): ?>
                        <tr><td colspan="6" class="text-center text-muted border-0 py-4">Belum ada dokumen yang diunggah Admin.</td></tr>
                    <?php endif; ?>
                    
                    <?php $i=1; foreach($arsip_list as $arsip): ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($arsip['judul_arsip']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($arsip['deskripsi']) ?></small>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary"><?= htmlspecialchars($arsip['nama_kategori']) ?></span></td>
                        <td class="text-center"><?= htmlspecialchars($arsip['pengunggah']) ?></td>
                        <td class="text-center"><?= date('d M Y, H:i', strtotime($arsip['tanggal_upload'])) ?></td>
                        <td class="text-center">
                            <a href="dekripsi.php?id=<?= $arsip['id_arsip'] ?>" class="btn btn-outline-success btn-sm fw-bold">Buka (Dekripsi)</a>
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
