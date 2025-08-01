<?php
// Pindahkan session_start() ke bagian paling atas file
if (!isset($_SESSION)) {
    session_start();
}

ob_start();

if (!isset($base_url)) {
    include '../config/config.php';
}

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    ob_end_clean(); // Tambahkan ini sebelum header redirect
    header("Location: " . $base_url . "../index.php");
    exit(); // Tambahkan exit setelah header
}

// Tentukan path untuk sidebar-icon.png menggunakan base_url yang sudah didefinisikan
$sidebarIconPath = $base_url . 'assets/media/sidebar-icon.png';
$sidebarIconExists = true; // Asumsikan file ada karena sudah diverifikasi

?>

<div class="sidebar" id="sidebar">
    <div class="text-center mb-0 mt-4">
        <img src="<?php echo $base_url; ?>assets/logo.png" alt="Logo"
            style="width: 160px; -webkit-user-drag: none; -webkit-user-select: none; user-select: none;">
    </div>
    <div class="user-profile">
        <img src="<?php
                    if (!empty($_SESSION['foto'])) {
                        echo $base_url . 'assets/media/profile/' . $_SESSION['foto'];
                    } else {
                        $avatarNumber = isset($_SESSION['nik']) ? $_SESSION['nik'] : rand(1, 1000);
                        echo "https://api.dicebear.com/9.x/pixel-art/svg?seed=" . $avatarNumber;
                    }
                    ?>" alt="Profile" class="profile-img">
        <div>
            <h6 class="mb-0 mt-1 profile-name">
                <a href="<?php echo $base_url; ?>profile/index.php?nik=<?php echo $_SESSION['nik']; ?>"
                    style="color: inherit; text-decoration: none;">
                    <?php echo $_SESSION['nama']; ?>
                </a>
            </h6>
            <small class="text-light opacity-75 profile-time" id="waktuSekarang"> </small>
        </div>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>"
                href="<?php echo $base_url; ?>dashboard.php">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
        </li>
        <?php if ($_SESSION['role_id'] == 1): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'cpanel') !== false) ? 'active' : ''; ?>"
                    href="#" data-bs-toggle="collapse" data-bs-target="#cpanelSubmenu">
                    <i class="bi bi-gear-fill"></i> CPanel Admin
                </a>
                <div class="collapse <?php echo (strpos($_SERVER['PHP_SELF'], 'cpanel') !== false) ? 'show' : ''; ?>"
                    id="cpanelSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>ksu/data-karyawan.php">
                                <i class="bi bi-person-fill"></i> Data Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>cpanel/">
                                <i class="bi bi-database-fill-gear"></i> Panel Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users/index.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>cpanel/users/">
                                <i class="bi bi-people-fill"></i> Manajemen Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'kantor/index.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>cpanel/kantor/">
                                <i class="bi bi-building-fill"></i> Data Kantor
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>
        <?php if (strpos($_SESSION['key_app'], 'ADM') !== false || strpos($_SESSION['key_app'], 'CS') !== false || strpos($_SESSION['key_app'], 'KABID') !== false): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'customer-service') !== false) ? 'active' : ''; ?>"
                    href="#" data-bs-toggle="collapse" data-bs-target="#csSubmenu">
                    <i class="bi bi-headset"></i> Customer Service
                </a>
                <div class="collapse <?php echo (strpos($_SERVER['PHP_SELF'], 'customer-service') !== false) ? 'show' : ''; ?>"
                    id="csSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cs/input-nasabah.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>cs/input-nasabah.php">
                                <i class="bi bi-person-plus-fill"></i> Data Nasabah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cs/input-pendamping.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>cs/input-pendamping.php">
                                <i class="bi bi-person-plus-fill"></i> Data Pendamping
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cs/input-penjamin.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>cs/input-penjamin.php">
                                <i class="bi bi-person-plus-fill"></i> Data Penjamin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cs/input-jaminan.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>cs/input-jaminan.php">
                                <i class="bi bi-file-earmark-fill"></i> Data Jaminan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cs/input-pengajuan.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>cs/input-pengajuan.php">
                                <i class="bi bi-file-text-fill"></i> Data Pengajuan
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if (strpos($_SESSION['key_app'], 'ADM') !== false || strpos($_SESSION['key_app'], 'CS') !== false || strpos($_SESSION['key_app'], 'KABID') !== false): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'administrasi') !== false) ? 'active' : ''; ?>"
                    href="#" data-bs-toggle="collapse" data-bs-target="#admSubmenu">
                    <i class="bi bi-file-earmark-text-fill"></i> Adm Kredit
                </a>
                <div class="collapse <?php echo (strpos($_SERVER['PHP_SELF'], 'administrasi') !== false) ? 'show' : ''; ?>"
                    id="admSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'administrasi/input-pejabat-approvel.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>administrasi/input-pejabat-approvel.php">
                                <i class="bi bi-person-badge-fill"></i> Pejabat Approvel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'administrasi/input-droping.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>administrasi/input-droping.php">
                                <i class="bi bi-cash-stack"></i> Data Droping
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'administrasi/input-restruk.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>administrasi/input-restruk.php">
                                <i class="bi bi-arrow-repeat"></i> Data Restruk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'administrasi/pengambilan-jaminan.php') ? 'active' : ''; ?>"
                                href="<?php echo $base_url; ?>administrasi/pengambilan-jaminan.php">
                                <i class="bi bi-file-earmark-check"></i> Pengambilan Jaminan
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>tracking/tracking-berkas.php">
                <i class="bi bi-clock"></i> Tracking Berkas
            </a>
        </li>

        <hr>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>virtual-account/index.php">
                <i class="bi bi-credit-card-fill"></i> Virtual Account
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'monitoringnasabah') !== false) ? 'active' : ''; ?>"
                href="#" data-bs-toggle="collapse" data-bs-target="#monnasSubmenu">
                <i class="bi bi-graph-up"></i> Monitoring Nasabah
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['PHP_SELF'], 'monitoringnasabah') !== false) ? 'show' : ''; ?>"
                id="monnasSubmenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'monitoring-nasabah/kunjungan-nasabah.php') ? 'active' : ''; ?>"
                            href="<?php echo $base_url; ?>monitoring-nasabah/kunjungan-nasabah.php">
                            <i class="bi bi-people-fill"></i> Kunjungan Nasabah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'prospekting-nasabah/prospekting-nasabah.php') ? 'active' : ''; ?>"
                            href="<?php echo $base_url; ?>monitoring-nasabah/prospekting-nasabah.php">
                            <i class="bi bi-people-fill"></i> Prospekting Nasabah
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>ketentuanku/">
                <i class="bi bi-envelope-paper-fill"></i> Ketentuanku
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?php echo $base_url; ?>monitoring-teller/">
                <i class="bi bi-box-fill"></i> Monitoring Teller
            </a>
        </li>

        <li class="nav-item mt-3">
            <a class="nav-link" href="<?php echo $base_url; ?>../index.php">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </li>
    </ul>
</div>

<!-- Tombol untuk membuka sidebar di mobile -->
<button class="btn d-md-none mobile-menu-btn shadow" onclick="toggleSidebar()"
    style="position: fixed; bottom: 20px; right: 20px; z-index: 999; padding: 12px; border-radius: 50%; width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; background: linear-gradient(45deg, #28a745, #20c997); color: white;">
    <i class="bi bi-list"></i>
</button>



<!-- Ikon sidebar yang melayang di sebelah kiri bawah -->
<!-- <div class="floating-sidebar-icon" data-icon-exists="true" data-path="<?php echo htmlspecialchars($sidebarIconPath); ?>">
    <img src="<?php echo $sidebarIconPath; ?>" alt="Sidebar Icon" class="sidebar-floating-icon">
</div> -->

<!-- Overlay untuk menutup sidebar saat diklik -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Tambahkan SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tambahkan notifikasi di sini -->
<?php
if (isset($_SESSION['success'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '" . $_SESSION['message'] . "',
                confirmButtonColor: '#3085d6'
            });
        });
    </script>";
    unset($_SESSION['success']);
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '" . $_SESSION['message'] . "',
                confirmButtonColor: '#d33'
            });
        });
    </script>";
    unset($_SESSION['error']);
    unset($_SESSION['message']);
}
?>

<!-- Tambahkan modal di akhir file sebelum script -->
<div class="modal fade" id="updateFotoModal" tabindex="-1" aria-labelledby="updateFotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateFotoModalLabel">Ubah Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo $base_url; ?>cpanel/users/update-foto.php" method="POST"
                enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <h6>Foto Saat Ini</h6>
                        <img src="<?php echo $_SESSION['foto'] ? $base_url . 'assets/media/profile/' . $_SESSION['foto'] : $base_url . 'assets/media/profile/default.png'; ?>"
                            alt="Foto Profil Saat Ini" class="img-thumbnail"
                            style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="mb-3">
                        <label for="foto" class="form-label">Pilih Foto Baru</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                        <small class="text-muted">Format yang didukung: JPG, JPEG, PNG. Maksimal 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tambahkan cpanelMenu ke dalam variabel yang sudah ada
        const csMenu = document.querySelector('[data-bs-target="#csSubmenu"]');
        const monnasMenu = document.querySelector('[data-bs-target="#monnasSubmenu"]');
        const cpanelMenu = document.querySelector('[data-bs-target="#cpanelSubmenu"]');
        const subMenuItems = document.querySelectorAll(
            '#csSubmenu .nav-link, #monnasSubmenu .nav-link, #cpanelSubmenu .nav-link');

        // Update fungsi setActiveMenu untuk menangani cpanelSubmenu
        function setActiveMenu(element) {
            localStorage.setItem('activeMenu', element.getAttribute('href'));

            // Hapus semua class active yang ada
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Tambahkan class active ke menu yang diklik
            element.classList.add('active');

            // Jika yang diklik adalah submenu, buka parent menu-nya
            if (element.closest('#csSubmenu')) {
                csMenu.classList.add('active');
                document.getElementById('csSubmenu').classList.add('show');
            } else if (element.closest('#monnasSubmenu')) {
                monnasMenu.classList.add('active');
                document.getElementById('monnasSubmenu').classList.add('show');
            } else if (element.closest('#cpanelSubmenu')) {
                cpanelMenu.classList.add('active');
                document.getElementById('cpanelSubmenu').classList.add('show');
            }
        }

        // Menerapkan active state saat halaman dimuat
        const activeMenu = localStorage.getItem('activeMenu');
        if (activeMenu) {
            const activeLink = document.querySelector(`[href="${activeMenu}"]`);
            if (activeLink) {
                setActiveMenu(activeLink);
            }
        }

        // Event listener untuk setiap menu item
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.hasAttribute('data-bs-toggle')) {
                    setActiveMenu(this);
                }
            });
        });

        // Fungsi untuk menampilkan waktu
        function tampilkanWaktu() {
            const waktu = new Date();
            const jam = String(waktu.getHours()).padStart(2, '0');
            const menit = String(waktu.getMinutes()).padStart(2, '0');
            const detik = String(waktu.getSeconds()).padStart(2, '0');
            const jamPulang = (waktu.getHours() >= 17) ? ' - Jam Pulang!' : '';
            document.getElementById('waktuSekarang').innerHTML = `${jam}:${menit}:${detik}${jamPulang}`;
        }

        // Update waktu setiap detik
        setInterval(tampilkanWaktu, 1000);
        tampilkanWaktu(); // Tampilkan waktu pertama kali

        // Fungsi untuk toggle sidebar (jika belum ada)
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            } else {
                sidebar.classList.add('show');
                overlay.classList.add('show');
            }
        }

        // Tambahkan event listener untuk ikon melayang (opsional - untuk efek visual)
        const floatingIcon = document.querySelector('.sidebar-floating-icon img');
        const floatingContainer = document.querySelector('.floating-sidebar-icon');

        console.log('Debug: Checking floating icon...');
        console.log('Floating container found:', !!floatingContainer);
        console.log('Floating icon found:', !!floatingIcon);

        if (floatingContainer) {
            console.log('Floating icon path:', floatingContainer.getAttribute('data-path'));
            console.log('Floating icon display style:', window.getComputedStyle(floatingContainer).display);
        }

        if (floatingIcon) {
            // Cek apakah device adalah mobile/tablet
            const isMobile = window.innerWidth <= 991;

            if (isMobile) {
                // Sembunyikan ikon di mobile/tablet
                floatingContainer.style.display = 'none';
                floatingContainer.style.visibility = 'hidden';
                floatingContainer.style.opacity = '0';
                floatingContainer.style.pointerEvents = 'none';
                console.log('Floating icon hidden on mobile/tablet');
            } else {
                // Tambahkan efek subtle saat hover hanya di desktop
                floatingIcon.addEventListener('mouseenter', function() {
                    this.style.filter = 'brightness(1.1) drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3))';
                });

                floatingIcon.addEventListener('mouseleave', function() {
                    this.style.filter = 'drop-shadow(0 2px 8px rgba(0, 0, 0, 0.15))';
                });

                // Pastikan ikon ditampilkan di desktop
                floatingContainer.classList.add('show');
                floatingContainer.style.display = 'block';
                floatingContainer.style.visibility = 'visible';
                floatingContainer.style.opacity = '1';
                floatingContainer.style.pointerEvents = 'none';
                console.log('Floating icon should be visible now');
            }
        } else {
            console.log('Floating icon element not found');
        }

        // Tambahkan event listener untuk resize window
        window.addEventListener('resize', function() {
            const floatingContainer = document.querySelector('.floating-sidebar-icon');
            if (floatingContainer) {
                const isMobile = window.innerWidth <= 991;
                if (isMobile) {
                    floatingContainer.style.display = 'none';
                    floatingContainer.style.visibility = 'hidden';
                    floatingContainer.style.opacity = '0';
                    floatingContainer.style.pointerEvents = 'none';
                } else {
                    floatingContainer.style.display = 'block';
                    floatingContainer.style.visibility = 'visible';
                    floatingContainer.style.opacity = '1';
                    floatingContainer.style.pointerEvents = 'none';
                }
            }
        });
    });
</script>