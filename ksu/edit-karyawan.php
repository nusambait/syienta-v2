<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah parameter id tersedia
if (!isset($_GET['id'])) {
    echo "<script>window.location.href='data-karyawan.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($connect, $_GET['id']);

// Ambil data karyawan berdasarkan id
$query = mysqli_query($connect, "SELECT * FROM ksu_karyawan WHERE id='$id'");
if (mysqli_num_rows($query) == 0) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Data karyawan tidak ditemukan!',
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                window.location.href='data-karyawan.php';
            });
        });
    </script>";
    exit;
}
$data = mysqli_fetch_assoc($query);

// Proses update data karyawan
if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $nik = mysqli_real_escape_string($connect, $_POST['nik']);
    $jk = mysqli_real_escape_string($connect, $_POST['jk']);
    $tgl_lahir = mysqli_real_escape_string($connect, $_POST['tgl_lahir']);
    $alamat = mysqli_real_escape_string($connect, $_POST['alamat']);
    $jabatan = mysqli_real_escape_string($connect, $_POST['jabatan']);
    $kantor = mysqli_real_escape_string($connect, $_POST['kantor']);
    $pendidikan = mysqli_real_escape_string($connect, $_POST['pendidikan']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    // Update data karyawan
    $update = mysqli_query($connect, "UPDATE ksu_karyawan SET 
        nama = '$nama',
        nik = '$nik',
        jk = '$jk',
        tgl_lahir = '$tgl_lahir',
        alamat = '$alamat',
        jabatan = '$jabatan',
        kantor = '$kantor',
        pendidikan = '$pendidikan',
        status = '$status'
        WHERE id = '$id'");

    if ($update) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data karyawan berhasil diupdate!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='data-karyawan.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengupdate data karyawan!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Edit Karyawan</h2>
                <a href="data-karyawan.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="row">
                            <!-- Kolom Kiri -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama"
                                        value="<?php echo $data['nama']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">NIK</label>
                                    <input type="text" class="form-control" name="nik"
                                        value="<?php echo $data['nik']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" name="jk" required>
                                        <option value="LAKI-LAKI"
                                            <?php echo ($data['jk'] == 'LAKI-LAKI') ? 'selected' : ''; ?>>LAKI-LAKI
                                        </option>
                                        <option value="PEREMPUAN"
                                            <?php echo ($data['jk'] == 'PEREMPUAN') ? 'selected' : ''; ?>>PEREMPUAN
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" name="tgl_lahir"
                                        value="<?php echo $data['tgl_lahir']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="alamat" rows="3"
                                        required><?php echo $data['alamat']; ?></textarea>
                                </div>
                            </div>
                            <!-- Kolom Kanan -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" name="jabatan"
                                        value="<?php echo $data['jabatan']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kantor</label>
                                    <select class="form-select" name="kantor" required>
                                        <option value="PUSAT"
                                            <?php echo ($data['kantor'] == 'PUSAT') ? 'selected' : ''; ?>>
                                            PUSAT</option>
                                        <option value="CIPARAY"
                                            <?php echo ($data['kantor'] == 'CIPARAY') ? 'selected' : ''; ?>>
                                            CIPARAY</option>
                                        <option value="GARUT"
                                            <?php echo ($data['kantor'] == 'GARUT') ? 'selected' : ''; ?>>
                                            GARUT</option>
                                        <option value="SITURAJA"
                                            <?php echo ($data['kantor'] == 'SITURAJA') ? 'selected' : ''; ?>>
                                            SITURAJA</option>
                                        <option value="SOREANG"
                                            <?php echo ($data['kantor'] == 'SOREANG') ? 'selected' : ''; ?>>
                                            SOREANG</option>
                                        <option value="BANDUNG"
                                            <?php echo ($data['kantor'] == 'BANDUNG') ? 'selected' : ''; ?>>
                                            BANDUNG</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pendidikan</label>
                                    <select class="form-select" name="pendidikan" required>
                                        <option value="SD"
                                            <?php echo ($data['pendidikan'] == 'SD') ? 'selected' : ''; ?>>SD</option>
                                        <option value="SMP"
                                            <?php echo ($data['pendidikan'] == 'SMP') ? 'selected' : ''; ?>>SMP</option>
                                        <option value="SMA"
                                            <?php echo ($data['pendidikan'] == 'SMA') ? 'selected' : ''; ?>>SMA</option>
                                        <option value="D3"
                                            <?php echo ($data['pendidikan'] == 'D3') ? 'selected' : ''; ?>>D3</option>
                                        <option value="S1"
                                            <?php echo ($data['pendidikan'] == 'S1') ? 'selected' : ''; ?>>S1</option>
                                        <option value="S2"
                                            <?php echo ($data['pendidikan'] == 'S2') ? 'selected' : ''; ?>>S2</option>
                                        <option value="S3"
                                            <?php echo ($data['pendidikan'] == 'S3') ? 'selected' : ''; ?>>S3</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="TETAP"
                                            <?php echo ($data['status'] == 'TETAP') ? 'selected' : ''; ?>>TETAP</option>
                                        <option value="KONTRAK"
                                            <?php echo ($data['status'] == 'KONTRAK') ? 'selected' : ''; ?>>KONTRAK
                                        </option>
                                        <option value="TRAINEE"
                                            <?php echo ($data['status'] == 'TRAINEE') ? 'selected' : ''; ?>>TRAINEE
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
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