<?php
/**
 * Halaman "Tentang Sekolah" (pengunjung)
 * Visi & Misi, Fasilitas, Guru & Staf, Kegiatan, Ekstrakurikuler, Berita — satu halaman.
 * Konten dikelola di admin/kelola_konten_sekolah.php
 *
 * Detail: aboutsekolah.php?berita=ID  atau  ?kegiatan=ID  (hanya yang Dipublikasikan;
 * admin yang sedang login boleh melihat pratinjau Draft).
 */
require_once 'config/database.php';
require_once 'includes/konten_sekolah.php';

$db   = new Database();
$conn = $db->getConnection();

/* ---------------- Data ---------------- */
$visiRow = ks_fetch_all($conn, "SELECT visi, misi FROM sekolah_visi_misi WHERE id = 1")[0] ?? ['visi' => '', 'misi' => ''];
$visi    = trim((string) ($visiRow['visi'] ?? ''));
$misiArr = ks_misi_items($visiRow['misi'] ?? '');

$fasilitas = ks_fetch_all($conn, "SELECT * FROM sekolah_fasilitas ORDER BY urutan ASC, id ASC");
$guru      = ks_fetch_all($conn, "SELECT * FROM sekolah_guru ORDER BY urutan ASC, id ASC");
$ekskul    = ks_fetch_all($conn, "SELECT * FROM sekolah_ekskul ORDER BY urutan ASC, id ASC");
$kegiatan  = ks_fetch_all($conn,
    "SELECT * FROM sekolah_kegiatan WHERE status = 'dipublikasikan'
     ORDER BY (tanggal_kegiatan IS NULL) ASC, tanggal_kegiatan DESC, id DESC LIMIT 60");
$berita    = ks_fetch_all($conn,
    "SELECT * FROM sekolah_berita WHERE status = 'dipublikasikan'
     ORDER BY tanggal_berita DESC, id DESC LIMIT 60");

/* ---------------- Detail (opsional) ---------------- */
$detail = null;        // ['jenis' => 'berita'|'kegiatan', 'row' => [...]]
$detailMissing = false;
$isAdmin = ks_is_admin();
foreach (['berita' => 'sekolah_berita', 'kegiatan' => 'sekolah_kegiatan'] as $jenis => $table) {
    if (isset($_GET[$jenis])) {
        $row = ks_fetch_one($conn, $table, (int) $_GET[$jenis], !$isAdmin);
        if ($row) {
            $detail = ['jenis' => $jenis, 'row' => $row];
        } else {
            $detailMissing = true;
        }
        break;
    }
}

$pageTitle = 'Tentang Sekolah - SD Negeri Pasir Panjang 02';
if ($detail) {
    $pageTitle = $detail['row']['judul'] . ' - SD Negeri Pasir Panjang 02';
}

/* ---------------- Potongan tampilan ---------------- */
function ab_media(?string $img, string $alt, string $icon): string
{
    $url = ks_img_url($img);
    $inner = $url
        ? '<img src="' . ks_h($url) . '" alt="' . ks_h($alt) . '" loading="lazy">'
        : '<div class="ab-ph" aria-hidden="true"><i class="fas fa-' . $icon . '"></i></div>';
    return '<div class="ab-media">' . $inner . '</div>';
}

function ab_initials(string $name): string
{
    $name = preg_replace('/^(?:(?:Drs?|Hj?|Ir|Prof|S\.?Pd\.?I?|M\.?Pd\.?)\.?\s+)+/iu', '', trim($name)) ?? '';
    $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $ini = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $ini .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return $ini !== '' ? $ini : '?';
}

$kategoriBerita = [];
foreach ($berita as $b) {
    $kategoriBerita[$b['kategori']] = true;
}
$kategoriKegiatan = [];
foreach ($kegiatan as $k) {
    $kategoriKegiatan[$k['kategori']] = true;
}
$PAGE_STEP = 6;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ks_h($pageTitle) ?></title>
    <meta name="description" content="<?= $detail ? ks_h(ks_excerpt($detail['row']['ringkasan'] ?? $detail['row']['deskripsi'] ?? '', 150)) : 'Profil, visi misi, fasilitas, guru, kegiatan, ekstrakurikuler, dan berita SD Negeri Pasir Panjang 02.' ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/mobile.css">
    <link rel="stylesheet" href="assets/css/konten-sekolah.css">
</head>
<body class="ab-body">

<!-- Header (sama dengan index.php) -->
<header class="header">
    <div class="container header-content">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-school"></i></div>
            <div class="logo-text">
                <h1>PPDB SD Negeri Pasir Panjang 02</h1>
                <p>Penerimaan Peserta Didik Baru</p>
            </div>
        </div>
        <button class="mobile-menu-btn" type="button" aria-label="Buka menu" onclick="document.querySelector('.nav').classList.toggle('active')">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="nav">
            <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
            <a href="index.php#informasi"><i class="fas fa-info-circle"></i> Informasi</a>
            <a href="index.php#pengumuman"><i class="fas fa-bullhorn"></i> Pengumuman</a>
            <a href="index.php#kontak"><i class="fas fa-address-book"></i> Kontak</a>
            <a href="aboutsekolah.php" class="active"><i class="fas fa-school"></i> Tentang Sekolah</a>
            <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login Pendaftar</a>
            <a href="register.php" class="btn-register"><i class="fas fa-user-plus"></i> Daftar</a>
        </nav>
    </div>
</header>

<main>
    <section class="ab-hero">
        <div class="ab-wrap">
            <h1>Tentang SD Negeri Pasir Panjang 02</h1>
            <p>Kenali sekolah kami: visi dan misi, fasilitas, guru dan tenaga kependidikan, kegiatan, ekstrakurikuler, serta kabar terbaru.</p>
        </div>
    </section>

    <nav class="ab-subnav" aria-label="Bagian halaman" id="abSubnav">
        <div class="ab-wrap">
            <a href="#visi-misi"><i class="fas fa-bullseye"></i> Visi &amp; Misi</a>
            <a href="#fasilitas"><i class="fas fa-building"></i> Fasilitas</a>
            <a href="#guru-staf"><i class="fas fa-chalkboard-teacher"></i> Guru &amp; Staf</a>
            <a href="#kegiatan"><i class="fas fa-calendar-alt"></i> Kegiatan</a>
            <a href="#ekstrakurikuler"><i class="fas fa-futbol"></i> Ekstrakurikuler</a>
            <a href="#berita"><i class="fas fa-newspaper"></i> Berita</a>
        </div>
    </nav>

<?php if ($detail && !empty($detail['row']) && $detail['row']['status'] !== 'dipublikasikan'): ?>
    <div class="ab-draftbar"><i class="fas fa-eye-slash"></i> Pratinjau Draft — hanya terlihat oleh admin, belum tampil ke pengunjung.</div>
<?php endif; ?>

<?php if ($detailMissing): ?>
    <div class="ab-wrap ab-detail-wrap" id="detail">
        <div class="ab-notfound">
            <i class="fas fa-search fa-2x" style="opacity:.4"></i>
            <h2 style="margin:10px 0 6px">Konten tidak ditemukan</h2>
            <p>Berita atau kegiatan yang Anda cari tidak tersedia atau belum dipublikasikan.</p>
            <p style="margin-top:14px"><a class="ab-back" href="aboutsekolah.php#berita"><i class="fas fa-arrow-left"></i> Kembali ke halaman Tentang Sekolah</a></p>
        </div>
    </div>
<?php elseif ($detail):
    $r = $detail['row'];
    $isBerita = $detail['jenis'] === 'berita';
    $imgName = $isBerita ? ($r['gambar'] ?? '') : ($r['foto'] ?? '');
    $imgUrl = ks_img_url($imgName);
    $tgl = $isBerita ? ks_tanggal($r['tanggal_berita'] ?? '') : ks_tanggal($r['tanggal_kegiatan'] ?? '');
    $lead = $isBerita ? ($r['ringkasan'] ?? '') : ($r['deskripsi'] ?? '');
    $body = trim((string) ($r['isi'] ?? ''));
?>
    <div class="ab-wrap ab-detail-wrap" id="detail">
        <a class="ab-back" href="aboutsekolah.php#<?= $isBerita ? 'berita' : 'kegiatan' ?>"><i class="fas fa-arrow-left"></i> Kembali ke <?= $isBerita ? 'Berita' : 'Kegiatan' ?></a>
        <article class="ab-detail">
            <?php if ($imgUrl): ?>
                <div class="ab-detail-img"><img src="<?= ks_h($imgUrl) ?>" alt="<?= ks_h($r['judul']) ?>"></div>
            <?php endif; ?>
            <div class="ab-detail-body">
                <div class="ab-meta">
                    <span class="ab-chip"><?= ks_h($r['kategori']) ?></span>
                    <?php if ($tgl): ?><span><i class="far fa-calendar"></i> <?= ks_h($tgl) ?></span><?php endif; ?>
                    <?php if (!$isBerita && !empty($r['waktu_kegiatan'])): ?><span><i class="far fa-clock"></i> <?= ks_h($r['waktu_kegiatan']) ?></span><?php endif; ?>
                </div>
                <h1><?= ks_h($r['judul']) ?></h1>
                <?php if ($lead !== '' && $lead !== null): ?><p class="lead"><?= nl2br(ks_h($lead)) ?></p><?php endif; ?>
                <div class="ab-prose"><?= ks_paragraphs_html($body) ?></div>
            </div>
        </article>
    </div>
<?php endif; ?>

    <!-- ================= VISI & MISI ================= -->
    <section id="visi-misi" class="ab-section">
        <div class="ab-wrap">
            <div class="ab-title"><h2><i class="fas fa-bullseye"></i> Visi &amp; Misi</h2></div>
            <?php if ($visi === '' && !$misiArr): ?>
                <div class="ab-empty"><i class="fas fa-bullseye"></i>Visi dan misi sekolah belum tersedia.</div>
            <?php else: ?>
                <div class="ab-vm">
                    <div class="ab-visi">
                        <h3><i class="fas fa-eye"></i> Visi</h3>
                        <?php if ($visi !== ''): ?><p><?= nl2br(ks_h($visi)) ?></p><?php else: ?><p>Belum tersedia.</p><?php endif; ?>
                    </div>
                    <div class="ab-card-plain ab-misi">
                        <h3><i class="fas fa-list-check"></i> Misi</h3>
                        <?php if ($misiArr): ?>
                            <ol>
                                <?php foreach ($misiArr as $m): ?><li><?= ks_h($m) ?></li><?php endforeach; ?>
                            </ol>
                        <?php else: ?><p>Belum tersedia.</p><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ================= FASILITAS ================= -->
    <section id="fasilitas" class="ab-section">
        <div class="ab-wrap">
            <div class="ab-title"><h2><i class="fas fa-building"></i> Fasilitas Sekolah</h2></div>
            <?php if (!$fasilitas): ?>
                <div class="ab-empty"><i class="fas fa-building"></i>Data fasilitas belum tersedia.</div>
            <?php else: ?>
                <div class="ab-grid">
                    <?php foreach ($fasilitas as $f): ?>
                        <article class="ab-card">
                            <?= ab_media($f['foto'], $f['nama'], 'building') ?>
                            <div class="ab-body-txt">
                                <h3><?= ks_h($f['nama']) ?></h3>
                                <?php if (trim((string) $f['deskripsi']) !== ''): ?><p><?= nl2br(ks_h($f['deskripsi'])) ?></p><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ================= GURU & STAF ================= -->
    <section id="guru-staf" class="ab-section">
        <div class="ab-wrap">
            <div class="ab-title"><h2><i class="fas fa-chalkboard-teacher"></i> Guru &amp; Tenaga Kependidikan</h2></div>
            <?php if (!$guru): ?>
                <div class="ab-empty"><i class="fas fa-chalkboard-teacher"></i>Data guru dan staf belum tersedia.</div>
            <?php else: ?>
                <div class="ab-grid guru">
                    <?php foreach ($guru as $g):
                        $gUrl = ks_img_url($g['foto']);
                    ?>
                        <article class="ab-card ab-guru">
                            <div class="ab-media">
                                <?php if ($gUrl): ?>
                                    <img src="<?= ks_h($gUrl) ?>" alt="<?= ks_h($g['nama']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="ab-avatar" aria-hidden="true"><?= ks_h(ab_initials($g['nama'])) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="ab-body-txt">
                                <h3><?= ks_h($g['nama']) ?></h3>
                                <span class="ab-role"><?= ks_h($g['jabatan']) ?></span>
                                <?php if (trim((string) $g['deskripsi']) !== ''): ?><p><?= nl2br(ks_h($g['deskripsi'])) ?></p><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ================= KEGIATAN ================= -->
    <section id="kegiatan" class="ab-section">
        <div class="ab-wrap">
            <div class="ab-title"><h2><i class="fas fa-calendar-alt"></i> Kegiatan Sekolah</h2></div>
            <?php if (!$kegiatan): ?>
                <div class="ab-empty"><i class="fas fa-calendar-alt"></i>Belum ada kegiatan yang dipublikasikan.</div>
            <?php else: ?>
                <?php if (count($kategoriKegiatan) > 1): ?>
                    <div class="ab-filter" data-filter-for="kegiatan-grid" role="group" aria-label="Filter kategori kegiatan">
                        <button type="button" class="active" data-kat="">Semua</button>
                        <?php foreach (array_keys($kategoriKegiatan) as $kat): ?>
                            <button type="button" data-kat="<?= ks_h($kat) ?>"><?= ks_h($kat) ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="ab-grid" id="kegiatan-grid" data-step="<?= $PAGE_STEP ?>">
                    <?php foreach ($kegiatan as $k):
                        $tgl = ks_tanggal($k['tanggal_kegiatan'] ?? '');
                    ?>
                        <article class="ab-card" data-kat="<?= ks_h($k['kategori']) ?>">
                            <?= ab_media($k['foto'], $k['judul'], 'calendar-alt') ?>
                            <div class="ab-body-txt">
                                <div class="ab-meta">
                                    <span class="ab-chip"><?= ks_h($k['kategori']) ?></span>
                                    <?php if ($tgl): ?><span><i class="far fa-calendar"></i> <?= ks_h($tgl) ?></span><?php endif; ?>
                                </div>
                                <h3><?= ks_h($k['judul']) ?></h3>
                                <?php if (!empty($k['waktu_kegiatan'])): ?><div class="ab-meta"><span><i class="far fa-clock"></i> <?= ks_h($k['waktu_kegiatan']) ?></span></div><?php endif; ?>
                                <?php if (trim((string) $k['deskripsi']) !== ''): ?><p><?= ks_h(ks_excerpt($k['deskripsi'], 150)) ?></p><?php endif; ?>
                                <a class="ab-more" href="aboutsekolah.php?kegiatan=<?= (int) $k['id'] ?>#detail">Selengkapnya <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="ab-loadmore ab-hidden" data-more-for="kegiatan-grid"><button type="button">Tampilkan lebih banyak</button></div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ================= EKSTRAKURIKULER ================= -->
    <section id="ekstrakurikuler" class="ab-section">
        <div class="ab-wrap">
            <div class="ab-title"><h2><i class="fas fa-futbol"></i> Ekstrakurikuler</h2></div>
            <?php if (!$ekskul): ?>
                <div class="ab-empty"><i class="fas fa-futbol"></i>Data ekstrakurikuler belum tersedia.</div>
            <?php else: ?>
                <div class="ab-grid">
                    <?php foreach ($ekskul as $e): ?>
                        <article class="ab-card">
                            <?= ab_media($e['foto'], $e['nama'], 'futbol') ?>
                            <div class="ab-body-txt">
                                <h3><?= ks_h($e['nama']) ?></h3>
                                <?php if (trim((string) $e['deskripsi']) !== ''): ?><p><?= nl2br(ks_h($e['deskripsi'])) ?></p><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ================= BERITA ================= -->
    <section id="berita" class="ab-section">
        <div class="ab-wrap">
            <div class="ab-title"><h2><i class="fas fa-newspaper"></i> Blog &amp; Berita Sekolah</h2></div>
            <?php if (!$berita): ?>
                <div class="ab-empty"><i class="fas fa-newspaper"></i>Belum ada berita yang dipublikasikan.</div>
            <?php else: ?>
                <?php if (count($kategoriBerita) > 1): ?>
                    <div class="ab-filter" data-filter-for="berita-grid" role="group" aria-label="Filter kategori berita">
                        <button type="button" class="active" data-kat="">Semua</button>
                        <?php foreach (array_keys($kategoriBerita) as $kat): ?>
                            <button type="button" data-kat="<?= ks_h($kat) ?>"><?= ks_h($kat) ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="ab-grid" id="berita-grid" data-step="<?= $PAGE_STEP ?>">
                    <?php foreach ($berita as $b): ?>
                        <article class="ab-card" data-kat="<?= ks_h($b['kategori']) ?>">
                            <?= ab_media($b['gambar'], $b['judul'], 'newspaper') ?>
                            <div class="ab-body-txt">
                                <div class="ab-meta">
                                    <span class="ab-chip"><?= ks_h($b['kategori']) ?></span>
                                    <span><i class="far fa-calendar"></i> <?= ks_h(ks_tanggal($b['tanggal_berita'])) ?></span>
                                </div>
                                <h3><?= ks_h($b['judul']) ?></h3>
                                <p><?= ks_h(ks_excerpt($b['ringkasan'] !== '' && $b['ringkasan'] !== null ? $b['ringkasan'] : $b['isi'], 150)) ?></p>
                                <a class="ab-more" href="aboutsekolah.php?berita=<?= (int) $b['id'] ?>#detail">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="ab-loadmore ab-hidden" data-more-for="berita-grid"><button type="button">Tampilkan lebih banyak</button></div>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="ab-footer">
    <div class="ab-wrap">
        <p><strong>SD Negeri Pasir Panjang 02</strong> &middot; NPSN 20326230</p>
        <p>
            <a href="index.php">Beranda</a>
            <a href="index.php#informasi">Informasi</a>
            <a href="index.php#pengumuman">Pengumuman</a>
            <a href="index.php#kontak">Kontak</a>
        </p>
    </div>
</footer>

<script>
(function () {
    // Tinggi header tetap → dipakai untuk posisi sub-navigasi & offset anchor
    var header = document.querySelector('.header');
    function setHeaderHeight() {
        if (header) document.documentElement.style.setProperty('--ab-hdr', header.offsetHeight + 'px');
    }
    setHeaderHeight();
    window.addEventListener('resize', setHeaderHeight);
    window.addEventListener('load', setHeaderHeight);

    // Tutup menu mobile setelah klik tautan
    document.querySelectorAll('.nav a').forEach(function (a) {
        a.addEventListener('click', function () { document.querySelector('.nav').classList.remove('active'); });
    });

    // Sorot bagian yang sedang dilihat pada sub-navigasi
    var links = Array.prototype.slice.call(document.querySelectorAll('#abSubnav a'));
    var sections = links.map(function (a) { return document.querySelector(a.getAttribute('href')); });
    function spy() {
        var y = window.scrollY + (header ? header.offsetHeight : 0) + 90, current = -1;
        sections.forEach(function (s, i) { if (s && s.offsetTop <= y) current = i; });
        links.forEach(function (a, i) { a.classList.toggle('active', i === current); });
        if (current > -1) {
            var a = links[current], box = a.parentNode;
            if (a.offsetLeft < box.scrollLeft || a.offsetLeft + a.offsetWidth > box.scrollLeft + box.clientWidth) {
                box.scrollLeft = a.offsetLeft - 20;
            }
        }
    }
    window.addEventListener('scroll', spy, { passive: true });
    spy();

    // Filter kategori + "tampilkan lebih banyak" untuk kegiatan & berita
    document.querySelectorAll('.ab-grid[data-step]').forEach(function (grid) {
        var step = parseInt(grid.dataset.step, 10) || 6;
        var cards = Array.prototype.slice.call(grid.children);
        var more = document.querySelector('[data-more-for="' + grid.id + '"]');
        var filter = document.querySelector('[data-filter-for="' + grid.id + '"]');
        var kat = '', shown = step;

        function render() {
            var matched = cards.filter(function (c) { return kat === '' || c.dataset.kat === kat; });
            cards.forEach(function (c) { c.classList.add('ab-hidden'); });
            matched.forEach(function (c, i) { c.classList.toggle('ab-hidden', i >= shown); });
            if (more) more.classList.toggle('ab-hidden', matched.length <= shown);
        }
        if (filter) filter.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            filter.querySelectorAll('button').forEach(function (x) { x.classList.toggle('active', x === b); });
            kat = b.dataset.kat; shown = step; render();
        });
        if (more) more.querySelector('button').addEventListener('click', function () { shown += step; render(); });
        render();
    });

    // Bila membuka detail, gulir ke artikel
    if (document.getElementById('detail') && location.hash === '#detail') {
        setTimeout(function () { document.getElementById('detail').scrollIntoView({ behavior: 'auto', block: 'start' }); }, 50);
    }
})();
</script>
</body>
</html>
