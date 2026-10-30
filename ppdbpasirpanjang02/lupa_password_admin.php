<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
$db = new Database(); $conn = $db->getConnection();
$message = ''; $isError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $message='Masukkan alamat email yang valid.'; $isError=true; }
    else {
        $stmt=$conn->prepare("SELECT id, nama, username, email FROM users WHERE email=? AND role IN ('admin','panitia') LIMIT 1");
        $stmt->bind_param('s',$email); $stmt->execute(); $account=$stmt->get_result()->fetch_assoc();
        // Respons generik agar keberadaan akun tidak bocor.
        if ($account) {
            $token=bin2hex(random_bytes(32)); $hash=hash('sha256',$token); $expiry=date('Y-m-d H:i:s',time()+1800);
            $up=$conn->prepare('UPDATE users SET reset_token=?, reset_expired=? WHERE id=?');
            $up->bind_param('ssi',$hash,$expiry,$account['id']); $up->execute();
            $url = getResetPasswordLink($token);
            $body="Halo ".($account['nama']??$account['username']??'Admin').",\n\nKami menerima permintaan reset password akun Admin/Panitia PPDB. Buka tautan berikut dalam 30 menit:\n\n".$url."\n\nJika bukan Anda yang meminta, abaikan email ini. Jangan bagikan tautan ini kepada siapa pun.";
            if (!sendGmailMessage($email,'Reset Password PPDB SDN Pasir Panjang 02',$body)) { $message='Email belum dapat dikirim. Pastikan config/mail.php sudah diisi dengan Gmail dan Google App Password, serta hosting mengizinkan SMTP.'; $isError=true; }
        }
        if (!$isError) $message='Jika email tersebut terdaftar sebagai Admin/Panitia, tautan reset akan dikirim ke Gmail. Periksa Kotak Masuk dan Spam.';
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lupa Password Admin/Panitia</title><link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/global.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><style>body{background:#f3f6fa;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}.reset-box{background:#fff;width:100%;max-width:460px;padding:clamp(20px,5vw,32px);border-radius:16px;box-shadow:0 8px 28px #1d35571a}.reset-box h1{font-size:1.5rem;color:#245b86}.reset-box p{line-height:1.6;color:#536273}.reset-box label{display:block;font-weight:600;margin:16px 0 8px}.reset-box input{width:100%;min-width:0;padding:12px;border:1px solid #cbd5e1;border-radius:8px}.reset-box button{width:100%;margin-top:16px;padding:12px;border:0;border-radius:8px;background:#496fa8;color:white;font-weight:700}.notice{padding:12px;border-radius:8px;background:#eaf4ff;overflow-wrap:anywhere}.err{background:#fff0f0;color:#9b2525}.back{display:block;text-align:center;margin-top:18px}</style>    <link rel="stylesheet" href="assets/css/mobile.css">
</head><body><main class="reset-box"><h1><i class="fa-solid fa-envelope"></i> Lupa Password</h1><p>Khusus akun Admin dan Panitia. Masukkan Gmail yang terdaftar. Kami akan mengirimkan tautan untuk membuat password baru.</p>
<?php if($message!==''): ?><div class="notice <?= $isError?'err':'' ?>"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
<form method="post" action=""><label for="email">Email Gmail terdaftar</label><input type="email" id="email" name="email" autocomplete="email" required maxlength="190" placeholder="nama@gmail.com"><button type="submit"><i class="fa-solid fa-paper-plane"></i> Kirim Link Reset</button></form><a class="back" href="admin/area/login_admin.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login Admin</a></main></body></html>
