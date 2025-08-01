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
if(isset($_GET['action']) && $_GET['action'] == 'hapus') {
    if(isset($_GET['id']) && isset($_GET['nik'])) {
        $id = $_GET['id'];
        $nik = $_GET['nik'];
        
        $query = mysqli_query($connect, "DELETE FROM restruk WHERE id='$id' AND nik='$nik'");
        
        if($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data restrukturisasi berhasil dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-restruk-cbs.php?nik=" . $nik . "';
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
                            window.location.href = 'form-restruk-cbs.php?nik=" . $nik . "';
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
    $noreg_lama = $_POST['noreg_lama'];
    $noreg_baru = $_POST['noreg_baru'];
    $ket = $_POST['ket'];

    $query = mysqli_query($connect, "INSERT INTO restruk (nik, nama, untuk, noreg_lama, noreg_baru, ket) 
    VALUES ('$nik', '$nama', '$untuk', '$noreg_lama', '$noreg_baru', '$ket')");

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
                    window.location.href = 'form-restruk-cbs.php?nik=" . $nik . "';
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
    <title>Form Restrukturisasi CBS</title>
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
                    <h4 class="mb-0 text-gray-800">Form Restrukturisasi CBS</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-restruk.php">Data Restrukturisasi</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form Restrukturisasi CBS</li>
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
                                        <th>No. Registrasi Lama</th>
                                        <th>No. Registrasi Baru</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_restruk = mysqli_query($connect, "SELECT * FROM restruk WHERE nik='$nik'");
                                    $no = 1;
                                    if(mysqli_num_rows($query_restruk) > 0) {
                                        while($data_restruk = mysqli_fetch_array($query_restruk)) {
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $data_restruk['nama']; ?></td>
                                            <td><?php echo $data_restruk['untuk']; ?></td>
                                            <td><?php echo $data_restruk['noreg_lama']; ?></td>
                                            <td><?php echo $data_restruk['noreg_baru']; ?></td>
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
                                            <td colspan="7" class="text-center">Belum ada data restrukturisasi</td>
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
                                    <label class="form-label required">No. Registrasi Lama</label>
                                    <select class="form-select" name="noreg_lama" id="noreg_lama" required onchange="updateNoregBaru()">
                                        <option value="">-- Pilih No. Registrasi Lama --</option>
                                        <?php
                                        $query_pengajuan = mysqli_query($connect, "SELECT noreg, pengajuan, plaf, jw FROM pengajuan WHERE niknas='$nik'");
                                        while($data_pengajuan = mysqli_fetch_array($query_pengajuan)) {
                                            echo "<option value='".$data_pengajuan['noreg']."'>".$data_pengajuan['noreg']." - ".$data_pengajuan['pengajuan']." (Rp ".number_format($data_pengajuan['plaf'],0,',','.')." / ".$data_pengajuan['jw']." bulan)</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Registrasi Baru</label>
                                    <select class="form-select" name="noreg_baru" id="noreg_baru" required>
                                        <option value="">-- Pilih No. Registrasi Baru --</option>
                                        <?php
                                        $query_pengajuan = mysqli_query($connect, "SELECT noreg, pengajuan, plaf, jw FROM pengajuan WHERE niknas='$nik'");
                                        while($data_pengajuan = mysqli_fetch_array($query_pengajuan)) {
                                            echo "<option value='".$data_pengajuan['noreg']."' class='noreg-option'>".$data_pengajuan['noreg']." - ".$data_pengajuan['pengajuan']." (Rp ".number_format($data_pengajuan['plaf'],0,',','.')." / ".$data_pengajuan['jw']." bulan)</option>";
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
                window.location.href = 'form-restruk-cbs.php?action=hapus&id=' + id + '&nik=<?php echo $nik; ?>';
            }
        })
    }

    function updateNoregBaru() {
        var noregLama = document.getElementById('noreg_lama').value;
        var noregBaruSelect = document.getElementById('noreg_baru');
        var options = noregBaruSelect.getElementsByTagName('option');
        
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === noregLama && noregLama !== '') {
                options[i].disabled = true;
                options[i].style.display = 'none';
            } else {
                options[i].disabled = false;
                options[i].style.display = '';
            }
        }
        
        // Reset selection if currently selected option is now disabled
        if (noregBaruSelect.value === noregLama) {
            noregBaruSelect.value = '';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateNoregBaru();
    });
    </script>
</body>

</html>