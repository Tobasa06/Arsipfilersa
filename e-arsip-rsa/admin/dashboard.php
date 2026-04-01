<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_admin(); // Hanya admin

// Mengambil semua kategori beserta jumlah data arsip-nya
$query = "SELECT k.nama_kategori, COUNT(a.id_arsip) as total 
          FROM kategori k 
          LEFT JOIN arsip a ON k.id_kategori = a.id_kategori 
          GROUP BY k.id_kategori";
$stmt = $pdo->query($query);
$statistik = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - E-Arsip Desa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">Admin E-Arsip</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="upload_arsip.php">Upload & Enkripsi</a></li>
          <li class="nav-item"><a class="nav-link" href="kelola_arsip.php">Kelola Arsip</a></li>
          <li class="nav-item"><a class="nav-link text-warning fw-semibold" href="../logout.php">Logout (
              <?= $_SESSION['username'] ?>)
            </a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row mb-4">
      <div class="col-md-12 text-center text-primary">
        <h2>Visi & Misi Kantor Desa</h2>
        <p class="lead text-dark">"Menjadi Desa yang Inovatif, Mandiri, dan Transparan dalam Era Digital Sejahtera."</p>
      </div>
    </div>

    <h4 class="mb-3 text-secondary">Statistik Data E-Arsip</h4>
    <div class="row">
      <?php foreach ($statistik as $stat): ?>
        <div class="col-md-3 mb-4">
          <div class="card shadow-sm border-0 border-top border-4 border-primary h-100">
            <div class="card-body text-center">
              <h5 class="card-title text-uppercase text-muted" style="font-size: 0.9rem;">
                <?= htmlspecialchars($stat['nama_kategori']) ?>
              </h5>
              <h1 class="display-4 fw-bold text-primary">
                <?= $stat['total'] ?>
              </h1>
              <span class="text-secondary">Dokumen Tersertifikasi</span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>

</html>