<?php
// hapus semua file ini ganti dengan isi dari login.php jika ingin menggunakan login terpisah
session_start();
require_once __DIR__ . '/config/init.php';
include '../config.php';
include 'config/config.php';

// Tambahkan query untuk menghitung total nasabah
$query_total_nasabah = "SELECT COUNT(*) as total FROM nasabah WHERE nik IS NOT NULL AND nik != ''";
$result_total = mysqli_query($connect, $query_total_nasabah);
$row_total = mysqli_fetch_assoc($result_total);
$total_nasabah = $row_total['total'];

// Query untuk mencari user yang berulang tahun hari ini
$query_birthday = "SELECT GROUP_CONCAT(nama SEPARATOR ', ') as nama_list, COUNT(*) as total 
    FROM account 
    WHERE DATE_FORMAT(tgl_lahir, '%m-%d') = DATE_FORMAT(CURRENT_DATE, '%m-%d')";
$result_birthday = mysqli_query($connect, $query_birthday);
$birthday_data = mysqli_fetch_assoc($result_birthday);

// Query untuk menghitung total plafon cair bulan ini
$query_total_plafon = "SELECT SUM(CAST(REPLACE(plafond, ',', '') AS DECIMAL)) as total_plafon 
    FROM droping 
    WHERE (status = 'CAIR' OR status = 'SIAP CAIR' OR status LIKE 'ACC%')
    AND DATE_FORMAT(STR_TO_DATE(tgl_droping, '%d-%m-%Y'), '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')";
$result_plafon = mysqli_query($connect, $query_total_plafon);
$row_plafon = mysqli_fetch_assoc($result_plafon);
$total_plafon = $row_plafon['total_plafon'] ?: 0;

// Query untuk menghitung total plafon pengajuan yang TOLAK dan BATAL
$query_total_plafon_tolak_batal = "SELECT SUM(CAST(REPLACE(plafond, ',', '') AS DECIMAL)) as total_plafon 
    FROM droping 
    WHERE (status LIKE 'TOLAK%' OR status LIKE 'BATAL%')";
$result_plafon_tolak_batal = mysqli_query($connect, $query_total_plafon_tolak_batal);
$row_plafon_tolak_batal = mysqli_fetch_assoc($result_plafon_tolak_batal);
$total_plafon_tolak_batal = $row_plafon_tolak_batal['total_plafon'] ?: 0;

// Query untuk menghitung total pengajuan yang statusnya selain TOLAK% dan BATAL%
$query_total_pengajuan = "SELECT COUNT(*) as total FROM droping 
    WHERE status NOT LIKE 'TOLAK%' AND status NOT LIKE 'BATAL%'";
$result_pengajuan = mysqli_query($connect, $query_total_pengajuan);
$row_pengajuan = mysqli_fetch_assoc($result_pengajuan);
$total_pengajuan = $row_pengajuan['total'];

// Query untuk menghitung total pengajuan dengan status TOLAK% atau BATAL%
$query_total_tolak_batal = "SELECT COUNT(*) as total FROM droping WHERE status LIKE 'TOLAK%' OR status LIKE 'BATAL%'";
$result_tolak_batal = mysqli_query($connect, $query_total_tolak_batal);
$row_tolak_batal = mysqli_fetch_assoc($result_tolak_batal);
$total_tolak_batal = $row_tolak_batal['total'];

// Query untuk mengambil 5 aktivitas terakhir dari tabel tracking
$query_aktivitas = "SELECT * FROM tracking ORDER BY STR_TO_DATE(tgl, '%d-%m-%Y') DESC, STR_TO_DATE(jam, '%H:%i:%s') DESC LIMIT 5";
$result_aktivitas = mysqli_query($connect, $query_aktivitas);

// Query untuk mengambil data user terakhir login
$query_last_login = "SELECT username, nama, log, foto FROM account 
    WHERE log IS NOT NULL AND log != '' 
    ORDER BY STR_TO_DATE(log, '%Y-%m-%d %H:%i:%s') DESC 
    LIMIT 5";
$result_last_login = mysqli_query($connect, $query_last_login);

// Add this query after your existing queries at the top of the file
$current_month = date('m');
$current_year = date('Y');
$last_month = date('m', strtotime('-1 month'));
$last_month_year = date('Y', strtotime('-1 month'));

// Query for current month applications
$query_current = mysqli_query($connect, "
    SELECT COUNT(*) as total 
    FROM pengajuan 
    WHERE DATE_FORMAT(STR_TO_DATE(tglpeng, '%d-%m-%Y'), '%m-%Y') = '$current_month-$current_year'
    AND (status = 'CAIR' OR status LIKE 'ACC%' OR status = 'SIAP CAIR')
");
$current_month_data = mysqli_fetch_assoc($query_current);
$current_month_total = $current_month_data['total'];

// Query for last month applications
$query_last = mysqli_query($connect, "
    SELECT COUNT(*) as total 
    FROM pengajuan 
    WHERE DATE_FORMAT(STR_TO_DATE(tglpeng, '%d-%m-%Y'), '%m-%Y') = '$last_month-$last_month_year'
    AND (status = 'CAIR' OR status LIKE 'ACC%' OR status = 'SIAP CAIR')
");
$last_month_data = mysqli_fetch_assoc($query_last);
$last_month_total = $last_month_data['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        /* Animations */
        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes cardBlink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        hr {
            border: 0;
            height: 1px;
            background-image: linear-gradient(to right, rgba(70, 70, 70, 0), rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0));
            margin: 1rem 0;
        }

        /* Components */
        .blink-alert {
            animation: blink 2s infinite;
        }

        .blink-card {
            animation: cardBlink 2s infinite;
        }

        .carousel-image-container {
            width: 100%;
            height: 190px;
            overflow: hidden;
            position: relative;
            background-color: #f8f9fa;
        }

        .carousel-image-container img {
            width: 1980px !important;
            height: 190px !important;
            object-fit: fill;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin: 0;
            padding: 0;
        }

        /* Remove the responsive media query since we want fixed dimensions */
        @media (max-width: 1980px) {
            .carousel-image-container {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .carousel-image-container img {
                width: 1980px !important;
                min-width: unset;
                max-width: unset;
            }
        }

        .user-photo {
            position: relative;
            overflow: hidden;
        }

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 70%;
            height: 100%;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.66), rgba(161, 88, 88, 0));
            z-index: 1;
            pointer-events: none;
        }

        .user-photo {
            position: relative;
            overflow: hidden;
        }

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 70%;
            height: 100%;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.66), rgba(161, 88, 88, 0));
            z-index: 1;
            pointer-events: none;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .blink-alert {
            animation: blink 2s infinite;
        }

        @media (max-width: 768px) {
            .carousel {
                display: none;
            }
        }

        .custom-context-menu {
            background: white;
            border: 1px solid rgba(0, 0, 0, .125);
            border-radius: 4px;
            width: 200px;
        }

        .custom-context-menu .list-group-item {
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
        }

        .custom-context-menu .list-group-item:hover {
            background-color: #f8f9fa;
        }

        table td {
            cursor: context-menu;
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/navbar.php'; ?>

        <div class="row">
            <div class="col-md-12">
                <?php
                // Tampilkan ucapan selamat ulang tahun jika ada
                if ($birthday_data['total'] > 0) {
                    echo '<div class="alert alert-success" role="alert">
                            <i class="bi bi-gift me-2"></i>
                            Selamat Ulang Tahun <strong>' . htmlspecialchars($birthday_data['nama_list']) . '</strong> Semoga panjang umur sehat selalu!
                          </div>';
                }
                ?>

                <?php
                // Cek kelengkapan data profil
                $username = $_SESSION['username'];
                $query_profile = "SELECT bio, tgl_lahir, no_wa, alamat FROM account WHERE username = '$username'";
                $result_profile = mysqli_query($connect, $query_profile);
                $profile_data = mysqli_fetch_assoc($result_profile);

                if (
                    empty($profile_data['bio']) || empty($profile_data['tgl_lahir']) ||
                    empty($profile_data['no_wa']) || empty($profile_data['alamat'])
                ) {
                    echo '<div class="alert alert-danger blink-alert" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Profile anda belum diisi lengkap. Silakan klik nama profil anda di pojok kiri atas di bawah logo, atau 
                                <a href="' . $base_url . 'profile/index.php?nik=' . $_SESSION['nik'] . '" class="alert-link">di sini</a> untuk melengkapinya.
                             </div>';
                }
                ?>
            </div>
        </div>

        <div class="row mb-4 font-pogonia">

            <!-- Slideshow Informasi -->
            <div id="infoCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="0"
                        class="active"></button>
                    <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="2"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="carousel-image-container">
                            <img src="assets/media/1.jpg" alt="Slide1">
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-image-container">
                            <img src="assets/media/2.jpg" alt="Slide2">
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="carousel-image-container">
                            <img src="assets/media/3.jpg" alt="Slide3">
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#infoCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                    <span class="visually-hidden">Sebelumnya</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#infoCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                    <span class="visually-hidden">Selanjutnya</span>
                </button>
            </div>

            <div class="col-md-3">
                <div class="stats-card gradient-purple">
                    <i class="bi bi-person-check" style="color: white;"></i>
                    <h3 style="color: white;"><?php echo number_format($total_nasabah); ?></h3>
                    <p class="mb-0" style="color: white;">TOTAL NASABAH</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card gradient-green">
                    <i class="bi bi-currency-dollar" style="color: white;"></i>
                    <h3 style="color: white;">Rp <?php echo number_format($total_plafon, 0, ',', '.'); ?></h3>
                    <p class="mb-0" style="color: white;">TOTAL PLAFOND CAIR BULAN INI</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card gradient-orange">
                    <i class="bi bi-file-earmark-text" style="color: white;"></i>
                    <h3 style="color: white;"><?php echo number_format($total_pengajuan); ?></h3>
                    <p class="mb-0" style="color: white;">TOTAL PENGAJUAN AKTIF</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card gradient-red">
                    <i class="bi bi-x-circle" style="color: white;"></i>
                    <h3 style="color: white;"><?php echo number_format($total_tolak_batal); ?></h3>
                    <p class="mb-0" style="color: white;">TOTAL PENGAJUAN TOLAK & BATAL</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="mb-4">

                        <?php
                        $query_latest_tracking = mysqli_query($connect, "
                    SELECT t.*, p.niknas, n.nama 
                    FROM tracking t
                    JOIN pengajuan p ON t.noreg = p.noreg
                    JOIN nasabah n ON p.niknas = n.nik
                    ORDER BY t.id DESC
                    LIMIT 1
                ");

                        while ($track = mysqli_fetch_assoc($query_latest_tracking)) {
                            echo '<div class="alert alert-info py-0 mb-1 px-2">
                        <span style="font-size: 0.8rem;">
                            <i class="bi bi-info-circle-fill"></i> Pengajuan atas nama <strong>' . htmlspecialchars($track['nama']) . '</strong> 
                            dengan No. Reg <strong>' . $track['noreg'] . '</strong> 
                            telah berubah status menjadi <strong>' . $track['status'] . '</strong> 
                            oleh <strong>' . $track['op'] . '</strong> 
                            pada tanggal ' . $track['tgl'] . ' 
                            dan jam ' . $track['jam'] . '
                        </span>
                    </div>';
                        }
                        ?>
                    </div>
                    <h5 class="card-title">
                        <i class="bi bi-list-check me-2"></i>
                        20 Pengajuan Terbaru Sudah Cair
                    </h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Reg</th>
                                    <th>Nama</th>
                                    <th>Plafond</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query untuk 10 pengajuan terbaru
                                $query_recent = mysqli_query($connect, "
                                    SELECT p.*, n.nama 
                                    FROM pengajuan p
                                    LEFT JOIN nasabah n ON p.niknas = n.nik
                                    WHERE (p.status = 'CAIR' OR p.status LIKE 'ACC%' OR p.status = 'SIAP CAIR')
                                    ORDER BY STR_TO_DATE(p.tglpeng, '%d-%m-%Y') DESC, p.noreg DESC
                                    LIMIT 20
                                ");

                                while ($row = mysqli_fetch_assoc($query_recent)) {
                                    // Set badge color based on status
                                    $badge_class = 'bg-success';
                                    if ($row['status'] === 'SIAP CAIR') {
                                        $badge_class = 'bg-warning text-dark';
                                    } elseif (strpos($row['status'], 'ACC') === 0) {
                                        $badge_class = 'bg-info';
                                    }

                                    echo "<tr>
                                        <td>{$row['tglpeng']}</td>
                                        <td>{$row['noreg']}</td>
                                        <td>" . htmlspecialchars($row['nama']) . "</td>
                                        <td>Rp " . number_format($row['plaf'], 0, ',', '.') . "</td>
                                        <td><span class='badge {$badge_class}'>{$row['status']}</span></td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5 class="card-title"><i class="bi bi-person-circle me-2"></i>Informasi Akun</h5>
                    <hr>
                    <div class="user-info">
                        <div class="d-flex align-items-center mb-0">
                            <div class="user-avatar me-3">
                                <?php if (!empty($_SESSION['foto']) && file_exists('assets/media/profile/' . $_SESSION['foto'])): ?>
                                    <img src="<?php echo $base_url; ?>assets/media/profile/<?php echo $_SESSION['foto']; ?>"
                                        alt="Foto Profil" class="rounded-circle"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?php echo $base_url; ?>assets/media/profile/default.png"
                                        alt="Foto Profil Default" class="rounded-circle"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo $_SESSION['nama']; ?></h6>
                                <span class="badge bg-secondary"><?php echo $_SESSION['key_app']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h5 class="card-title"><i class="bi bi-building me-2"></i>Informasi Kantor Anda</h5>
                    <hr>
                    <div class="office-info font-pogonia">
                        <?php
                        // Get office information based on user's username
                        $username = $_SESSION['username'];
                        $office_query = mysqli_query($connect, "
                            SELECT a.*, k.nm_kantor, k.almt as alamat_kantor, k.kec, k.kab, k.nm_perusahaan 
                            FROM account a 
                            LEFT JOIN kantor k ON a.kantor = k.kd_kantor 
                            WHERE a.username = '$username'
                        ");
                        $office_data = mysqli_fetch_assoc($office_query);
                        ?>
                        <div class="mb-2">
                            <div class="text-muted small">Nama Kantor</div>
                            <div class="fw-medium"><?php echo htmlspecialchars($office_data['nm_kantor']); ?></div>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted small">Alamat</div>
                            <div class="fw-medium"><?php echo htmlspecialchars($office_data['alamat_kantor']); ?> - <?php echo htmlspecialchars($office_data['kab']); ?></div>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted small">Perusahaan</div>
                            <div class="fw-medium"><?php echo htmlspecialchars($office_data['nm_perusahaan']); ?></div>
                        </div>
                        <div class="mb-2 border-bottom pb-2">
                            <div class="text-muted small"></div>
                            <?php
                            $maps_url = '';
                            switch ($office_data['kantor']) {
                                case '100':
                                    $maps_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13322.657249057509!2d107.78958429669592!3d-6.908569232513158!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68db3a40ea9f2d%3A0xfe773bcb9af6a47c!2sBPR%20Nusamba%20Tanjungsari%20KPO!5e0!3m2!1sen!2sid!4v1748421873643!5m2!1sen!2sid';
                                    break;
                                case '200':
                                    $maps_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.791598483242!2d107.7046584979699!3d-7.033763737743374!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68c1a493587729%3A0x595f6e459a15dc3d!2sBPR%20Nusamba%20Tanjungsari%20KC%20Ciparay!5e0!3m2!1sen!2sid!4v1748421977707!5m2!1sen!2sid';
                                    break;
                                case '300':
                                    $maps_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1399.4543013078758!2d107.90078033727396!3d-7.211781176548612!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68b05394c817a5%3A0xae5ec46fa1c8aeb4!2sbank%20nusamba!5e0!3m2!1sen!2sid!4v1748422210022!5m2!1sen!2sid';
                                    break;
                                case '400':
                                    $maps_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.418315618655!2d108.01726377573837!3d-6.840348466916872!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68d3644860fa29%3A0x2e4df273add04e77!2sBPR%20Nusamba%20Tanjungsari%20KK%20Situraja!5e0!3m2!1sen!2sid!4v1748422248700!5m2!1sen!2sid';
                                    break;
                                case '500':
                                    $maps_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.8255437922776!2d107.52675577574077!3d-7.029781768872648!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68ec3485943f93%3A0xe7ae44c70da6ac72!2sBPR%20Nusamba%20Tanjungsari%20KK%20Soreang!5e0!3m2!1sen!2sid!4v1748422283342!5m2!1sen!2sid';
                                    break;
                                case '600':
                                    $maps_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7922.7117344856!2d107.92497821642642!3d-6.847873866117169!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68d14759a0521f%3A0x466cff02a84e322e!2sBPR%20Nusamba%20Tanjungsari%20KK%20Sumedang%20Utara!5e0!3m2!1sen!2sid!4v1748422323524!5m2!1sen!2sid';
                                    break;
                                default:
                                    $maps_url = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13322.657249057509!2d107.78958429669592!3d-6.908569232513158!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68db3a40ea9f2d%3A0xfe773bcb9af6a47c!2sBPR%20Nusamba%20Tanjungsari%20KPO!5e0!3m2!1sen!2sid!4v1748421873643!5m2!1sen!2sid';
                            }
                            ?>
                            <div class="maps-container" style="height: 200px;">
                                <iframe
                                    src="<?php echo $maps_url; ?>"
                                    width="100%"
                                    height="100%"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mt-3">
                    <h5 class="card-title"><i class="bi bi-person-check me-2"></i>Login Terakhir</h5>
                    <hr>
                    <div class="activity-list font-pogonia">
                        <?php while ($user = mysqli_fetch_assoc($result_last_login)): ?>
                            <div class="activity-item py-1 border-bottom d-flex justify-content-between align-items-stretch"
                                style="border-left: 3px solid #6c757d; padding-left: 8px;">
                                <div class="small">
                                    <div class="fw-medium">
                                        <?php echo htmlspecialchars($user['nama']); ?>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($user['log']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="user-photo position-relative"
                                    style="width: 60px; margin: -4px -12px -4px 8px; border-radius: 0 8px 8px 0;">
                                    <div class="gradient-overlay"></div>
                                    <?php if (!empty($user['foto']) && file_exists('assets/media/profile/' . $user['foto'])): ?>
                                        <img src="<?php echo $base_url; ?>assets/media/profile/<?php echo $user['foto']; ?>"
                                            alt="Foto Profil" class="img-fluid h-100 w-100"
                                            style="object-fit: cover; border-radius: 0 8px 8px 0;">
                                    <?php else: ?>
                                        <?php
                                        $avatarNumber = rand(1, 15); // Random number between 1-8
                                        ?>
                                        <img src="https://api.dicebear.com/9.x/pixel-art/svg?seed=<?php echo $avatarNumber; ?>"
                                            alt="Random Avatar" class="img-fluid h-100 w-100"
                                            style="object-fit: cover; border-radius: 0 8px 8px 0;">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="custom-context-menu" id="contextMenu" style="display: none; position: fixed; z-index: 1000;">
            <div class="list-group shadow-sm">
                <a href="#" class="list-group-item list-group-item-action" id="viewKomite">
                    <i class="bi bi-file-text me-2"></i>Buka Data Komite
                </a>
                <a href="#" class="list-group-item list-group-item-action" id="copyNoreg">
                    <i class="bi bi-clipboard me-2"></i>Copy Noreg
                </a>
            </div>
        </div>

        <style>
            .custom-context-menu {
                background: white;
                border: 1px solid rgba(0, 0, 0, .125);
                border-radius: 4px;
                width: 200px;
            }

            .custom-context-menu .list-group-item {
                padding: 8px 16px;
                font-size: 14px;
                cursor: pointer;
            }

            .custom-context-menu .list-group-item:hover {
                background-color: #f8f9fa;
            }

            table td {
                cursor: context-menu;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const contextMenu = document.getElementById('contextMenu');
                let selectedNoreg = '';

                // Handle right click on table cells
                document.querySelector('table').addEventListener('contextmenu', function(e) {
                    if (e.target.tagName === 'TD') {
                        e.preventDefault();
                        const row = e.target.closest('tr');
                        selectedNoreg = row.querySelector('td:nth-child(2)').textContent; // Get Noreg from second column

                        // Position menu at cursor
                        contextMenu.style.display = 'block';

                        // Ukuran menu
                        const menuWidth = contextMenu.offsetWidth;
                        const menuHeight = contextMenu.offsetHeight;

                        // Ukuran viewport
                        const winWidth = window.innerWidth;
                        const winHeight = window.innerHeight;

                        // Posisi awal
                        let posX = e.clientX;
                        let posY = e.clientY;

                        // Jika menu keluar layar kanan
                        if (posX + menuWidth > winWidth) {
                            posX = winWidth - menuWidth - 10;
                        }
                        // Jika menu keluar layar bawah
                        if (posY + menuHeight > winHeight) {
                            posY = winHeight - menuHeight - 10;
                        }

                        contextMenu.style.left = posX + 'px';
                        contextMenu.style.top = posY + 'px';
                    }
                });

                // Hide menu when clicking elsewhere
                document.addEventListener('click', function() {
                    contextMenu.style.display = 'none';
                });

                // Menu item handlers
                document.getElementById('viewKomite').addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = `administrasi/data-komite.php?noreg=${selectedNoreg}`;
                });

                document.getElementById('copyNoreg').addEventListener('click', function(e) {
                    e.preventDefault();
                    navigator.clipboard.writeText(selectedNoreg).then(() => {
                        // Show copy success message
                        const toast = document.createElement('div');
                        toast.className = 'alert alert-success position-fixed top-0 end-0 m-3';
                        toast.style.zIndex = '1050';
                        toast.innerHTML = `
                        <i class="bi bi-check-circle me-2"></i>
                        No. Reg ${selectedNoreg} berhasil disalin!
                    `;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 2000);
                    });
                    contextMenu.style.display = 'none';
                });
            });
        </script>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

            if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 767) {
                document.getElementById('sidebar').classList.remove('show');
            }
        });

        // Chart Pengajuan
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('pengajuanChart').getContext('2d');
            const pengajuanChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Perbandingan Plafon Bulan Ini'],
                    datasets: [{
                            label: 'Total Plafon Cair (Rp)',
                            data: [<?php echo $total_plafon; ?>],
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Total Plafon Ditolak & Dibatalkan (Rp)',
                            data: [<?php echo $total_plafon_tolak_batal; ?>],
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + ' Juta';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(1) + ' Ribu';
                                    }
                                    return 'Rp ' + value;
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('pengajuanBarChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Bulan Lalu (<?php echo date("F Y", strtotime("-1 month")); ?>)',
                            'Bulan Ini (<?php echo date("F Y"); ?>)'
                        ],
                        datasets: [{
                            label: 'Jumlah Pengajuan',
                            data: [
                                <?php echo $last_month_total; ?>,
                                <?php echo $current_month_total; ?>
                            ],
                            backgroundColor: [
                                'rgba(255, 159, 64, 0.7)',
                                'rgba(75, 192, 192, 0.7)'
                            ],
                            borderColor: [
                                'rgba(255, 159, 64, 1)',
                                'rgba(75, 192, 192, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        if (value % 1 === 0) {
                                            return value;
                                        }
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.parsed.y} Pengajuan`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>