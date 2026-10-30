<?php
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$token = trim($_GET['token'] ?? '');
$error = '';
$success = false;
$userId = null;
if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
    $error = 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.';
} else {
    $tokenHash = hash('sha256', $token);
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expired > NOW() AND role IN ('admin','panitia') LIMIT 1");
    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) $error = 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.';
    else $userId = (int)$user['id'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 8) $error = 'Password minimal 8 karakter.';
    elseif ($password !== $confirm) $error = 'Konfirmasi password tidak cocok.';
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expired = NULL WHERE id = ? AND role IN ('admin','panitia')");
        $update->bind_param('si', $hash, $userId);
        if ($update->execute() && $update->affected_rows === 1) $success = true;
        else $error = 'Password belum dapat diubah. Silakan minta tautan reset baru.';
        if ($success) $userId = null;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PPDB SDN Pasir Panjang 02</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/mobile.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-header"><h1><i class="fas fa-lock"></i> Reset Password</h1><p>Buat password baru Admin/Panitia</p></div>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success" role="status">Password berhasil diubah. Silakan login kembali.</div>
        <a class="btn-primary btn-block" href="admin/area/login_admin.php">Login Admin/Panitia</a>
    <?php elseif ($userId): ?>
        <form method="post" class="login-form" autocomplete="off">
            <div class="form-group"><label for="password">Password Baru</label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required></div>
            <div class="form-group"><label for="confirm_password">Konfirmasi Password</label><input id="confirm_password" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></div>
            <button type="submit" class="btn-primary btn-block">Simpan Password Baru</button>
        </form>
    <?php else: ?><a class="btn-primary btn-block" href="lupa_password.php">Minta Tautan Reset Baru</a><?php endif; ?>
</div>
</body>
</html>
