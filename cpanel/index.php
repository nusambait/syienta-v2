<?php
session_start();
include '../../config.php';
include '../config/config.php';
include '../includes/check-admin.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($connect, "SELECT * FROM account WHERE username='$username' AND password='$password'");
    $data = mysqli_fetch_array($query);

    if (mysqli_num_rows($query) > 0) {
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $data['role_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['key_app'] = $data['key_app'];
        $_SESSION['foto'] = $data['foto'];
        $_SESSION['kantor'] = $data['kantor'];

        header("Location: ../dashboard.php");
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Panel Admin</h2>

            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Menu File Ketentuanku -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">File Ketentuanku</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text text-muted flex-grow-1">
                                        Menu ini menampilkan semua file yang telah diunggah di bagian ketentuanku.
                                        Anda dapat melihat dan mengelola file-file tersebut.
                                        <strong class="text-danger">Perhatian:</strong> Hapusan file bersifat permanen
                                        dan akan menghapus data dari server dan database.
                                    </p>
                                    <div class="mt-auto w-100">
                                        <a href="m-file/file-ketentuanku.php" class="btn btn-primary w-100">
                                            <i class="bi bi-folder2-open"></i> Lihat File
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu File Komite -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">File Komite</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text text-muted flex-grow-1">
                                        Akses dan kelola semua dokumen terkait komite.
                                        Anda dapat melihat, mengunduh, dan mengelola file-file komite di sini.
                                    </p>
                                    <div class="mt-auto w-100">
                                        <a href="m-file/file-komite.php" class="btn btn-primary w-100">
                                            <i class="bi bi-folder2-open"></i> Lihat File
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Ubah Noreg -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Ubah / Hapus Nomor Registrasi</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text text-muted flex-grow-1">
                                        <strong class="text-warning">Peringatan Penting:</strong>
                                        Perubahan nomor registrasi akan mengubah / hapus semua data terkait di database.
                                        Pastikan untuk melakukan backup data terlebih dahulu sebelum menggunakan fitur ini.
                                    </p>
                                    <div class="mt-auto w-100">
                                        <a href="ubah-noreg.php" class="btn btn-warning w-100">
                                            <i class="bi bi-pencil-square"></i> Ubah Noreg
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Kelola Data Pengajuan -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Data Pengajuan</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text text-muted flex-grow-1">
                                        Lihat dan unduh data pengajuan kredit berdasarkan tahun, bulan, dan status (CAIR, SIAP CAIR, TOLAK, BATAL).
                                        Data dapat diexport ke Excel untuk keperluan pelaporan dan analisis lebih lanjut.
                                    </p>
                                    <div class="mt-auto w-100">
                                        <a href="m-pengajuan/index.php" class="btn btn-primary w-100">
                                            <i class="bi bi-database-gear"></i> Lihat Data
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Data Dropping -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Data Dropping</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text text-muted flex-grow-1">
                                        Kelola data dropping kredit yang telah dicairkan.
                                        Lihat riwayat pencairan dan detail dropping berdasarkan periode dan status pencairan.
                                    </p>
                                    <div class="mt-auto w-100">
                                        <a href="m-droping/index.php" class="btn btn-primary w-100">
                                            <i class="bi bi-database-gear"></i> Lihat Data
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Backup -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Backup Database</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <p class="card-text text-muted flex-grow-1">
                                        Buat salinan cadangan database dalam format SQL.
                                        Sangat disarankan untuk melakukan backup secara berkala
                                        untuk menjaga keamanan data.
                                    </p>
                                    <div class="mt-auto w-100">
                                        <a href="backup-db.php" class="btn btn-danger w-100">
                                            <i class="bi bi-download"></i> Backup Database
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>

</html>