<?php
require_once 'config/database.php';
require_once 'includes/konten_sekolah.php';
$db = new Database();
$conn = $db->getConnection();

// Berita & kegiatan sekolah yang ditandai "tampil di beranda" (hanya yang Dipublikasikan)
$kabarSekolah = ks_fetch_all($conn,
    "SELECT * FROM (
        SELECT 'berita' AS jenis, id, judul, kategori, tanggal_berita AS tgl, ringkasan AS ringkas, gambar AS img
          FROM sekolah_berita WHERE status = 'dipublikasikan' AND tampil_beranda = 1
        UNION ALL
        SELECT 'kegiatan' AS jenis, id, judul, kategori, tanggal_kegiatan AS tgl, deskripsi AS ringkas, foto AS img
          FROM sekolah_kegiatan WHERE status = 'dipublikasikan' AND tampil_beranda = 1
     ) k ORDER BY (tgl IS NULL) ASC, tgl DESC, id DESC LIMIT 3");

// Ambil informasi untuk ditampilkan
$sqlInfo = "SELECT * FROM informasi WHERE jenis != 'profil' ORDER BY id DESC LIMIT 6";
$resultInfo = $conn->query($sqlInfo);

$sqlPengumuman = "SELECT * FROM pengumuman WHERE tampil_index = 'ya' ORDER BY id DESC LIMIT 4";
$resultPengumuman = $conn->query($sqlPengumuman);

// Ambil informasi sekolah (profil)
$sqlProfil = "SELECT * FROM informasi WHERE jenis = 'profil' LIMIT 1";
$resultProfil = $conn->query($sqlProfil);
$profil = $resultProfil->fetch_assoc();

// Ambil tahun ajaran aktif
$sqlTahun = "SELECT * FROM tahun_ajaran WHERE status = 'aktif' LIMIT 1";
$resultTahun = $conn->query($sqlTahun);
$tahunAjaran = $resultTahun->fetch_assoc();

$status = 'tutup'; // default

if ($tahunAjaran && !empty($tahunAjaran['tanggal_buka']) && !empty($tahunAjaran['tanggal_tutup'])) {

    $today = date('Y-m-d');

    if ($today < $tahunAjaran['tanggal_buka']) {
        $status = 'belum';
    } elseif ($today > $tahunAjaran['tanggal_tutup']) {
        $status = 'tutup';
    } else {
        $status = 'aktif';
    }
}

$statusText  = 'Pendaftaran Ditutup';
$statusClass = 'tutup';

if ($tahunAjaran && !empty($tahunAjaran['tanggal_buka']) && !empty($tahunAjaran['tanggal_tutup'])) {

    $today = date('Y-m-d');

    if ($today < $tahunAjaran['tanggal_buka']) {
        $statusText  = 'Belum Dibuka';
        $statusClass = 'belum';
    } elseif ($today > $tahunAjaran['tanggal_tutup']) {
        $statusText  = 'Sudah Ditutup';
        $statusClass = 'tutup';
    } else {
        $statusText  = 'Sedang Berlangsung';
        $statusClass = 'aktif';
    }
}
// Hitung statistik
$sqlStats = "SELECT 
    COUNT(*) as total_pendaftar,
    COUNT(CASE WHEN status_terima = 'diterima' THEN 1 END) as diterima,
    COUNT(CASE WHEN status_terima = 'cadangan' THEN 1 END) as cadangan
    FROM calon_siswa cs
    JOIN tahun_ajaran ta ON cs.tahun_ajaran_id = ta.id
    WHERE ta.status = 'aktif'";
$resultStats = $conn->query($sqlStats);
$stats = $resultStats->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB SD Negeri Pasirpanjan 02 -PPDB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            
            color: #333;
            background: url("./assets/sd2pasirpanjang.png") no-repeat center center fixed;

        }
        
    </style>
    <link rel="stylesheet" href="assets/css/mobile.css">
    <link rel="stylesheet" href="assets/css/konten-sekolah.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-school"></i>
                </div>
                <div class="logo-text">
                    <h1>PPDB SD Negeri Pasir Panjang 02</h1>
                    <p>Penerimaan Peserta Didik Baru</p>
                </div>
            </div>
            <button class="mobile-menu-btn" onclick="document.querySelector('.nav').classList.toggle('active')">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="nav">
                <a href="#home"><i class="fas fa-home"></i> Beranda</a>
                <a href="#informasi"><i class="fas fa-info-circle"></i> Informasi</a>
                <a href="#pengumuman"><i class="fas fa-bullhorn"></i> Pengumuman</a>
                <a href="#kontak"><i class="fas fa-address-book"></i> Kontak</a>
                <a href="aboutsekolah.php"><i class="fas fa-school"></i> Tentang Sekolah</a>
                <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login Pendaftar</a>
                <a href="register.php" class="btn-register"><i class="fas fa-user-plus"></i> Daftar</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero Section -->
        <section id="home" class="hero">
            <div class="hero-content">
                <h1>Selamat Datang di <span>PPDB Online SD Negeri Pasir Panjang 02</span></h1>
                <p>Sistem pendaftaran peserta didik baru yang modern dan efisien. Daftarkan putra/putri Anda dengan
                    mudah melalui platform digital kami. Proses cepat, aman, dan transparan.</p>

                <?php if ($tahunAjaran): ?>
                    <div class="tahun-banner">
                        <h3><i class="fas fa-calendar-check"></i> Tahun Ajaran
                            <?php echo htmlspecialchars($tahunAjaran['tahun']); ?> Telah Dibuka!
                        </h3>
                        <p>Kuota tersedia: <strong><?php echo $tahunAjaran['kuota']; ?> siswa</strong></p>
                    </div>
                <?php endif; ?>

                <div class="hero-buttons">

    <?php if ($status === 'aktif'): ?>
        <a href="register.php"
           class="btn-primary animate__animated animate__pulse animate__infinite">
            <i class="fas fa-rocket"></i> Daftar Sekarang
        </a>
    <?php else: ?>
        <a href="javascript:void(0)"
           class="btn-primary btn-disabled">
            <i class="fas fa-lock"></i>
            <?= $status === 'belum'
                ? 'Pendaftaran Belum Dibuka'
                : 'Pendaftaran Ditutup'; ?>
        </a>
    <?php endif; ?>

    <a href="#informasi" class="btn-secondary">
        <i class="fas fa-book-open"></i> Pelajari Selengkapnya
    </a>

    <a href="aboutsekolah.php" class="btn-secondary">
        <i class="fas fa-school"></i> Tentang Sekolah
    </a>

</div>

            </div>
            <div class="hero-image">
    <div class="hero-slider">
        <img src="./assets/sd2pasirpanjang.png" alt="SD Negeri Pasirpanjang 02"  class="hero-slide active">
        <img src="./assets/psrpjg.jpeg" class="hero-slide">
        <!-- <img src="assets/hero/2.jpg" class="hero-slide">
        <img src="assets/hero/3.jpg" class="hero-slide"> -->
    </div>

    <button class="hero-nav prev" onclick="prevHero()">❮</button>
    <button class="hero-nav next" onclick="nextHero()">❯</button>
</div>

        </section>

       <?php if ($tahunAjaran && $tahunAjaran['tanggal_buka'] && $tahunAjaran['tanggal_tutup']): ?>

<section class="section">
    <h2 class="section-title">Jadwal Pendaftaran</h2>

    <div class="jadwal-wrapper">
        <div class="jadwal-card utama">
            <div class="jadwal-icon">
                <i class="fas fa-calendar-check"></i>
            </div>

            <h3>Pendaftaran Peserta Didik Baru</h3>

            <p class="jadwal-tanggal">
                <strong><?= date('d F Y', strtotime($tahunAjaran['tanggal_buka'])) ?></strong>
                <br>sampai<br>
                <strong><?= date('d F Y', strtotime($tahunAjaran['tanggal_tutup'])) ?></strong>
            </p>

            <span class="jadwal-badge <?= $statusClass ?>">
                <i class="fas fa-circle"></i> <?= $statusText ?>
            </span>
        </div>
    </div>
</section>

<?php endif; ?>


        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card animate__animated animate__fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_pendaftar'] ?? '0'; ?></div>
                        <div class="stat-label">Total Pendaftar</div>
                    </div>

                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-1s">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['diterima'] ?? '0'; ?></div>
                        <div class="stat-label">Siswa Diterima</div>
                    </div>

                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-3s">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-number">
                            <?php echo $tahunAjaran ? max(0, $tahunAjaran['kuota'] - ($stats['diterima'] ?? 0)) : '0'; ?>
                        </div>
                        <div class="stat-label">Kuota Tersisa</div>
                    </div>
                </div>
            </div>
        </section>

      <!-- Information Section -->
<section id="informasi" class="section">
    <h2 class="section-title">Informasi Penting</h2>

    <div class="info-grid">
        <?php
        $resultInfo->data_seek(0);
        $infoIndex = 0;

        while ($row = $resultInfo->fetch_assoc()):
            $infoIndex++;

            // ICON
            $icons = [
                'jadwal' => 'fa-calendar-alt',
                'persyaratan' => 'fa-file-contract',
                'lokasi' => 'fa-map-marked-alt',
                'alur' => 'fa-sitemap',
                'biaya' => 'fa-money-check-alt',
                'informasi' => 'fa-info-circle',
                'fasilitas' => 'fa-school'
            ];
            $icon = $icons[$row['jenis']] ?? 'fa-info-circle';
        ?>
            <div class="info-card animate__animated animate__fadeInUp"
                 data-animation-delay="<?= $infoIndex * 0.2 ?>">

                <div class="info-icon">
                    <i class="fas <?= $icon ?>"></i>
                </div>

                <h3><?= htmlspecialchars($row['judul']) ?></h3>

                <p>
                    <?= nl2br(htmlspecialchars(
                        substr($row['konten'], 0, 200) .
                        (strlen($row['konten']) > 200 ? '...' : '')
                    )) ?>
                </p>

                <?php if (!empty($row['file'])):
                    $fileUrl = "assets/uploads/informasi/" . htmlspecialchars($row['file']);
                    $fileExt = strtolower(pathinfo($row['file'], PATHINFO_EXTENSION));

                    $isImage = in_array($fileExt, ['jpg','jpeg','png','gif','bmp','webp']);
                    $isPdf   = ($fileExt === 'pdf');
                ?>

                    <?php if ($isImage): ?>
                        <!-- IMAGE PREVIEW -->
                        <div class="image-preview-container"
                             onclick="openModal('<?= $fileUrl ?>','<?= htmlspecialchars($row['judul']) ?>','image','<?= $fileExt ?>')">

                            <img src="<?= $fileUrl ?>"
                                 alt="<?= htmlspecialchars($row['judul']) ?>"
                                 class="image-preview">

                            <div class="preview-overlay">
                                <div class="preview-icon">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- FILE PREVIEW BUTTON -->
                        <button class="file-preview-btn"
                            onclick="openModal(
                                '<?= $fileUrl ?>',
                                '<?= htmlspecialchars($row['judul']) ?>',
                                '<?= $isPdf ? 'pdf' : 'document' ?>',
                                '<?= $fileExt ?>'
                            )">

                            <i class="fas fa-eye"></i>
                            <?= $isPdf ? 'Preview PDF' : 'Lihat File' ?>
                        </button>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</section>


        <!-- Pengumuman Section -->
        <section id="pengumuman" class="section">
            <h2 class="section-title">Pengumuman Terbaru</h2>
            <div class="pengumuman-list">
                <?php
                $resultPengumuman->data_seek(0);
                $pengumumanIndex = 0;
                while ($row = $resultPengumuman->fetch_assoc()):
                    $pengumumanIndex++;
                    ?>
                    <div class="pengumuman-card animate__animated animate__fadeInUp"
                        data-animation-delay="<?php echo $pengumumanIndex * 0.2; ?>">
                        <h3><i class="fas fa-bullhorn"></i> <?php echo htmlspecialchars($row['judul']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($row['isi'])); ?></p>

                        <?php if ($row['file']):
                            $fileUrl = "assets/uploads/pengumuman/" . htmlspecialchars($row['file']);
                            $fileExt = strtolower(pathinfo($row['file'], PATHINFO_EXTENSION));
                            $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                            $isPdf = ($fileExt === 'pdf');
                            ?>
                            <div>
                                <a href="<?php echo $fileUrl; ?>" class="btn-download" download>
                                    <i class="fas fa-download"></i> Download File
                                </a>

                                <button class="file-preview-btn"
                                    onclick="openModal('<?php echo $fileUrl; ?>', '<?php echo htmlspecialchars($row['judul']); ?>', '<?php echo $isImage ? 'image' : ($isPdf ? 'pdf' : 'document'); ?>', '<?php echo $fileExt; ?>')">
                                    <i class="fas fa-eye"></i> Preview File
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <?php if (!empty($kabarSekolah)): ?>
        <!-- Kabar Sekolah (berita & kegiatan yang ditandai tampil di beranda) -->
        <section id="kabar-sekolah" class="section">
            <h2 class="section-title">Kabar Sekolah</h2>
            <div class="ab-home-news">
                <?php foreach ($kabarSekolah as $kb):
                    $kbUrl = ks_img_url($kb['img']);
                    $kbIcon = $kb['jenis'] === 'berita' ? 'newspaper' : 'calendar-alt';
                ?>
                    <article class="ab-card">
                        <div class="ab-media">
                            <?php if ($kbUrl): ?>
                                <img src="<?= ks_h($kbUrl) ?>" alt="<?= ks_h($kb['judul']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="ab-ph" aria-hidden="true"><i class="fas fa-<?= $kbIcon ?>"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="ab-body-txt">
                            <div class="ab-meta">
                                <span class="ab-chip"><?= ks_h($kb['kategori']) ?></span>
                                <?php if (!empty($kb['tgl'])): ?><span><i class="far fa-calendar"></i> <?= ks_h(ks_tanggal($kb['tgl'])) ?></span><?php endif; ?>
                            </div>
                            <h3><?= ks_h($kb['judul']) ?></h3>
                            <?php if (!empty($kb['ringkas'])): ?><p><?= ks_h(ks_excerpt($kb['ringkas'], 120)) ?></p><?php endif; ?>
                            <a class="ab-more" href="aboutsekolah.php?<?= $kb['jenis'] ?>=<?= (int) $kb['id'] ?>#detail">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="ab-home-cta">
                <a href="aboutsekolah.php#berita" class="btn-secondary"><i class="fas fa-newspaper"></i> Lihat Semua Berita</a>
            </div>
        </section>
        <?php endif; ?>

        <!-- Contact & Map Section -->
        <section id="kontak" class="contact-section animate__animated animate__fadeIn">
            <div class="contact-grid">
                <div class="contact-info">
                    <h3><i class="fas fa-address-book"></i> Hubungi Kami</h3>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Alamat Sekolah</h4>
                            <p><?php echo $profil ? htmlspecialchars($profil['konten']) : 'Desa Pasirpanjang RT.02/RW.01, Kec. Salem, Kab. Brebes, Jawa Tengah'; ?>
                            </p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="contact-text">
                            <h4>WhatsApp</h4>
                            <a href="https://wa.me/6282221599435" target="_blank">+62 822-2159-9435</a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                           <h4>Email</h4>
<a href="https://mail.google.com/mail/?view=cm&fs=1&to=sdnegeripasirpanjang.02%40gmail.com"
   target="_blank"
   rel="noopener noreferrer">
    sdnegeripasirpanjang.02@gmail.com
</a>
                        </div>
                    </div>

                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

            <div class="map-container">
    <div class="map-placeholder" style="padding: 0;">
        <div style="text-align: center; padding: 20px;">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.82524450123!2d108.8105!3d-7.1465!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f790000000001%3A0x1234567890abcdef!2sSDN%20Pasirpanjang%2002!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid" 
              width="100%" 
        height="400" 
        style="border:0; border-radius: 15px;" 
        allowfullscreen="" 
        loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            
            <p>SD Negeri Pasirpanjang 02<br>Google Maps Location</p>
            <a href="https://maps.app.goo.gl/SPkAo8Kcod3eHNsX8" target="_blank"
                style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: white; color: #667eea; text-decoration: none; border-radius: 25px;">
                <i class="fas fa-external-link-alt"></i> Buka di Google Maps
            </a>
        </div>
    </div>
</div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-school"></i> SD Negeri Pasirpanjang 02</h3>
                    <p>NPSN: 20326230</p>
                    <p>Sekolah dasar negeri yang mencetak generasi berakhlak mulia, berprestasi, dan berwawasan tinggi.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Link Cepat</h3>
                    <ul class="footer-links">
                        <li><a href="#home"><i class="fas fa-chevron-right"></i> Beranda</a></li>
                        <li><a href="aboutsekolah.php"><i class="fas fa-chevron-right"></i> Tentang Sekolah</a></li>
                        <li><a href="#informasi"><i class="fas fa-chevron-right"></i> Informasi</a></li>
                        <li><a href="#pengumuman"><i class="fas fa-chevron-right"></i> Pengumuman</a></li>
                        <li><a href="#kontak"><i class="fas fa-chevron-right"></i> Kontak</a></li>
                        <li><a href="login.php"><i class="fas fa-chevron-right"></i> Login Pendaftar</a></li>
                        <li><a href="admin/area/login_admin.php"><i class="fas fa-chevron-right"></i> Login Admin/Panitia</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Jam Operasional</h3>
                    <p><strong>Senin - Jumat:</strong> 07:00 - 16:00</p>
                    <p><strong>Sabtu:</strong> 08:00 - 14:00</p>
                    <p><strong>Minggu & Hari Libur:</strong> Tutup</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal untuk Preview File -->
    <div id="fileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle"></div>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>

            <div id="modalBody">
                <!-- Konten akan diisi oleh JavaScript -->
            </div>

            <div class="modal-nav prev" onclick="navigateModal('prev')" style="display: none;">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="modal-nav next" onclick="navigateModal('next')" style="display: none;">
                <i class="fas fa-chevron-right"></i>
            </div>

            <div class="modal-actions" id="modalActions">
                <!-- Tombol aksi akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/6282221599435" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="assets/js/script.js"></script>
    <script>
        // Data untuk gallery modal
        let modalFiles = [];
        let currentModalIndex = 0;

        // Fungsi untuk membuka modal
        function openModal(fileUrl, title, fileType, fileExt) {
    const modal = document.getElementById('fileModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalActions = document.getElementById('modalActions');

    modalTitle.textContent = title;
    modalBody.innerHTML = '';

    /* ===== IMAGE ===== */
    if (fileType === 'image') {
        modalBody.innerHTML = `
            <img src="${fileUrl}" class="modal-image">
        `;
    }

    /* ===== PDF ===== */
    else if (fileExt === 'pdf') {
        modalBody.innerHTML = `
            <iframe src="${fileUrl}" class="modal-pdf"></iframe>
        `;
    }

    /* ===== DOCX / XLS via Google Viewer ===== */
    else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(fileExt)) {
        modalBody.innerHTML = `
            <iframe
                src="https://docs.google.com/gview?url=${encodeURIComponent(location.origin + '/' + fileUrl)}&embedded=true"
                class="modal-document">
            </iframe>
        `;
    }

    /* ===== OTHER FILE ===== */
    else {
        modalBody.innerHTML = `
            <div style="text-align:center;padding:50px;color:white">
                <i class="fas fa-file fa-5x"></i>
                <h3>Preview tidak tersedia</h3>
                <p>Silakan download file</p>
            </div>
        `;
    }

    modalActions.innerHTML = `
        <button class="modal-btn" onclick="downloadFile('${fileUrl}')">
            <i class="fas fa-download"></i> Download
        </button>
        <button class="modal-btn" onclick="closeModal()">
            <i class="fas fa-times"></i> Tutup
        </button>
    `;

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

            // Simpan data file saat ini untuk navigasi
            currentModalIndex = 0;
        }

        // Fungsi untuk membuka modal dengan gallery
        function openModalGallery(files, startIndex) {
            modalFiles = files;
            currentModalIndex = startIndex;

            if (modalFiles.length > 0) {
                const file = modalFiles[currentModalIndex];
                openModal(file.url, file.title, file.type, file.ext);

                // Tampilkan navigasi jika ada lebih dari 1 file
                const prevBtn = document.querySelector('.modal-nav.prev');
                const nextBtn = document.querySelector('.modal-nav.next');

                if (modalFiles.length > 1) {
                    prevBtn.style.display = 'flex';
                    nextBtn.style.display = 'flex';
                }
            }
        }

        // Fungsi untuk navigasi modal gallery
        function navigateModal(direction) {
            if (direction === 'prev') {
                currentModalIndex = (currentModalIndex - 1 + modalFiles.length) % modalFiles.length;
            } else {
                currentModalIndex = (currentModalIndex + 1) % modalFiles.length;
            }

            const file = modalFiles[currentModalIndex];
            openModal(file.url, file.title, file.type, file.ext);
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            const modal = document.getElementById('fileModal');
            const prevBtn = document.querySelector('.modal-nav.prev');
            const nextBtn = document.querySelector('.modal-nav.next');

            modal.style.display = 'none';
            document.body.style.overflow = 'auto';

            // Sembunyikan navigasi
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';

            // Reset data
            modalFiles = [];
            currentModalIndex = 0;
        }

        // Fungsi untuk mendownload file
        function downloadFile(fileUrl) {
            const link = document.createElement('a');
            link.href = fileUrl;
            link.download = fileUrl.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Fungsi untuk mendapatkan icon berdasarkan ekstensi file
        function getFileIcon(ext) {
            const icons = {
                'pdf': 'pdf',
                'doc': 'word',
                'docx': 'word',
                'xls': 'excel',
                'xlsx': 'excel',
                'ppt': 'powerpoint',
                'pptx': 'powerpoint',
                'txt': 'alt',
                'zip': 'archive',
                'rar': 'archive',
                'jpg': 'image',
                'jpeg': 'image',
                'png': 'image',
                'gif': 'image',
                'mp4': 'video',
                'avi': 'video',
                'mp3': 'audio'
            };
            return icons[ext] || 'file';
        }

        // Tutup modal ketika klik di luar konten
        window.onclick = function (event) {
            const modal = document.getElementById('fileModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }

            // Navigasi dengan arrow keys
            if (modalFiles.length > 0) {
                if (event.key === 'ArrowLeft') {
                    navigateModal('prev');
                } else if (event.key === 'ArrowRight') {
                    navigateModal('next');
                }
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = entry.target.getAttribute('data-animation-delay') || 0;
                    setTimeout(() => {
                        entry.target.classList.add('animate__fadeInUp');
                    }, delay * 1000);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.info-card, .pengumuman-card').forEach(card => {
            observer.observe(card);
        });

        // Tahun ajaran countdown
        <?php if ($tahunAjaran && isset($tahunAjaran['tgl_selesai'])): ?>
            function updateCountdown() {
                const endDate = new Date('<?php echo $tahunAjaran['tgl_selesai']; ?>').getTime();
                const now = new Date().getTime();
                const timeLeft = endDate - now;

                if (timeLeft > 0) {
                    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));

                    const countdownElement = document.getElementById('countdown');
                    if (countdownElement) {
                        countdownElement.innerHTML = `${days} hari ${hours} jam ${minutes} menit lagi`;
                    }
                }
            }

            // Update countdown every minute
            setInterval(updateCountdown, 60000);
            updateCountdown();
        <?php endif; ?>

        // Parallax effect for hero image
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const heroImage = document.querySelector('.hero-image img');
            if (heroImage) {
                heroImage.style.transform = `translateY(${scrolled * 0.05}px) scale(${1 + scrolled * 0.0002})`;
            }
        });
    </script>
    <script>
window.addEventListener('scroll',()=>{
    document.querySelector('.header')
        .classList.toggle('scrolled',window.scrollY>50)
});

// active link
document.querySelectorAll('.nav a').forEach(link=>{
    link.addEventListener('click',()=>{
        document.querySelectorAll('.nav a')
            .forEach(l=>l.classList.remove('active'));
        link.classList.add('active');
    });
});
</script>
<script>
let heroIndex = 0;
const slides = document.querySelectorAll('.hero-slide');

function showHero(n){
    slides.forEach(s=>s.classList.remove('active'));
    slides[n].classList.add('active');
}

function nextHero(){
    heroIndex = (heroIndex+1)%slides.length;
    showHero(heroIndex);
}

function prevHero(){
    heroIndex = (heroIndex-1+slides.length)%slides.length;
    showHero(heroIndex);
}

// auto slide
setInterval(nextHero,5000);
</script>

</body>

</html>