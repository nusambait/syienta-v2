<div class="top-navbar d-flex justify-content-between align-items-center d-none d-md-flex">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-dark me-3 mobile-menu-btn d-none" onclick="toggleSidebar()">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div>
            <h4 class="mb-0"> <?php
                                date_default_timezone_set('Asia/Jakarta');
                                $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
                                $bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

                                $tanggal = $hari[date('w')] . ', ' . date('d') . ' ' . $bulan[date('n') - 1] . ' ' . date('Y');
                                echo $tanggal;
                                ?></h4>
            <small class="text-muted fs-6">Selamat datang kembali, <?php echo $_SESSION['nama']; ?>!</small>
            </small>
        </div>
    </div>
    <div class="d-flex align-items-center">
        <!-- <div class="position-relative me-3 d-none d-md-block">
            <i class="bi bi-bell fs-5"></i>
            <span class="notification-badge">3</span>
        </div> -->
        <div class="me-3">
            <input type="text" id="shortcutCode" class="form-control form-control-sm" placeholder="Kode Shortcut"
                style="width: 120px;">
        </div>
        <a href="<?php echo $base_url; ?>logout.php" class="btn btn-custom btn-outline-danger btn-sm">
            <i class="bi bi-power"></i>
            LOGOUT
        </a>
    </div>
</div>

<!-- Navbar Mobile -->
<div class="mobile-navbar d-flex d-md-none justify-content-between align-items-center p-2">
    <div class="d-flex align-items-center">
        <!-- <button class="btn btn-link text-dark me-2" onclick="toggleSidebar()">
            <i class="bi bi-list fs-4"></i>
        </button> -->
        <small class="text-muted">Hai, <?php echo $_SESSION['nama']; ?>!</small>
    </div>
    <div class="d-flex align-items-center">
        <div class="me-2">
            <input type="text" id="shortcutCodeMobile" class="form-control form-control-sm" placeholder="Shortcut"
                style="width: 80px;">
        </div>
        <a href="<?php echo $base_url; ?>logout.php" class="btn btn-custom btn-outline-danger btn-sm">
            <i class="bi bi-power"></i>
        </a>
    </div>
</div>

<!-- Slideshow Cover Image -->
<!-- <div class="slideshow-cover-container mb-3 d-none d-md-block"
    style="margin-top: -35px; position: relative; z-index: -1; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; overflow: hidden;">
    <img src="<?php echo $base_url; ?>assets/media/navbar-event.png" alt="Cover Image" class="img-fluid w-100">
</div> -->

<!-- <div class="mobile-menu fixed-bottom bg-white py-3 px-4 d-md-none">
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?php echo $base_url; ?>dashboard.php" class="text-decoration-none text-center">
            <i class="bi bi-house-door fs-5"></i>
            <div class="small">Home</div>
        </a>
        <a href="#" class="text-decoration-none text-center" onclick="document.getElementById('shortcutCodeMobile').focus()">
            <i class="bi bi-search fs-5"></i>
            <div class="small">Shortcut</div>
        </a>
        <a href="#" class="text-decoration-none text-center" onclick="toggleSidebar()">
            <i class="bi bi-clock-history fs-5"></i>
            <div class="small">History</div>
        </a>
        <a href="<?php echo $base_url; ?>logout.php" class="text-decoration-none text-center">
            <i class="bi bi-box-arrow-right fs-5"></i>
            <div class="small">Logout</div>
        </a>
    </div>
</div> -->

<!-- <style>
.mobile-menu {
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}
.mobile-menu a {
    color: #666;
}
.mobile-menu a:hover, .mobile-menu a.active {
    color: #6610f2;
}

/* Tambahkan style untuk konten */
body {
    padding-bottom: 80px; /* Sesuaikan dengan tinggi navbar mobile */
}
@media (min-width: 768px) {
    body {
        padding-bottom: 0; /* Hilangkan padding di desktop */
    }
}
</style> -->

<script>
    document.getElementById('shortcutCode').addEventListener('keyup', function(e) {
        if (e.target.value === '99') {
            window.location.href = '<?php echo $base_url; ?>cpanel/';
        }
    });

    document.getElementById('shortcutCode').addEventListener('keyup', function(e) {
        if (e.target.value === '98') {
            window.location.href = '<?php echo $base_url; ?>cpanel/ubah-noreg.php';
        }
    });

    document.getElementById('shortcutCodeMobile').addEventListener('keyup', function(e) {
        if (e.target.value === '99') {
            window.location.href = '<?php echo $base_url; ?>cpanel/';
        }
    });
</script>