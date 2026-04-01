<?php
// helpers autentikasi agar dipanggil di tiap halaman dashboard

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: ../index.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        die("<h1>403 Forbidden!</h1><p>Akses ditolak. Anda bukan admin.</p><a href='../user/dashboard.php'>Kembali</a>");
    }
}

function require_user_role() {
    require_login();
    if ($_SESSION['role'] !== 'user') {
        die("<h1>403 Forbidden!</h1><p>Akses ditolak. Layar ini untuk User Penduduk.</p><a href='../admin/dashboard.php'>Kembali</a>");
    }
}
?>
