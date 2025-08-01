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

// Ubah logika hapus BPKB berdasarkan nopol
if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    if (isset($_GET['nopol']) && isset($_GET['nik'])) {
        $nopol = $_GET['nopol'];
        $nik = $_GET['nik'];

        $query = mysqli_query($connect, "DELETE FROM bpkb WHERE nopol='$nopol' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data BPKB berhasil dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-bpkb.php?nik=" . $nik . "';
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
                        text: 'Data BPKB gagal dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-bpkb.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

// Tambahkan logika untuk aksi lepas BPKB
if (isset($_GET['action']) && $_GET['action'] == 'lepas') {
    if (isset($_GET['nopol']) && isset($_GET['nik'])) {
        $nopol = $_GET['nopol'];
        $nik = $_GET['nik'];

        $query = mysqli_query($connect, "UPDATE bpkb SET status='Tersedia' WHERE nopol='$nopol' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status BPKB berhasil diubah menjadi Tersedia',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-bpkb.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

if (isset($_POST['submit'])) {
    $nik = $_POST['nik'];
    $kodjam = $_POST['kodjam'];
    $bpkb = $_POST['bpkb'];
    $nopol = $_POST['nopol'];
    $merk = $_POST['merk'];
    $norang = $_POST['norang'];
    $nomes = $_POST['nomes'];
    $war = $_POST['war'];
    $thnpem = $_POST['thnpem'];
    $bahbak = $_POST['bahbak'];
    $an = $_POST['an'];
    $almt = $_POST['almt'];
    $kec = $_POST['kec'];
    $kab = $_POST['kab'];
    $nm_pemjam = $_POST['nm_pemjam'];
    $almt_pemjam = $_POST['almt_pemjam'];
    $kec_pemjam = $_POST['kec_pemjam'];
    $kab_pemjam = $_POST['kab_pemjam'];
    $pek_pemjam = $_POST['pek_pemjam'];
    $psrwjr = $_POST['psrwjr'];
    $tak = $_POST['tak'];
    $nilpenj = $_POST['nilpenj'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];
    $status = 'Tersedia';

    $query = mysqli_query($connect, "INSERT INTO bpkb (nik, kodjam, bpkb, nopol, merk, norang, nomes, war, thnpem, bahbak, an, almt, kec, kab, nm_pemjam, almt_pemjam, kec_pemjam, kab_pemjam, pek_pemjam, psrwjr, tak, nilpenj, pengikatan, ketjam, status) 
    VALUES ('$nik', '$kodjam', '$bpkb', '$nopol', '$merk', '$norang', '$nomes', '$war', '$thnpem', '$bahbak', '$an', '$almt', '$kec', '$kab', '$nm_pemjam', '$almt_pemjam', '$kec_pemjam', '$kab_pemjam', '$pek_pemjam', '$psrwjr', '$tak', '$nilpenj', '$pengikatan', '$ketjam', '$status')");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil disimpan',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'input-jaminan.php';
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
                text: 'Data gagal disimpan',
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
    <title>Input Data Jaminan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
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
                    <h4 class="mb-0 text-gray-800">Form Input Data BPKB</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form BPKB</li>
                    </ol>
                </div>

                <!-- Tambahan: Tabel Data BPKB -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data BPKB Nasabah <?php echo $nasabah['nama']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Jaminan</th>
                                        <th>No. BPKB</th>
                                        <th>No. Polisi</th>
                                        <th>Merk</th>
                                        <th>Atas Nama</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_bpkb = mysqli_query($connect, "SELECT * FROM bpkb WHERE nik='$nik'");
                                    $no = 1;
                                    if (mysqli_num_rows($query_bpkb) > 0) {
                                        while ($data_bpkb = mysqli_fetch_array($query_bpkb)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $data_bpkb['kodjam']; ?></td>
                                                <td><?php echo $data_bpkb['bpkb']; ?></td>
                                                <td><?php echo $data_bpkb['nopol']; ?></td>
                                                <td><?php echo $data_bpkb['merk']; ?></td>
                                                <td><?php echo $data_bpkb['an']; ?></td>
                                                <td><?php echo $data_bpkb['status']; ?></td>
                                                <td class="d-flex justify-content-center">
                                                    <a href="edit-bpkb.php?nopol=<?php echo $data_bpkb['nopol']; ?>&nik=<?php echo $nik; ?>"
                                                        class="btn btn-warning btn-sm me-1">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($data_bpkb['status'] != 'Tersedia'): ?>
                                                        <button type="button" class="btn btn-success btn-sm me-1"
                                                            onclick="lepasBPKB('<?php echo $data_bpkb['nopol']; ?>')">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="hapusBPKB('<?php echo $data_bpkb['nopol']; ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada data BPKB</td>
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
                        <h5 class="card-title mb-4 bg-judul-card">Form Tambah Data BPKB</h5>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" name="nik" value="<?php echo $nik; ?>"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kode Jaminan</label>
                                    <select class="form-control" name="kodjam" required>
                                        <option value="">Pilih Kode Jaminan</option>
                                        <option value="R2 - MOTOR">R2 - MOTOR</option>
                                        <option value="R4 - MOBIL">R4 - MOBIL</option>
                                        <option value="R4 - PICKUP">R4 - PICKUP</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. BPKB</label>
                                    <input type="text" class="form-control" name="bpkb" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Polisi</label>
                                    <input type="text" class="form-control" name="nopol" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Merk</label>
                                    <input type="text" class="form-control" name="merk" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">No. Rangka</label>
                                    <input type="text" class="form-control" name="norang" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">No. Mesin</label>
                                    <input type="text" class="form-control" name="nomes" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Warna</label>
                                    <input type="text" class="form-control" name="war" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Tahun Pembuatan</label>
                                    <input type="number" class="form-control" name="thnpem" min="1900"
                                        max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Bahan Bakar</label>
                                    <input type="text" class="form-control" name="bahbak" required>
                                </div>

                                <!-- Data Pemilik -->
                                <h5 class="mt-3">Data Pemilik BPKB</h5>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat</label>
                                    <textarea class="form-control" name="almt" required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kabupaten</label>
                                    <select type="text" class="form-control" name="kab" id="kabupaten" required>
                                        <option value="">Pilih Kabupaten</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kecamatan</label>
                                    <select type="text" class="form-control" name="kec" id="kecamatan" required>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                </div>

                                <!-- Data Peminjam -->
                                <h5 class="mt-3">Data Peminjam</h5>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label required">Nama Peminjam</label>
                                    <input type="text" class="form-control" name="nm_pemjam" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label required">Alamat Peminjam</label>
                                    <textarea class="form-control" name="almt_pemjam" required></textarea>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Kabupaten Peminjam</label>
                                    <input type="text" class="form-control" name="kab_pemjam" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Kecamatan Peminjam</label>
                                    <input type="text" class="form-control" name="kec_pemjam" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Pekerjaan Peminjam</label>
                                    <input type="text" class="form-control" name="pek_pemjam" required>
                                </div>

                                <!-- Data Nilai -->
                                <h5 class="mt-3">Data Nilai</h5>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Pasar Wajar</label>
                                    <input type="number" class="form-control" name="psrwjr" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Taksasi</label>
                                    <input type="number" class="form-control" name="tak" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">Nilai Penjamin</label>
                                    <input type="number" class="form-control" name="nilpenj" required>
                                </div>

                                <!-- Data Tambahan -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN">INTERN</option>
                                        <option value="FEO Notaril">FEO Notaril</option>
                                        <option value="Fiducia">Fiducia</option>
                                        <option value="Fiducia (Terdaftar)">Fiducia (Terdaftar)</option>
                                        <option value="WAARMERKING">WAARMERKING</option>
                                        <option value="ADDENDUM">ADDENDUM</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control" name="ketjam"></textarea>
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
    <script src="<?php echo $base_url; ?>assets/js/input-date.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
    <script>
        function hapusBPKB(nopol) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data BPKB akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bpkb.php?action=hapus&nopol=' + nopol + '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function lepasBPKB(nopol) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Status BPKB akan diubah menjadi Tersedia!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lepas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bpkb.php?action=lepas&nopol=' + nopol + '&nik=<?php echo $nik; ?>';
                }
            })
        }
    </script>
</body>

</html>