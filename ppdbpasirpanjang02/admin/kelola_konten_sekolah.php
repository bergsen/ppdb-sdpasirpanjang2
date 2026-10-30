<?php
/**
 * Kelola Konten Sekolah — satu halaman (tab) untuk:
 * Visi & Misi, Fasilitas, Guru & Staf, Kegiatan, Ekstrakurikuler, Berita.
 * Ditampilkan ke pengunjung di ../aboutsekolah.php
 */
require_once '../config/database.php';
require_once '../includes/konten_sekolah.php';
redirectIfNotAuthorized(['admin']);

$db   = new Database();
$conn = $db->getConnection();
$tablesOk = ks_ensure_tables($conn);

/* ------------------------------------------------------------------ */
/* Definisi tab / jenis konten                                         */
/* ------------------------------------------------------------------ */
$STATUS_OPTIONS = ['draft' => 'Draft', 'dipublikasikan' => 'Dipublikasikan'];

$ENT = [
    'fasilitas' => [
        'table' => 'sekolah_fasilitas', 'label' => 'Fasilitas', 'noun' => 'fasilitas', 'icon' => 'building',
        'image' => 'foto', 'image_label' => 'Foto fasilitas', 'prefix' => 'fasilitas',
        'order' => 'urutan ASC, id ASC', 'has_status' => false, 'anchor' => 'fasilitas',
        'fields' => [
            ['name' => 'nama', 'label' => 'Nama fasilitas', 'type' => 'text', 'max' => 150, 'required' => true, 'wide' => true],
            ['name' => 'deskripsi', 'label' => 'Deskripsi singkat', 'type' => 'textarea', 'max' => 500, 'rows' => 3, 'wide' => true],
            ['name' => 'urutan', 'label' => 'Urutan tampil', 'type' => 'number', 'hint' => 'Angka kecil tampil lebih dulu.'],
        ],
    ],
    'guru' => [
        'table' => 'sekolah_guru', 'label' => 'Guru & Staf', 'noun' => 'guru/staf', 'icon' => 'chalkboard-teacher',
        'image' => 'foto', 'image_label' => 'Foto guru / staf', 'prefix' => 'guru',
        'order' => 'urutan ASC, id ASC', 'has_status' => false, 'anchor' => 'guru-staf',
        'fields' => [
            ['name' => 'nama', 'label' => 'Nama lengkap', 'type' => 'text', 'max' => 150, 'required' => true],
            ['name' => 'jabatan', 'label' => 'Jabatan', 'type' => 'text', 'max' => 150, 'required' => true, 'placeholder' => 'mis. Kepala Sekolah, Guru Kelas 1'],
            ['name' => 'deskripsi', 'label' => 'Deskripsi singkat', 'type' => 'textarea', 'max' => 300, 'rows' => 3, 'wide' => true, 'hint' => 'Maksimal 300 karakter, cukup 1–2 kalimat.'],
            ['name' => 'urutan', 'label' => 'Urutan tampil', 'type' => 'number', 'hint' => 'Angka kecil tampil lebih dulu (mis. Kepala Sekolah = 1).'],
        ],
    ],
    'kegiatan' => [
        'table' => 'sekolah_kegiatan', 'label' => 'Kegiatan', 'noun' => 'kegiatan', 'icon' => 'calendar-alt',
        'image' => 'foto', 'image_label' => 'Foto kegiatan', 'prefix' => 'kegiatan',
        'order' => '(tanggal_kegiatan IS NULL) ASC, tanggal_kegiatan DESC, id DESC', 'has_status' => true, 'anchor' => 'kegiatan',
        'fields' => [
            ['name' => 'judul', 'label' => 'Judul kegiatan', 'type' => 'text', 'max' => 200, 'required' => true, 'wide' => true],
            ['name' => 'kategori', 'label' => 'Kategori', 'type' => 'kategori', 'placeholder' => 'Ketik kategori, mis. Kegiatan Sekolah'],
            ['name' => 'tanggal_kegiatan', 'label' => 'Tanggal kegiatan', 'type' => 'date'],
            ['name' => 'waktu_kegiatan', 'label' => 'Waktu / keterangan waktu', 'type' => 'text', 'max' => 100, 'placeholder' => 'mis. 07.30 – 11.00 WIB'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'status'],
            ['name' => 'deskripsi', 'label' => 'Deskripsi singkat', 'type' => 'textarea', 'max' => 500, 'rows' => 3, 'wide' => true],
            ['name' => 'isi', 'label' => 'Isi lengkap (opsional)', 'type' => 'textarea', 'max' => 20000, 'rows' => 6, 'wide' => true, 'hint' => 'Pisahkan paragraf dengan satu baris kosong.'],
            ['name' => 'tampil_beranda', 'label' => 'Tampilkan di beranda (index.php) bila Dipublikasikan', 'type' => 'check', 'wide' => true],
        ],
    ],
    'ekskul' => [
        'table' => 'sekolah_ekskul', 'label' => 'Ekstrakurikuler', 'noun' => 'ekstrakurikuler', 'icon' => 'futbol',
        'image' => 'foto', 'image_label' => 'Foto ekstrakurikuler', 'prefix' => 'ekskul',
        'order' => 'urutan ASC, id ASC', 'has_status' => false, 'anchor' => 'ekstrakurikuler',
        'fields' => [
            ['name' => 'nama', 'label' => 'Nama ekstrakurikuler', 'type' => 'text', 'max' => 150, 'required' => true, 'wide' => true],
            ['name' => 'deskripsi', 'label' => 'Deskripsi singkat', 'type' => 'textarea', 'max' => 500, 'rows' => 3, 'wide' => true],
            ['name' => 'urutan', 'label' => 'Urutan tampil', 'type' => 'number', 'hint' => 'Angka kecil tampil lebih dulu.'],
        ],
    ],
    'berita' => [
        'table' => 'sekolah_berita', 'label' => 'Berita', 'noun' => 'berita', 'icon' => 'newspaper',
        'image' => 'gambar', 'image_label' => 'Gambar berita', 'prefix' => 'berita',
        'order' => 'tanggal_berita DESC, id DESC', 'has_status' => true, 'anchor' => 'berita',
        'fields' => [
            ['name' => 'judul', 'label' => 'Judul berita', 'type' => 'text', 'max' => 200, 'required' => true, 'wide' => true],
            ['name' => 'kategori', 'label' => 'Kategori', 'type' => 'kategori', 'placeholder' => 'Ketik kategori, mis. Prestasi Siswa'],
            ['name' => 'tanggal_berita', 'label' => 'Tanggal berita', 'type' => 'date', 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'status'],
            ['name' => 'ringkasan', 'label' => 'Ringkasan', 'type' => 'textarea', 'max' => 500, 'rows' => 3, 'wide' => true, 'hint' => 'Kosongkan untuk mengambil otomatis dari awal isi berita.'],
            ['name' => 'isi', 'label' => 'Isi berita', 'type' => 'textarea', 'max' => 50000, 'rows' => 9, 'required' => true, 'wide' => true, 'hint' => 'Pisahkan paragraf dengan satu baris kosong.'],
            ['name' => 'tampil_beranda', 'label' => 'Tampilkan di beranda (index.php) bila Dipublikasikan', 'type' => 'check', 'wide' => true],
        ],
    ],
];

$TABS = ['visi' => ['label' => 'Visi & Misi', 'icon' => 'bullseye']];
foreach ($ENT as $key => $cfg) {
    $TABS[$key] = ['label' => $cfg['label'], 'icon' => $cfg['icon']];
}

$tab = $_GET['tab'] ?? ($_POST['tab'] ?? 'visi');
if (!isset($TABS[$tab])) {
    $tab = 'visi';
}

/* ------------------------------------------------------------------ */
/* Notifikasi (flash) + state                                          */
/* ------------------------------------------------------------------ */
$success = '';
$errors  = [];
if (!empty($_SESSION['ks_flash'])) {
    $success = $_SESSION['ks_flash'];
    unset($_SESSION['ks_flash']);
}
if (!$tablesOk) {
    $errors[] = 'Tabel konten sekolah belum dapat dibuat otomatis. Jalankan file database/migrasi_konten_sekolah.sql melalui phpMyAdmin.';
}

$self = 'kelola_konten_sekolah.php';
function ks_redirect_tab(string $tab, string $msg): void
{
    $_SESSION['ks_flash'] = $msg;
    header('Location: kelola_konten_sekolah.php?tab=' . urlencode($tab));
    exit();
}

/** Nilai form yang dipakai ulang bila terjadi error validasi */
$formValues = null;
$editId = 0;

/* ------------------------------------------------------------------ */
/* Proses POST                                                         */
/* ------------------------------------------------------------------ */
if ($tablesOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors[] = 'Data yang dikirim melebihi batas ukuran server (post_max_size). Perkecil ukuran gambar lalu coba lagi.';
    } elseif (!ks_csrf_verify()) {
        $errors[] = 'Sesi formulir tidak valid atau sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($action === 'save_visi') {
        /* ---------------- Visi & Misi ---------------- */
        $visi = ks_clean_text($_POST['visi'] ?? '', 3000);
        $misi = ks_clean_text($_POST['misi'] ?? '', 8000);
        try {
            $stmt = $conn->prepare(
                "INSERT INTO sekolah_visi_misi (id, visi, misi) VALUES (1, ?, ?)
                 ON DUPLICATE KEY UPDATE visi = VALUES(visi), misi = VALUES(misi)"
            );
            $stmt->bind_param('ss', $visi, $misi);
            $stmt->execute();
            $stmt->close();
            ks_redirect_tab('visi', 'Visi & Misi berhasil disimpan.');
        } catch (Throwable $e) {
            error_log('[konten_sekolah] ' . $e->getMessage());
            $errors[] = 'Gagal menyimpan Visi & Misi. Silakan coba lagi.';
            $formValues = ['visi' => $visi, 'misi' => $misi];
        }
    } elseif (isset($ENT[$tab]) && in_array($action, ['save', 'delete'], true)) {
        $cfg = $ENT[$tab];
        $table = $cfg['table'];
        $imgCol = $cfg['image'];
        $id = (int) ($_POST['id'] ?? 0);

        if ($action === 'delete') {
            /* ---------------- Hapus ---------------- */
            try {
                $stmt = $conn->prepare("SELECT `$imgCol` AS img FROM `$table` WHERE id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$row) {
                    $errors[] = 'Data tidak ditemukan atau sudah dihapus.';
                } else {
                    $stmt = $conn->prepare("DELETE FROM `$table` WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $stmt->close();
                    ks_delete_image($row['img']);
                    ks_redirect_tab($tab, ucfirst($cfg['noun']) . ' berhasil dihapus.');
                }
            } catch (Throwable $e) {
                error_log('[konten_sekolah] ' . $e->getMessage());
                $errors[] = 'Gagal menghapus data. Silakan coba lagi.';
            }
        } else {
            /* ---------------- Tambah / Edit ---------------- */
            $existing = null;
            if ($id > 0) {
                try {
                    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $existing = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                } catch (Throwable $e) {
                    error_log('[konten_sekolah] ' . $e->getMessage());
                }
                if (!$existing) {
                    $errors[] = 'Data yang akan diedit tidak ditemukan.';
                }
            }

            $values = [];
            $types = '';
            if (!$errors) {
                foreach ($cfg['fields'] as $f) {
                    $name = $f['name'];
                    $raw = $_POST[$name] ?? '';
                    switch ($f['type']) {
                        case 'text':
                            $v = ks_clean_line($raw, $f['max']);
                            if (!empty($f['required']) && $v === '') {
                                $errors[] = $f['label'] . ' wajib diisi.';
                            }
                            $values[$name] = $v;
                            $types .= 's';
                            break;
                        case 'textarea':
                            $v = ks_clean_text($raw, $f['max']);
                            if (!empty($f['required']) && $v === '') {
                                $errors[] = $f['label'] . ' wajib diisi.';
                            }
                            $values[$name] = $v;
                            $types .= 's';
                            break;
                        case 'kategori':
                            $values[$name] = ks_clean_kategori($raw);
                            $types .= 's';
                            break;
                        case 'date':
                            $rawStr = is_string($raw) ? trim($raw) : '';
                            $v = ks_valid_date($rawStr);
                            if ($rawStr !== '' && $v === null) {
                                $errors[] = $f['label'] . ' tidak valid.';
                            } elseif (!empty($f['required']) && $v === null) {
                                $errors[] = $f['label'] . ' wajib diisi.';
                            }
                            $values[$name] = $v; // null => NULL di database
                            $types .= 's';
                            break;
                        case 'number':
                            $v = filter_var($raw, FILTER_VALIDATE_INT);
                            $values[$name] = ($v === false || $v < 0) ? 0 : min((int) $v, 9999);
                            $types .= 'i';
                            break;
                        case 'status':
                            $v = is_string($raw) ? $raw : '';
                            if (!isset($STATUS_OPTIONS[$v])) {
                                $errors[] = 'Status harus Draft atau Dipublikasikan.';
                                $v = 'draft';
                            }
                            $values[$name] = $v;
                            $types .= 's';
                            break;
                        case 'check':
                            $values[$name] = !empty($_POST[$name]) ? 1 : 0;
                            $types .= 'i';
                            break;
                    }
                }
                if ($tab === 'berita' && ($values['ringkasan'] ?? '') === '' && ($values['isi'] ?? '') !== '') {
                    $values['ringkasan'] = ks_excerpt($values['isi'], 200);
                }
            }

            // Gambar
            $newImage = null;
            $removeImage = false;
            if (!$errors) {
                $up = ks_upload_image($_FILES[$imgCol] ?? ['error' => UPLOAD_ERR_NO_FILE], $cfg['prefix']);
                if (isset($up['error'])) {
                    $errors[] = $up['error'];
                } elseif (isset($up['file'])) {
                    $newImage = $up['file'];
                } elseif (!empty($_POST['hapus_gambar']) && $existing) {
                    $removeImage = true;
                }
            }

            if (!$errors) {
                try {
                    $cols = array_keys($values);
                    $params = array_values($values);
                    $t = $types;
                    if ($newImage !== null) {
                        $cols[] = $imgCol; $params[] = $newImage; $t .= 's';
                    } elseif ($removeImage) {
                        $cols[] = $imgCol; $params[] = null; $t .= 's';
                    }

                    if ($existing) {
                        $set = implode(', ', array_map(fn($c) => "`$c` = ?", $cols));
                        $stmt = $conn->prepare("UPDATE `$table` SET $set WHERE id = ?");
                        $params[] = $id; $t .= 'i';
                    } else {
                        $colSql = implode(', ', array_map(fn($c) => "`$c`", $cols));
                        $ph = implode(', ', array_fill(0, count($cols), '?'));
                        $stmt = $conn->prepare("INSERT INTO `$table` ($colSql) VALUES ($ph)");
                    }
                    $stmt->bind_param($t, ...$params);
                    $stmt->execute();
                    $stmt->close();

                    if ($existing && ($newImage !== null || $removeImage)) {
                        ks_delete_image($existing[$imgCol]);
                    }
                    ks_redirect_tab($tab, ucfirst($cfg['noun']) . ($existing ? ' berhasil diperbarui.' : ' berhasil ditambahkan.'));
                } catch (Throwable $e) {
                    error_log('[konten_sekolah] ' . $e->getMessage());
                    if ($newImage !== null) {
                        ks_delete_image($newImage);
                    }
                    $errors[] = 'Gagal menyimpan data ke database. Silakan coba lagi.';
                }
            } elseif ($newImage !== null) {
                ks_delete_image($newImage);
            }

            // Ada error → tampilkan form lagi dengan isian terakhir
            $formValues = $values + ($existing ? [$imgCol => $existing[$imgCol]] : []);
            foreach ($cfg['fields'] as $f) {
                if ($f['type'] === 'date' && !array_key_exists($f['name'], $formValues)) {
                    $formValues[$f['name']] = null;
                }
            }
            // tampilkan nilai mentah untuk field yang gagal divalidasi
            foreach ($cfg['fields'] as $f) {
                if (isset($_POST[$f['name']]) && is_string($_POST[$f['name']]) && $f['type'] !== 'check') {
                    $formValues[$f['name']] = $_POST[$f['name']];
                }
                if ($f['type'] === 'check') {
                    $formValues[$f['name']] = !empty($_POST[$f['name']]) ? 1 : 0;
                }
            }
            $editId = $existing ? $id : 0;
        }
    }
}

/* ------------------------------------------------------------------ */
/* Data untuk tampilan                                                 */
/* ------------------------------------------------------------------ */
$visiRow = ['visi' => '', 'misi' => ''];
$rows = [];
$cfg = $ENT[$tab] ?? null;
$statusCounts = ['draft' => 0, 'dipublikasikan' => 0];
$kategoriList = [];
$tabCounts = [];
$statusFilter = '';
$kategoriFilter = '';
$searchQ = '';
$editRow = null;

if ($tablesOk) {
    try {
        // jumlah per tab (semua status)
        foreach ($ENT as $key => $c) {
            $r = $conn->query("SELECT COUNT(*) AS n FROM `{$c['table']}`");
            $tabCounts[$key] = (int) ($r->fetch_assoc()['n'] ?? 0);
        }

        if ($tab === 'visi') {
            $r = $conn->query("SELECT visi, misi FROM sekolah_visi_misi WHERE id = 1");
            if ($r && ($row = $r->fetch_assoc())) {
                $visiRow = $row;
            }
            if ($formValues !== null) {
                $visiRow = $formValues + $visiRow;
            }
        } else {
            $table = $cfg['table'];

            if ($cfg['has_status']) {
                $r = $conn->query("SELECT status, COUNT(*) AS n FROM `$table` GROUP BY status");
                while ($r && ($row = $r->fetch_assoc())) {
                    $statusCounts[$row['status']] = (int) $row['n'];
                }
                $r = $conn->query("SELECT kategori, COUNT(*) AS n FROM `$table` GROUP BY kategori ORDER BY kategori ASC");
                while ($r && ($row = $r->fetch_assoc())) {
                    $kategoriList[$row['kategori']] = (int) $row['n'];
                }

                $sf = $_GET['status'] ?? '';
                $statusFilter = isset($STATUS_OPTIONS[$sf]) ? $sf : '';
                $kategoriFilter = ks_clean_line($_GET['kategori'] ?? '', 100);
            }
            $searchQ = ks_clean_line($_GET['q'] ?? '', 100);

            // Daftar: TANPA filter apa pun secara bawaan → Draft tidak pernah hilang
            $where = [];
            $params = [];
            $ptypes = '';
            if ($statusFilter !== '') {
                $where[] = 'status = ?'; $params[] = $statusFilter; $ptypes .= 's';
            }
            if ($kategoriFilter !== '') {
                $where[] = 'kategori = ?'; $params[] = $kategoriFilter; $ptypes .= 's';
            }
            if ($searchQ !== '') {
                $titleCol = isset($cfg['fields'][0]['name']) ? $cfg['fields'][0]['name'] : 'id';
                $like = '%' . addcslashes($searchQ, '%_\\') . '%';
                $where[] = "`$titleCol` LIKE ?"; $params[] = $like; $ptypes .= 's';
            }
            $sql = "SELECT * FROM `$table`" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY {$cfg['order']}";
            $stmt = $conn->prepare($sql);
            if ($params) {
                $stmt->bind_param($ptypes, ...$params);
            }
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Mode edit
            $reqEdit = $editId ?: (int) ($_GET['edit'] ?? 0);
            if ($reqEdit > 0) {
                $stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ?");
                $stmt->bind_param('i', $reqEdit);
                $stmt->execute();
                $editRow = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$editRow && !$errors) {
                    $errors[] = 'Data yang akan diedit tidak ditemukan.';
                }
            }
        }
    } catch (Throwable $e) {
        error_log('[konten_sekolah] ' . $e->getMessage());
        $errors[] = 'Terjadi kesalahan saat membaca data. Pastikan tabel konten sekolah sudah tersedia.';
    }
}

/** Nilai awal formulir (default / data edit / isian terakhir saat error) */
function ks_form_value(string $name, ?array $editRow, ?array $formValues, array $default = [])
{
    if ($formValues !== null && array_key_exists($name, $formValues)) {
        return $formValues[$name];
    }
    if ($editRow !== null && array_key_exists($name, $editRow)) {
        return $editRow[$name];
    }
    return $default[$name] ?? '';
}

$formDefaults = [
    'status' => 'draft',
    'urutan' => 0,
    'tanggal_berita' => date('Y-m-d'),
    'tampil_beranda' => 0,
];
$csrf = ks_csrf_field();
$namaUser = $_SESSION['nama'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Konten Sekolah - PPDB SDN Pasirpanjang02</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/mobile.css">
    <link rel="stylesheet" href="../assets/css/konten-sekolah.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header">
        <div class="container">
            <h1><i class="fas fa-school"></i> Kelola Konten Sekolah</h1>
            <div class="user-info">
                <div class="user-avatar"><?= ks_h(strtoupper(mb_substr($namaUser, 0, 1))) ?></div>
                <div>
                    <strong><?= ks_h($namaUser) ?></strong>
                    <small><?= ks_h($_SESSION['role'] ?? '') ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="container ks-admin">
        <?php if ($success): ?>
            <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i> <?= ks_h($success) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php if (count($errors) === 1): ?>
                    <?= ks_h($errors[0]) ?>
                <?php else: ?>
                    Mohon periksa kembali:
                    <ul class="ks-errlist">
                        <?php foreach ($errors as $er): ?><li><?= ks_h($er) ?></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="ks-topbar">
            <p>Semua konten halaman <strong>Tentang Sekolah</strong> dikelola dari sini.</p>
            <a class="btn-secondary btn-sm" href="../aboutsekolah.php" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Lihat halaman pengunjung</a>
        </div>

        <!-- Navigasi tab -->
        <nav class="ks-tabs" aria-label="Jenis konten">
            <?php foreach ($TABS as $key => $t): ?>
                <a href="<?= $self ?>?tab=<?= $key ?>" class="ks-tab<?= $tab === $key ? ' active' : '' ?>"<?= $tab === $key ? ' aria-current="page"' : '' ?>>
                    <i class="fas fa-<?= $t['icon'] ?>"></i>
                    <span><?= ks_h($t['label']) ?></span>
                    <?php if (isset($tabCounts[$key])): ?><em><?= $tabCounts[$key] ?></em><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

<?php if ($tab === 'visi'): ?>
        <!-- ===================== VISI & MISI ===================== -->
        <div class="form-section">
            <h2><i class="fas fa-bullseye"></i> Visi &amp; Misi Sekolah</h2>
            <form method="POST" action="<?= $self ?>?tab=visi">
                <?= $csrf ?>
                <input type="hidden" name="action" value="save_visi">
                <input type="hidden" name="tab" value="visi">
                <div class="form-group">
                    <label for="visi">Visi</label>
                    <textarea id="visi" name="visi" rows="4" maxlength="3000" placeholder="Tuliskan visi sekolah"><?= ks_h($visiRow['visi'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="misi">Misi</label>
                    <textarea id="misi" name="misi" rows="8" maxlength="8000" placeholder="Satu poin misi per baris"><?= ks_h($visiRow['misi'] ?? '') ?></textarea>
                    <small class="form-hint">Tulis <strong>satu poin misi per baris</strong>. Penomoran/tanda "-" di awal baris boleh dipakai, akan dirapikan otomatis.</small>
                </div>
                <div class="form-actions ks-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>

<?php else: ?>
        <!-- ===================== FORM TAMBAH / EDIT ===================== -->
        <?php
        $editing = $editRow !== null;
        $currentImg = $editing ? ($editRow[$cfg['image']] ?? '') : '';
        if ($formValues !== null && $editing) {
            $currentImg = $editRow[$cfg['image']] ?? '';
        }
        ?>
        <div class="form-section" id="form-konten">
            <h2>
                <i class="fas fa-<?= $editing ? 'edit' : 'plus' ?>"></i>
                <?= $editing ? 'Edit' : 'Tambah' ?> <?= ks_h($cfg['label']) ?>
            </h2>
            <form method="POST" enctype="multipart/form-data" action="<?= $self ?>?tab=<?= $tab ?>#form-konten" class="ks-form">
                <?= $csrf ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="tab" value="<?= $tab ?>">
                <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>"><?php endif; ?>

                <div class="ks-grid">
                    <?php foreach ($cfg['fields'] as $f):
                        $name = $f['name'];
                        $val = ks_form_value($name, $editRow, $formValues, $formDefaults);
                        $wide = !empty($f['wide']);
                        $req = !empty($f['required']);
                        $id = 'f_' . $name;
                    ?>
                    <div class="form-group<?= $wide ? ' ks-wide' : '' ?>">
                        <?php if ($f['type'] === 'check'): ?>
                            <label class="ks-check">
                                <input type="checkbox" name="<?= $name ?>" value="1" <?= $val ? 'checked' : '' ?>>
                                <span><?= ks_h($f['label']) ?></span>
                            </label>
                        <?php else: ?>
                            <label for="<?= $id ?>"><?= ks_h($f['label']) ?><?= $req ? ' *' : '' ?></label>
                            <?php if ($f['type'] === 'text'): ?>
                                <input type="text" id="<?= $id ?>" name="<?= $name ?>" maxlength="<?= (int) $f['max'] ?>"
                                       value="<?= ks_h($val) ?>" <?= $req ? 'required' : '' ?>
                                       <?= isset($f['placeholder']) ? 'placeholder="' . ks_h($f['placeholder']) . '"' : '' ?>>
                            <?php elseif ($f['type'] === 'textarea'): ?>
                                <textarea id="<?= $id ?>" name="<?= $name ?>" rows="<?= (int) ($f['rows'] ?? 4) ?>"
                                          maxlength="<?= (int) $f['max'] ?>" <?= $req ? 'required' : '' ?>><?= ks_h($val) ?></textarea>
                            <?php elseif ($f['type'] === 'kategori'): ?>
                                <input type="text" id="<?= $id ?>" name="<?= $name ?>" maxlength="100" list="dl-kategori" autocomplete="off"
                                       value="<?= ks_h($val) ?>" placeholder="<?= ks_h($f['placeholder'] ?? '') ?>">
                                <datalist id="dl-kategori">
                                    <?php foreach (array_keys($kategoriList) as $kat): ?><option value="<?= ks_h($kat) ?>"><?php endforeach; ?>
                                </datalist>
                                <small class="form-hint">Bebas diketik. Kategori yang pernah dipakai muncul sebagai saran; kategori baru otomatis tersimpan. Kosong = "Umum".</small>
                            <?php elseif ($f['type'] === 'date'): ?>
                                <input type="date" id="<?= $id ?>" name="<?= $name ?>" value="<?= ks_h($val) ?>" <?= $req ? 'required' : '' ?>>
                            <?php elseif ($f['type'] === 'number'): ?>
                                <input type="number" id="<?= $id ?>" name="<?= $name ?>" min="0" max="9999" step="1" value="<?= (int) $val ?>">
                            <?php elseif ($f['type'] === 'status'): ?>
                                <select id="<?= $id ?>" name="<?= $name ?>">
                                    <?php foreach ($STATUS_OPTIONS as $sv => $sl): ?>
                                        <option value="<?= $sv ?>" <?= $val === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-hint">Draft hanya terlihat oleh admin, tidak tampil ke pengunjung.</small>
                            <?php endif; ?>
                            <?php if (!empty($f['hint']) && $f['type'] !== 'kategori' && $f['type'] !== 'status'): ?>
                                <small class="form-hint"><?= ks_h($f['hint']) ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <!-- Foto / gambar -->
                    <div class="form-group ks-wide">
                        <label for="f_image"><?= ks_h($cfg['image_label']) ?></label>
                        <?php if ($currentImg): ?>
                            <div class="ks-current-img">
                                <img src="<?= ks_h(ks_img_url($currentImg, '../')) ?>" alt="Foto saat ini">
                                <label class="ks-check"><input type="checkbox" name="hapus_gambar" value="1"> <span>Hapus foto ini</span></label>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="f_image" name="<?= $cfg['image'] ?>" accept="image/jpeg,image/png,image/gif,image/webp" data-max="3145728">
                        <small class="form-hint">JPG, PNG, GIF, atau WEBP, maksimal 3 MB.<?= $currentImg ? ' Pilih file baru untuk mengganti foto.' : '' ?></small>
                        <img id="ks-preview" class="ks-preview" alt="" hidden>
                    </div>
                </div>

                <div class="form-actions ks-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <?php if ($editing): ?>
                        <a href="<?= $self ?>?tab=<?= $tab ?>" class="btn-secondary"><i class="fas fa-times"></i> Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ===================== DAFTAR ===================== -->
        <div class="form-section">
            <div class="ks-listhead">
                <h2><i class="fas fa-list"></i> Daftar <?= ks_h($cfg['label']) ?></h2>
                <span class="ks-total">Total: <strong><?= $tabCounts[$tab] ?? 0 ?></strong></span>
            </div>

            <?php if ($cfg['has_status']): ?>
                <div class="ks-filter">
                    <div class="ks-chips" role="group" aria-label="Filter status">
                        <?php
                        $chipBase = $self . '?tab=' . $tab . ($kategoriFilter !== '' ? '&kategori=' . urlencode($kategoriFilter) : '') . ($searchQ !== '' ? '&q=' . urlencode($searchQ) : '');
                        $allCount = array_sum($statusCounts);
                        ?>
                        <a class="ks-chip<?= $statusFilter === '' ? ' active' : '' ?>" href="<?= ks_h($chipBase) ?>">Semua <em><?= $allCount ?></em></a>
                        <a class="ks-chip<?= $statusFilter === 'draft' ? ' active' : '' ?>" href="<?= ks_h($chipBase . '&status=draft') ?>">Draft <em><?= $statusCounts['draft'] ?></em></a>
                        <a class="ks-chip<?= $statusFilter === 'dipublikasikan' ? ' active' : '' ?>" href="<?= ks_h($chipBase . '&status=dipublikasikan') ?>">Dipublikasikan <em><?= $statusCounts['dipublikasikan'] ?></em></a>
                    </div>
                    <form method="GET" action="<?= $self ?>" class="ks-filter-form">
                        <input type="hidden" name="tab" value="<?= $tab ?>">
                        <?php if ($statusFilter !== ''): ?><input type="hidden" name="status" value="<?= ks_h($statusFilter) ?>"><?php endif; ?>
                        <select name="kategori" aria-label="Filter kategori">
                            <option value="">Semua kategori</option>
                            <?php foreach ($kategoriList as $kat => $n): ?>
                                <option value="<?= ks_h($kat) ?>" <?= $kategoriFilter === $kat ? 'selected' : '' ?>><?= ks_h($kat) ?> (<?= $n ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <input type="search" name="q" value="<?= ks_h($searchQ) ?>" placeholder="Cari judul…" aria-label="Cari judul">
                        <button type="submit" class="btn-primary btn-sm"><i class="fas fa-search"></i> Terapkan</button>
                        <?php if ($statusFilter !== '' || $kategoriFilter !== '' || $searchQ !== ''): ?>
                            <a href="<?= $self ?>?tab=<?= $tab ?>" class="btn-secondary btn-sm">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($rows): ?>
            <div class="table-responsive ks-tablewrap">
                <table class="table ks-table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th><?= in_array($tab, ['kegiatan', 'berita'], true) ? 'Judul' : 'Nama' ?></th>
                            <?php if ($tab === 'guru'): ?><th>Jabatan</th><?php endif; ?>
                            <?php if ($cfg['has_status']): ?><th>Kategori</th><th><?= $tab === 'berita' ? 'Tanggal' : 'Tanggal / Waktu' ?></th><th>Status</th><?php else: ?><th>Deskripsi</th><th>Urutan</th><?php endif; ?>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row):
                        $img = $row[$cfg['image']] ?? '';
                        $title = $row['judul'] ?? $row['nama'];
                    ?>
                        <tr>
                            <td data-label="Foto">
                                <?php if ($img): ?>
                                    <img class="ks-thumb" src="<?= ks_h(ks_img_url($img, '../')) ?>" alt="">
                                <?php else: ?>
                                    <span class="ks-thumb ks-thumb-empty"><i class="fas fa-<?= $cfg['icon'] ?>"></i></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= in_array($tab, ['kegiatan', 'berita'], true) ? 'Judul' : 'Nama' ?>">
                                <strong><?= ks_h($title) ?></strong>
                                <?php if ($cfg['has_status'] && !empty($row['tampil_beranda'])): ?>
                                    <br><small class="ks-muted"><i class="fas fa-home"></i> Tampil di beranda</small>
                                <?php endif; ?>
                            </td>
                            <?php if ($tab === 'guru'): ?><td data-label="Jabatan"><?= ks_h($row['jabatan']) ?></td><?php endif; ?>
                            <?php if ($cfg['has_status']): ?>
                                <td data-label="Kategori"><span class="ks-cat"><?= ks_h($row['kategori']) ?></span></td>
                                <td data-label="Tanggal">
                                    <?php
                                    $tgl = $tab === 'berita' ? ($row['tanggal_berita'] ?? '') : ($row['tanggal_kegiatan'] ?? '');
                                    echo $tgl ? ks_h(ks_tanggal($tgl)) : '<span class="ks-muted">—</span>';
                                    if ($tab === 'kegiatan' && !empty($row['waktu_kegiatan'])) {
                                        echo '<br><small class="ks-muted">' . ks_h($row['waktu_kegiatan']) . '</small>';
                                    }
                                    ?>
                                </td>
                                <td data-label="Status">
                                    <span class="ks-status ks-status-<?= ks_h($row['status']) ?>">
                                        <?= ks_h($STATUS_OPTIONS[$row['status']] ?? $row['status']) ?>
                                    </span>
                                </td>
                            <?php else: ?>
                                <td data-label="Deskripsi"><?= ks_h(ks_excerpt($row['deskripsi'] ?? '', 110)) ?: '<span class="ks-muted">—</span>' ?></td>
                                <td data-label="Urutan"><?= (int) $row['urutan'] ?></td>
                            <?php endif; ?>
                            <td class="m-actions ks-rowactions" data-label="Aksi">
                                <a class="btn-primary btn-sm" href="<?= $self ?>?tab=<?= $tab ?>&amp;edit=<?= (int) $row['id'] ?>#form-konten"><i class="fas fa-edit"></i> Edit</a>
                                <?php if ($cfg['has_status']): ?>
                                    <a class="btn-secondary btn-sm" target="_blank" rel="noopener" href="../aboutsekolah.php?<?= $tab ?>=<?= (int) $row['id'] ?>#detail"><i class="fas fa-eye"></i> Pratinjau</a>
                                <?php endif; ?>
                                <form method="POST" action="<?= $self ?>?tab=<?= $tab ?>" class="ks-inline"
                                      onsubmit="return confirm('Hapus <?= ks_h($cfg['noun']) ?> ini? Tindakan ini tidak dapat dibatalkan.');">
                                    <?= $csrf ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="tab" value="<?= $tab ?>">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button type="submit" class="btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-<?= $cfg['icon'] ?> fa-3x"></i>
                    <h3><?= ($statusFilter !== '' || $kategoriFilter !== '' || $searchQ !== '') ? 'Tidak ada data yang cocok dengan filter' : 'Belum ada data ' . ks_h($cfg['noun']) ?></h3>
                    <p><?= ($statusFilter !== '' || $kategoriFilter !== '' || $searchQ !== '') ? 'Klik Reset untuk menampilkan semua.' : 'Tambahkan lewat formulir di atas.' ?></p>
                </div>
            <?php endif; ?>
        </div>
<?php endif; ?>
    </div>
</div>

<script>
// Pratinjau gambar + cek ukuran di sisi klien (validasi sebenarnya tetap di server)
(function () {
    var input = document.getElementById('f_image');
    var preview = document.getElementById('ks-preview');
    if (!input) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) { if (preview) preview.hidden = true; return; }
        if (file.size > parseInt(input.dataset.max, 10)) {
            alert('Ukuran gambar maksimal 3 MB. Pilih gambar yang lebih kecil.');
            input.value = '';
            if (preview) preview.hidden = true;
            return;
        }
        if (preview && file.type.indexOf('image/') === 0) {
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        }
    });
})();
</script>
</body>
</html>
