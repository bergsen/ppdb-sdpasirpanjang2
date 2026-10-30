<?php
$role = $_SESSION['role'] ?? '';

$menuItems = [];

if ($role === 'pendaftar') {
    $menuItems = [
        'dashboard.php' => ['icon' => 'tachometer-alt', 'label' => 'Dashboard'],
        'formulir.php'  => ['icon' => 'file-alt', 'label' => 'Formulir Pendaftaran'],
        'profil.php'    => ['icon' => 'user', 'label' => 'Profil'],
        '../logout.php' => ['icon' => 'sign-out-alt', 'label' => 'Logout']
    ];
} elseif ($role === 'panitia') {
    $menuItems = [
        'dashboard.php' => ['icon' => 'tachometer-alt', 'label' => 'Dashboard'],
        'data_calon.php'=> ['icon' => 'users', 'label' => 'Data Calon'],
        'laporan.php'   => ['icon' => 'file-pdf', 'label' => 'Laporan'],
        'profil.php'    => ['icon' => 'user', 'label' => 'Profil'],
        '../logout.php' => ['icon' => 'sign-out-alt', 'label' => 'Logout']
    ];
} elseif ($role === 'admin') {
    $menuItems = [
        'dashboard.php'        => ['icon' => 'tachometer-alt', 'label' => 'Dashboard'],
        'kelola_akun.php'      => ['icon' => 'user-cog', 'label' => 'Kelola Akun'],
        'kelola_pendaftar.php' => ['icon' => 'user-friends', 'label' => 'Kelola Pendaftar'],
        'kelola_tahun_ajaran.php'=> ['icon' => 'calendar', 'label' => 'Tahun Ajaran'],
        'kelola_kuota.php'     => ['icon' => 'chart-bar', 'label' => 'Kelola Kuota'],
        'kelola_informasi.php' => ['icon' => 'info-circle', 'label' => 'Informasi'],
        'kelola_pengumuman.php'=> ['icon' => 'bullhorn', 'label' => 'Pengumuman'],
        'kelola_konten_sekolah.php' => ['icon' => 'school', 'label' => 'Konten Sekolah'],
        'profil.php'           => ['icon' => 'user', 'label' => 'Profil'],
        '../logout.php'        => ['icon' => 'sign-out-alt', 'label' => 'Logout']
    ];
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
/* ===== SIDEBAR BASE ===== */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    transition: width .3s ease;
    z-index: 1000;
}

/* ===== COLLAPSE MODE (ICON ONLY) ===== */
.sidebar.collapsed {
    width: 70px;
}

.sidebar.collapsed .menu-text,
.sidebar.collapsed .sidebar-header h3,
.sidebar.collapsed .sidebar-header p {
    display: none;
}

/* ===== TOGGLE BUTTON ===== */
.sidebar-toggle {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
    margin-right: .5rem;
}

/* ===== MAIN CONTENT SHIFT ===== */
.main-content {
    margin-left: 250px;
    transition: margin-left .3s ease;
}

.sidebar.collapsed ~ .main-content {
    margin-left: 70px;
}

/* Mobile: sidebar dapat dibuka/tutup, konten tidak terhimpit */
.sidebar-toggle { color: inherit; padding: .35rem .5rem; border-radius: 6px; }
.mobile-sidebar-toggle { display:none; }
@media (max-width: 768px) {
    .mobile-sidebar-toggle { display:inline-flex; align-items:center; justify-content:center; position:fixed; top:12px; left:12px; z-index:1201; width:42px; height:42px; border:0; border-radius:10px; background:#2c3e50; color:#fff; box-shadow:0 3px 12px rgba(0,0,0,.2); }
    .sidebar { width:270px; max-width:85vw; transform:translateX(-110%); overflow-y:auto; overflow-x:hidden; box-shadow:0 0 25px rgba(0,0,0,.2); transition:transform .25s ease; }
    .sidebar.mobile-open { transform:translateX(0); }
    .sidebar .menu-text, .sidebar .sidebar-header h3, .sidebar .sidebar-header p { display:initial; }
    .sidebar-header { padding:1rem; text-align:left; }
    .sidebar-menu li a { padding:.8rem 1rem; justify-content:flex-start; gap:.7rem; }
    .sidebar-menu li a i { width:24px; font-size:1rem; }
    .sidebar-toggle { display:inline-flex; }
    .sidebar-overlay { display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,.42); }
    .sidebar-overlay.active { display:block; }
    .main-content { margin-left:0 !important; width:100%; max-width:100%; padding:70px 12px 20px !important; }
}
</style>

<button type="button" class="mobile-sidebar-toggle" id="mobileSidebarToggle" aria-label="Buka navigasi"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h3><i class="fas fa-school"></i> PPDB SDN Pasirpanjang02</h3>
        <p><?= ucfirst($role); ?> Panel</p>
    </div>

    <ul class="sidebar-menu">
        <?php foreach ($menuItems as $page => $item): ?>
        <li class="<?= ($currentPage === $page) ? 'active' : ''; ?>">
            <a href="<?= $page; ?>">
                <i class="fas fa-<?= $item['icon']; ?>"></i>
                <span class="menu-text"><?= $item['label']; ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>

<script>
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('collapsed');
});
(() => {
    const sidebar = document.getElementById('sidebar');
    const opener = document.getElementById('mobileSidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');
    const close = () => { sidebar?.classList.remove('mobile-open'); overlay?.classList.remove('active'); };
    opener?.addEventListener('click', () => { sidebar?.classList.toggle('mobile-open'); overlay?.classList.toggle('active'); });
    overlay?.addEventListener('click', close);
    sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', close));
})();
</script>
<script src="../assets/js/mobile.js"></script>
