<?php
session_start();

// Simpan role pengguna sebelum session dihapus
$role = $_SESSION['role'] ?? 'pendaftar';

// Hapus seluruh data session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect berdasarkan role pengguna
if ($role === 'admin' || $role === 'panitia') {
    // Kembali ke halaman login admin
    header("Location: admin/area/login_admin.php");
} else {
    // Kembali ke halaman utama PPDB
    header("Location: index.php");
}

exit();
?>
