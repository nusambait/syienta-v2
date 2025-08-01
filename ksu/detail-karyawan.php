<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data karyawan berdasarkan ID
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $query = mysqli_query($connect, "SELECT * FROM ksu_karyawan WHERE id='$id'");
    $data = mysqli_fetch_array($query);
} else {
    header("Location: data-karyawan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Detail Karyawan</h2>
                <div class="d-flex gap-2">
                    <a href="data-karyawan.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row py-4">
                        <div class="col-md-3 text-center mb-4">
                            <div class="profile-image-container mb-3">
                                <?php
                                $foto = !empty($data['foto']) ? $data['foto'] : 'default.png';
                                $foto_path = "../assets/media/profile/" . $foto;
                                ?>
                                <img src="<?php echo $foto_path; ?>" alt="Foto Profil" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                            <h4 class="mb-1"><?php echo $data['nama']; ?></h4>
                            <p class="text-muted mb-0"><?php echo $data['nik']; ?></p>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">Informasi Pribadi</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tr>
                                                <td width="40%">Nama Lengkap</td>
                                                <td width="60%"><?php echo $data['nama']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>NIK</td>
                                                <td><?php echo $data['nik']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Jenis Kelamin</td>
                                                <td><?php echo $data['jk']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Tanggal Lahir</td>
                                                <td><?php echo date('d F Y', strtotime($data['tgl_lahir'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Pendidikan</td>
                                                <td><?php echo $data['pendidikan']; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">Informasi Pekerjaan</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tr>
                                                <td width="40%">Jabatan</td>
                                                <td width="60%"><?php echo $data['jabatan']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Kantor</td>
                                                <td><?php echo $data['kantor']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Tanggal Masuk</td>
                                                <td><?php echo date('d F Y', strtotime($data['tgl_masuk'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Masa Kerja</td>
                                                <td><?php echo $data['masa_kerja']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Status</td>
                                                <td>
                                                    <span class="badge <?php echo $data['status'] == 'TETAP' ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $data['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
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