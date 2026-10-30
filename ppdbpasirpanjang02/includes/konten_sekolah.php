<?php
/**
 * Fungsi bersama untuk fitur "Tentang Sekolah"
 * dipakai oleh: aboutsekolah.php, index.php (berita beranda), admin/kelola_konten_sekolah.php
 *
 * Membutuhkan config/database.php (sudah memanggil session_start()).
 */

if (defined('KS_HELPER_LOADED')) {
    return;
}
define('KS_HELPER_LOADED', true);

/** Folder upload (di dalam assets/uploads/) dan batas ukuran gambar */
const KS_UPLOAD_SUBDIR = 'sekolah';
const KS_MAX_IMAGE_BYTES = 3145728; // 3 MB

/* ------------------------------------------------------------------ */
/* Dasar                                                               */
/* ------------------------------------------------------------------ */

function ks_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Admin yang sedang login? (dipakai untuk pratinjau konten Draft) */
function ks_is_admin(): bool
{
    return isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin';
}

/* ------------------------------------------------------------------ */
/* CSRF                                                                */
/* ------------------------------------------------------------------ */

function ks_csrf_token(): string
{
    if (empty($_SESSION['ks_csrf'])) {
        $_SESSION['ks_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['ks_csrf'];
}

function ks_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . ks_h(ks_csrf_token()) . '">';
}

function ks_csrf_verify(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && $sent !== '' && !empty($_SESSION['ks_csrf'])
        && hash_equals($_SESSION['ks_csrf'], $sent);
}

/* ------------------------------------------------------------------ */
/* Skema database (aman dijalankan berulang: hanya CREATE IF NOT EXISTS)*/
/* ------------------------------------------------------------------ */

function ks_schema_sql(): array
{
    $opt = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    return [
        'sekolah_visi_misi' => "CREATE TABLE IF NOT EXISTS `sekolah_visi_misi` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `visi` text DEFAULT NULL,
            `misi` text DEFAULT NULL,
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) $opt",
        'sekolah_fasilitas' => "CREATE TABLE IF NOT EXISTS `sekolah_fasilitas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nama` varchar(150) NOT NULL,
            `deskripsi` text DEFAULT NULL,
            `foto` varchar(255) DEFAULT NULL,
            `urutan` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) $opt",
        'sekolah_guru' => "CREATE TABLE IF NOT EXISTS `sekolah_guru` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nama` varchar(150) NOT NULL,
            `jabatan` varchar(150) NOT NULL,
            `deskripsi` text DEFAULT NULL,
            `foto` varchar(255) DEFAULT NULL,
            `urutan` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) $opt",
        'sekolah_kegiatan' => "CREATE TABLE IF NOT EXISTS `sekolah_kegiatan` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `judul` varchar(200) NOT NULL,
            `kategori` varchar(100) NOT NULL DEFAULT 'Umum',
            `tanggal_kegiatan` date DEFAULT NULL,
            `waktu_kegiatan` varchar(100) DEFAULT NULL,
            `deskripsi` text DEFAULT NULL,
            `isi` mediumtext DEFAULT NULL,
            `foto` varchar(255) DEFAULT NULL,
            `status` enum('draft','dipublikasikan') NOT NULL DEFAULT 'draft',
            `tampil_beranda` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_kegiatan_status` (`status`),
            KEY `idx_kegiatan_kategori` (`kategori`)
        ) $opt",
        'sekolah_ekskul' => "CREATE TABLE IF NOT EXISTS `sekolah_ekskul` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nama` varchar(150) NOT NULL,
            `deskripsi` text DEFAULT NULL,
            `foto` varchar(255) DEFAULT NULL,
            `urutan` int(11) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) $opt",
        'sekolah_berita' => "CREATE TABLE IF NOT EXISTS `sekolah_berita` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `judul` varchar(200) NOT NULL,
            `kategori` varchar(100) NOT NULL DEFAULT 'Umum',
            `tanggal_berita` date NOT NULL,
            `ringkasan` text DEFAULT NULL,
            `isi` mediumtext NOT NULL,
            `gambar` varchar(255) DEFAULT NULL,
            `status` enum('draft','dipublikasikan') NOT NULL DEFAULT 'draft',
            `tampil_beranda` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_berita_status` (`status`),
            KEY `idx_berita_kategori` (`kategori`)
        ) $opt",
    ];
}

/**
 * Membuat tabel konten sekolah bila belum ada. Tidak menyentuh tabel lama.
 * Mengembalikan true bila semua tabel tersedia.
 */
function ks_ensure_tables(mysqli $conn): bool
{
    try {
        foreach (ks_schema_sql() as $table => $sql) {
            $res = $conn->query("SHOW TABLES LIKE '" . $table . "'");
            if ($res && $res->num_rows === 0) {
                $conn->query($sql);
            }
        }
        return true;
    } catch (Throwable $e) {
        error_log('[konten_sekolah] gagal menyiapkan tabel: ' . $e->getMessage());
        return false;
    }
}

/* ------------------------------------------------------------------ */
/* Pembersihan / validasi input                                        */
/* ------------------------------------------------------------------ */

/** Rapikan teks: trim, buang karakter kontrol, batasi panjang. Newline dipertahankan. */
function ks_clean_text($value, int $max = 0): string
{
    $value = is_string($value) ? $value : '';
    $value = str_replace("\r\n", "\n", $value);
    $value = preg_replace('/[^\P{C}\n\t]+/u', '', $value) ?? '';
    $value = trim($value);
    if ($max > 0 && mb_strlen($value, 'UTF-8') > $max) {
        $value = mb_substr($value, 0, $max, 'UTF-8');
    }
    return $value;
}

/** Satu baris teks (tanpa newline). */
function ks_clean_line($value, int $max = 0): string
{
    $value = ks_clean_text($value, 0);
    $value = preg_replace('/\s+/u', ' ', $value) ?? '';
    $value = trim($value);
    if ($max > 0 && mb_strlen($value, 'UTF-8') > $max) {
        $value = mb_substr($value, 0, $max, 'UTF-8');
    }
    return $value;
}

/** Kategori bebas: diketik admin, disimpan apa adanya (rapi), default "Umum". */
function ks_clean_kategori($value): string
{
    $k = ks_clean_line($value, 100);
    return $k === '' ? 'Umum' : $k;
}

/** Kembalikan "Y-m-d" jika valid, selain itu null. */
function ks_valid_date($value): ?string
{
    $value = is_string($value) ? trim($value) : '';
    if ($value === '') {
        return null;
    }
    $d = DateTime::createFromFormat('!Y-m-d', $value);
    $err = DateTime::getLastErrors();
    if (!$d || ($err && ($err['warning_count'] || $err['error_count']))) {
        return null;
    }
    return $d->format('Y-m-d') === $value ? $value : null;
}

function ks_tanggal(?string $date): string
{
    if (!$date || $date === '0000-00-00') {
        return '';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return '';
    }
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
              'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

function ks_excerpt(?string $text, int $len = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', (string) $text) ?? '');
    if (mb_strlen($text, 'UTF-8') <= $len) {
        return $text;
    }
    $cut = mb_substr($text, 0, $len, 'UTF-8');
    $sp = mb_strrpos($cut, ' ', 0, 'UTF-8');
    if ($sp !== false && $sp > $len * 0.6) {
        $cut = mb_substr($cut, 0, $sp, 'UTF-8');
    }
    return rtrim($cut, " \t,.;:-") . '…';
}

/** Teks polos → HTML aman: paragraf dipisah baris kosong, baris tunggal jadi <br>. */
function ks_paragraphs_html(?string $text): string
{
    $text = trim(str_replace("\r\n", "\n", (string) $text));
    if ($text === '') {
        return '';
    }
    $out = '';
    foreach (preg_split('/\n{2,}/', $text) as $para) {
        $out .= '<p>' . nl2br(ks_h(trim($para))) . '</p>';
    }
    return $out;
}

/** Teks misi (satu poin per baris, boleh diawali "1.", "-", "•") → array poin. */
function ks_misi_items(?string $text): array
{
    $items = [];
    foreach (preg_split('/\n/', str_replace("\r\n", "\n", (string) $text)) as $line) {
        $line = trim(preg_replace('/^\s*(?:[-*•–]|\d+[.)])\s*/u', '', $line) ?? '');
        if ($line !== '') {
            $items[] = $line;
        }
    }
    return $items;
}

/* ------------------------------------------------------------------ */
/* Upload gambar                                                       */
/* ------------------------------------------------------------------ */

function ks_upload_dir(): string
{
    return dirname(__DIR__) . '/assets/uploads/' . KS_UPLOAD_SUBDIR . '/';
}

/** Pastikan folder ada dan tidak boleh menjalankan skrip di dalamnya. */
function ks_prepare_upload_dir(): bool
{
    $dir = ks_upload_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return false;
    }
    $ht = $dir . '.htaccess';
    if (!file_exists($ht)) {
        @file_put_contents($ht,
            "# Hanya gambar yang boleh disajikan dari folder ini\n" .
            "Options -Indexes -ExecCGI\n" .
            "<FilesMatch \"\\.(php|phtml|php[0-9]|phar|pl|py|cgi|sh)$\">\n" .
            "    Require all denied\n" .
            "</FilesMatch>\n");
    }
    return is_writable($dir);
}

/**
 * Upload satu gambar dengan validasi ketat.
 * @return array ['file' => 'nama.jpg'] jika sukses, ['error' => 'pesan'] jika gagal,
 *               [] jika tidak ada file yang dipilih.
 */
function ks_upload_image(array $file, string $prefix = 'img'): array
{
    $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_NO_FILE) {
        return [];
    }
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return ['error' => 'Ukuran gambar melebihi batas server. Maksimal 3 MB.'];
    }
    if ($err !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload gambar gagal (kode ' . (int) $err . '). Silakan coba lagi.'];
    }
    if (($file['size'] ?? 0) > KS_MAX_IMAGE_BYTES) {
        return ['error' => 'Ukuran gambar terlalu besar. Maksimal 3 MB.'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'File upload tidak valid.'];
    }

    $mime = '';
    if (class_exists('finfo')) {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    }
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return ['error' => 'File harus berupa gambar JPG, PNG, GIF, atau WEBP yang valid.'];
    }
    if (!ks_prepare_upload_dir()) {
        return ['error' => 'Folder upload tidak dapat ditulis. Periksa izin folder assets/uploads/sekolah.'];
    }

    $prefix = preg_replace('/[^a-z0-9]/i', '', $prefix) ?: 'img';
    $name = strtolower($prefix) . '_' . date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], ks_upload_dir() . $name)) {
        return ['error' => 'Gagal menyimpan gambar ke server.'];
    }
    @chmod(ks_upload_dir() . $name, 0644);
    return ['file' => $name];
}

/** Hapus file gambar milik fitur ini (nama dipastikan aman, tidak boleh path). */
function ks_delete_image(?string $name): void
{
    if (!$name || !preg_match('/^[A-Za-z0-9_.-]+$/', $name) || str_contains($name, '..')) {
        return;
    }
    $path = ks_upload_dir() . $name;
    if (is_file($path)) {
        @unlink($path);
    }
}

/** URL gambar relatif; $base = '' dari root, '../' dari folder admin. */
function ks_img_url(?string $name, string $base = ''): string
{
    if (!$name || !preg_match('/^[A-Za-z0-9_.-]+$/', $name)) {
        return '';
    }
    return $base . 'assets/uploads/' . KS_UPLOAD_SUBDIR . '/' . rawurlencode($name);
}

/* ------------------------------------------------------------------ */
/* Query publik                                                        */
/* ------------------------------------------------------------------ */

/** Ambil semua baris sebuah query tanpa parameter (dengan aman bila tabel belum ada). */
function ks_fetch_all(mysqli $conn, string $sql): array
{
    try {
        $res = $conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        error_log('[konten_sekolah] ' . $e->getMessage());
        return [];
    }
}

/** Ambil satu baris berdasarkan id; $onlyPublished=true menyaring Draft. */
function ks_fetch_one(mysqli $conn, string $table, int $id, bool $onlyPublished): ?array
{
    $allowed = ['sekolah_berita', 'sekolah_kegiatan'];
    if (!in_array($table, $allowed, true) || $id <= 0) {
        return null;
    }
    try {
        $sql = "SELECT * FROM `$table` WHERE id = ?" . ($onlyPublished ? " AND status = 'dipublikasikan'" : '');
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    } catch (Throwable $e) {
        error_log('[konten_sekolah] ' . $e->getMessage());
        return null;
    }
}
