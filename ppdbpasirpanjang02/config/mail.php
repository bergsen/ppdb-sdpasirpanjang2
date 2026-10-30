<?php
/**
 * Konfigurasi Gmail SMTP untuk PPDB SD Negeri Pasir Panjang 02.
 *
 * Tidak menggunakan .env.
 * Ganti nilai App Password dengan App Password Gmail BARU.
 * Jangan gunakan password login Gmail biasa.
 */

if (!defined('PPDB_GMAIL_USER')) {
    define('PPDB_GMAIL_USER', 'nama@gmail.com');
}

if (!defined('PPDB_GMAIL_APP_PASSWORD')) {
    define('PPDB_GMAIL_APP_PASSWORD', 'qmack--- --- sandi app');
}

/**
 * Domain resmi aplikasi yang digunakan untuk tautan reset password.
 * Pastikan reset_password.php berada di root domain.
 */
if (!defined('PPDB_BASE_URL')) {
    define('PPDB_BASE_URL', 'https://ppdb-pasirpanjang.id');
}

if (!defined('PPDB_RESET_PASSWORD_URL')) {
    define('PPDB_RESET_PASSWORD_URL', PPDB_BASE_URL . '/reset_password.php');
}

/**
 * Membuat tautan reset password.
 */
function getResetPasswordLink(string $token): string
{
    return PPDB_RESET_PASSWORD_URL . '?token=' . urlencode($token);
}

/**
 * Mengirim email menggunakan Gmail SMTP STARTTLS port 587.
 * Nama fungsi dan parameter dipertahankan agar kompatibel dengan aplikasi.
 */
function sendGmailMessage(string $to, string $subject, string $body): bool
{
    $user = trim(PPDB_GMAIL_USER);
    $pass = str_replace(' ', '', PPDB_GMAIL_APP_PASSWORD);

    if (
        !filter_var($user, FILTER_VALIDATE_EMAIL) ||
        !filter_var($to, FILTER_VALIDATE_EMAIL) ||
        $pass === '' ||
        strpos($pass, 'GANTI_DENGAN_') === 0
    ) {
        error_log('PPDB SMTP: konfigurasi email tidak valid atau belum diisi.');
        return false;
    }

    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client(
        'tcp://smtp.gmail.com:587',
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        error_log('PPDB SMTP: koneksi gagal (' . $errno . '): ' . $errstr);
        return false;
    }

    stream_set_timeout($socket, 15);

    $read = static function () use ($socket): string {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    };

    $command = static function (string $cmd, array $expected) use ($socket, $read): bool {
        if (fwrite($socket, $cmd . "\r\n") === false) {
            return false;
        }

        $response = $read();
        foreach ($expected as $code) {
            if (strpos($response, (string) $code) === 0) {
                return true;
            }
        }

        error_log('PPDB SMTP: respons tidak sesuai: ' . trim($response));
        return false;
    };

    try {
        $greeting = $read();
        if (strpos($greeting, '220') !== 0) {
            error_log('PPDB SMTP: sambutan server tidak valid.');
            return false;
        }

        $host = preg_replace(
            '/[^a-zA-Z0-9.-]/',
            '',
            (string) ($_SERVER['HTTP_HOST'] ?? 'ppdb-pasirpanjang.id')
        );
        if ($host === '') {
            $host = 'ppdb-pasirpanjang.id';
        }

        if (!$command('EHLO ' . $host, [250])) {
            return false;
        }

        if (!$command('STARTTLS', [220])) {
            return false;
        }

        if (@stream_socket_enable_crypto(
            $socket,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        ) !== true) {
            error_log('PPDB SMTP: aktivasi TLS gagal.');
            return false;
        }

        if (!$command('EHLO ' . $host, [250])) {
            return false;
        }

        if (
            !$command('AUTH LOGIN', [334]) ||
            !$command(base64_encode($user), [334]) ||
            !$command(base64_encode($pass), [235])
        ) {
            error_log('PPDB SMTP: autentikasi Gmail gagal.');
            return false;
        }

        if (!$command('MAIL FROM:<' . $user . '>', [250])) {
            return false;
        }

        if (!$command('RCPT TO:<' . $to . '>', [250, 251])) {
            return false;
        }

        if (!$command('DATA', [354])) {
            return false;
        }

        // Bersihkan karakter baris baru dari header.
        $safeTo = str_replace(["\r", "\n"], '', $to);
        $safeSubject = str_replace(["\r", "\n"], '', $subject);
        $encodedSubject = '=?UTF-8?B?' . base64_encode($safeSubject) . '?=';

        // Normalisasi baris dan lakukan SMTP dot-stuffing.
        $normalizedBody = str_replace(["\r\n", "\r"], "\n", $body);
        $normalizedBody = preg_replace('/^\./m', '..', $normalizedBody);
        $normalizedBody = str_replace("\n", "\r\n", $normalizedBody);

        $headers =
            'From: PPDB SDN Pasir Panjang 02 <' . $user . ">\r\n" .
            'To: ' . $safeTo . "\r\n" .
            'Subject: ' . $encodedSubject . "\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 8bit\r\n";

        $message = $headers . "\r\n" . $normalizedBody;

        if (fwrite($socket, $message . "\r\n.\r\n") === false) {
            error_log('PPDB SMTP: gagal mengirim isi pesan.');
            return false;
        }

        $response = $read();
        if (strpos($response, '250') === 0) {
            return true;
        }

        error_log('PPDB SMTP: pesan ditolak: ' . trim($response));
        return false;
    } catch (Throwable $e) {
        error_log('PPDB SMTP: terjadi kesalahan saat pengiriman email.');
        return false;
    } finally {
        @fwrite($socket, "QUIT\r\n");
        fclose($socket);
    }
}
