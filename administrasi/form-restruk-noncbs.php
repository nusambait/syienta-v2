<?php
session_start();
include '../../config.php';
include '../config/config.php';

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

        header("Location: ../dashboard.php");
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}

// Ambil data nasabah berdasarkan NIK
$nik = $_GET['nik'];
$query_nasabah = mysqli_query($connect, "SELECT * FROM nasabah WHERE nik='$nik'");
$nasabah = mysqli_fetch_array($query_nasabah);

// Ubah logika hapus untuk data restrukturisasi
if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    if (isset($_GET['id']) && isset($_GET['nik'])) {
        $id = $_GET['id'];
        $nik = $_GET['nik'];

        $query = mysqli_query($connect, "DELETE FROM restruk_2 WHERE id='$id' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data restrukturisasi berhasil dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-restruk-noncbs.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Data restrukturisasi gagal dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-restruk-noncbs.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

if (isset($_POST['submit'])) {
    $nik = $_POST['nik'];
    $nama = $_POST['nama'];
    $untuk = $_POST['untuk'];
    $noreg_awal = $_POST['noreg_awal'];
    $noreg_restruk_1 = $_POST['noreg_restruk_1'];
    $noreg_restruk_2 = $_POST['noreg_restruk_2'];
    $noreg_jatuh_tempo = $_POST['noreg_jatuh_tempo'];
    $ket = $_POST['ket'];

    $query = mysqli_query($connect, "INSERT INTO restruk_2 (nik, nama, untuk, noreg_awal, noreg_restruk_1, noreg_restruk_2, noreg_jatuh_tempo, ket) 
    VALUES ('$nik', '$nama', '$untuk', '$noreg_awal', '$noreg_restruk_1', '$noreg_restruk_2', '$noreg_jatuh_tempo', '$ket')");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data restrukturisasi berhasil disimpan',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-restruk-noncbs.php?nik=" . $nik . "';
                }
            });
        });
        </script>";
    } else {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Data restrukturisasi gagal disimpan',
                confirmButtonText: 'OK'
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
    <title>Form Restrukturisasi Droping Ulang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">

            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0 text-gray-800">Form Restrukturisasi Droping Ulang</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-restruk.php">Data Restrukturisasi</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form Restrukturisasi Droping Ulang</li>
                    </ol>
                </div>

                <!-- Tabel Data Restrukturisasi -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Data Restrukturisasi Nasabah</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Untuk</th>
                                        <th>No. Reg Awal</th>
                                        <th>No. Reg Restruk 1</th>
                                        <th>No. Reg Restruk 2</th>
                                        <th>No. Reg Jatuh Tempo</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_restruk = mysqli_query($connect, "SELECT * FROM restruk_2 WHERE nik='$nik'");
                                    $no = 1;
                                    if (mysqli_num_rows($query_restruk) > 0) {
                                        while ($data_restruk = mysqli_fetch_array($query_restruk)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $data_restruk['nama']; ?></td>
                                                <td><?php echo $data_restruk['untuk']; ?></td>
                                                <td><?php echo $data_restruk['noreg_awal']; ?></td>
                                                <td><?php echo $data_restruk['noreg_restruk_1']; ?></td>
                                                <td><?php echo $data_restruk['noreg_restruk_2']; ?></td>
                                                <td><?php echo $data_restruk['noreg_jatuh_tempo']; ?></td>
                                                <td><?php echo $data_restruk['ket']; ?></td>
                                                <td class="d-flex justify-content-center">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="hapusRestruk('<?php echo $data_restruk['id']; ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="9" class="text-center">Belum ada data restrukturisasi</td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" name="nik" value="<?php echo $nik; ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nama</label>
                                    <input type="text" class="form-control" name="nama" value="<?php echo $nasabah['nama']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Untuk</label>
                                    <input type="text" class="form-control" name="untuk" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Registrasi Awal</label>
                                    <select class="form-select" name="noreg_awal" id="noreg_awal" required>
                                        <option value="">-- Pilih No. Registrasi Awal --</option>
                                        <?php
                                        $query_pengajuan = mysqli_query($connect, "SELECT noreg, pengajuan, plaf, jw FROM pengajuan WHERE niknas='$nik'");
                                        while ($data_pengajuan = mysqli_fetch_array($query_pengajuan)) {
                                            echo "<option value='" . $data_pengajuan['noreg'] . "'>" . $data_pengajuan['noreg'] . " - " . $data_pengajuan['pengajuan'] . " (Rp " . number_format($data_pengajuan['plaf'], 0, ',', '.') . " / " . $data_pengajuan['jw'] . " bulan)</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Registrasi Restruk 1</label>
                                    <select class="form-select" name="noreg_restruk_1" id="noreg_restruk_1" required>
                                        <option value="">-- Pilih No. Registrasi Restruk 1 --</option>
                                        <?php
                                        $query_pengajuan = mysqli_query($connect, "SELECT noreg, pengajuan, plaf, jw FROM pengajuan WHERE niknas='$nik'");
                                        while ($data_pengajuan = mysqli_fetch_array($query_pengajuan)) {
                                            echo "<option value='" . $data_pengajuan['noreg'] . "'>" . $data_pengajuan['noreg'] . " - " . $data_pengajuan['pengajuan'] . " (Rp " . number_format($data_pengajuan['plaf'], 0, ',', '.') . " / " . $data_pengajuan['jw'] . " bulan)</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Registrasi Restruk 2</label>
                                    <select class="form-select" name="noreg_restruk_2" id="noreg_restruk_2" required>
                                        <option value="">-- Pilih No. Registrasi Restruk 2 --</option>
                                        <?php
                                        $query_pengajuan = mysqli_query($connect, "SELECT noreg, pengajuan, plaf, jw FROM pengajuan WHERE niknas='$nik'");
                                        while ($data_pengajuan = mysqli_fetch_array($query_pengajuan)) {
                                            echo "<option value='" . $data_pengajuan['noreg'] . "'>" . $data_pengajuan['noreg'] . " - " . $data_pengajuan['pengajuan'] . " (Rp " . number_format($data_pengajuan['plaf'], 0, ',', '.') . " / " . $data_pengajuan['jw'] . " bulan)</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Registrasi Jatuh Tempo</label>
                                    <select class="form-select" name="noreg_jatuh_tempo" id="noreg_jatuh_tempo" required>
                                        <option value="">-- Pilih No. Registrasi Jatuh Tempo --</option>
                                        <?php
                                        $query_pengajuan = mysqli_query($connect, "SELECT noreg, pengajuan, plaf, jw FROM pengajuan WHERE niknas='$nik'");
                                        while ($data_pengajuan = mysqli_fetch_array($query_pengajuan)) {
                                            echo "<option value='" . $data_pengajuan['noreg'] . "'>" . $data_pengajuan['noreg'] . " - " . $data_pengajuan['pengajuan'] . " (Rp " . number_format($data_pengajuan['plaf'], 0, ',', '.') . " / " . $data_pengajuan['jw'] . " bulan)</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Keterangan</label>
                                    <textarea class="form-control" name="ket" rows="3" required></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="input-jaminan.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function hapusRestruk(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data restrukturisasi akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-restruk-noncbs.php?action=hapus&id=' + id + '&nik=<?php echo $nik; ?>';
                }
            })
        }
    </script>
</body>

</html>