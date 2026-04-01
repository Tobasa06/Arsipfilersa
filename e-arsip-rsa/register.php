<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, redirect otomatis
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username     = trim($_POST['username']);
    $password     = trim($_POST['password']);
    $role         = $_POST['role'];
    
    // Cek apakah username sudah ada
    $stmt_check = $pdo->prepare("SELECT id_user FROM users WHERE username = ?");
    $stmt_check->execute([$username]);
    if ($stmt_check->rowCount() > 0) {
        $error = "Username '$username' sudah terdaftar! Silakan gunakan username lain.";
    } else {
        // Hash password menggunakan algoritma BCRYPT terbaru yang didukung RSA-Archival ini.
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Simpan User Baru ke Database
        $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)");
        if ($stmt_insert->execute([$username, $hashed_password, $role, $nama_lengkap])) {
            $success = "Pendaftaran berhasil! Akun Anda sudah aktif.";
        } else {
            $error = "Terjadi kesalahan sistem saat mendaftarkan akun.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun - E-Arsip Desa (RSA)</title>
    <!-- Bootstrap CSS dari CDN agar Anda tidak perlu mendownload library secara manual -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #1d2b64 0%, #f8cdda 100%);
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        .register-card { 
            width: 100%; max-width: 450px; padding: 2.5rem; 
            background: white; border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Daftar Akun Baru</h3>
            <p class="text-muted">Aplikasi E-Arsip Desa (RSA)</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success text-center">
                <?= $success ?>
                <hr>
                <a href="index.php" class="btn btn-sm btn-success fw-bold">Login Sekarang</a>
            </div>
        <?php else: ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Username Baru</label>
                <input type="text" name="username" class="form-control" placeholder="username.unik" required pattern="[A-Za-z0-9_]+" title="Hanya huruf, angka, dan garis bawah (_)">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kata Sandi</label>
                <div class="input-group">
                    <input type="password" name="password" id="regPasswordInput" class="form-control" placeholder="Buat kata sandi yang kuat" required minlength="4">
                    <button class="btn btn-outline-secondary bg-white" type="button" id="regTogglePassword" style="border-left: none;">
                        👁️
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Akses Peran (Role)</label>
                <select name="role" class="form-select" required>
                    <option value="user">Warga / Penduduk (User Biasa)</option>
                    <option value="admin">Aparatur Desa (Administrator)</option>
                </select>
                <small class="text-muted">Pilih Hak Akses Anda di Dalam Sistem Arsip.</small>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Daftar Akun</button>
        </form>
        <?php endif; ?>
        
        <div class="text-center mt-4 pt-2 border-top">
            <p class="mb-0 text-muted">Sudah punya akun? <a href="index.php" class="text-decoration-none fw-bold">Masuk di sini</a></p>
        </div>
    </div>
    
    <script>
        const regTogglePassword = document.querySelector('#regTogglePassword');
        const regPassword = document.querySelector('#regPasswordInput');

        if(regTogglePassword && regPassword) {
            regTogglePassword.addEventListener('click', function (e) {
                const type = regPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                regPassword.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
    </script>
</body>
</html>
