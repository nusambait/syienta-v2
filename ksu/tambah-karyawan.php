<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Tambahkan proses di sini
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitasi input
    $nik = mysqli_real_escape_string($connect, $_POST['nik']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $jk = mysqli_real_escape_string($connect, $_POST['jk']);
    $tgl_lahir = mysqli_real_escape_string($connect, $_POST['tgl_lahir']);
    $alamat = mysqli_real_escape_string($connect, $_POST['alamat']);
    $jabatan = mysqli_real_escape_string($connect, $_POST['jabatan']);
    $kantor = mysqli_real_escape_string($connect, $_POST['kantor']);
    $pendidikan = mysqli_real_escape_string($connect, $_POST['pendidikan']);
    $tgl_masuk = mysqli_real_escape_string($connect, $_POST['tgl_masuk']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    // Cek apakah NIK sudah ada
    $check_nik = mysqli_query($connect, "SELECT nik FROM ksu_karyawan WHERE nik = '$nik'");
    if (mysqli_num_rows($check_nik) > 0) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'NIK sudah terdaftar!'
                });
            });
        </script>";
    } else {
        // Hitung masa kerja
        $today = new DateTime();
        $join_date = new DateTime($tgl_masuk);
        $interval = $today->diff($join_date);

        $masa_kerja = '';
        if ($interval->y > 0) {
            $masa_kerja = $interval->y . " tahun";
            if ($interval->m > 0) {
                $masa_kerja .= " " . $interval->m . " bulan";
            }
        } elseif ($interval->m > 0) {
            $masa_kerja = $interval->m . " bulan";
        } else {
            $masa_kerja = "Baru masuk";
        }

        // Query insert
        $query = "INSERT INTO ksu_karyawan (nik, nama, jk, tgl_lahir, alamat, jabatan, kantor, pendidikan, tgl_masuk, masa_kerja, status) 
                  VALUES ('$nik', '$nama', '$jk', '$tgl_lahir', '$alamat', '$jabatan', '$kantor', '$pendidikan', '$tgl_masuk', '$masa_kerja', '$status')";

        if (mysqli_query($connect, $query)) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data karyawan berhasil ditambahkan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.href = 'data-karyawan.php';
                    });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Error: " . mysqli_error($connect) . "'
                    });
                });
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    .required::after {
        content: " *";
        color: red;
    }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Tambah Karyawan Baru</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nik" class="form-label required">NIK</label>
                                            <input type="text" class="form-control" id="nik" name="nik" maxlength="16"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nama" class="form-label required">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama" name="nama" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="jk" class="form-label required">Jenis Kelamin</label>
                                            <select class="form-select" id="jk" name="jk" required>
                                                <option value="">Pilih Jenis Kelamin</option>
                                                <option value="LAKI-LAKI">Laki-laki</option>
                                                <option value="PEREMPUAN">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tgl_lahir" class="form-label required">Tanggal Lahir</label>
                                            <input type="text" class="form-control date-input" id="tgl_lahir"
                                                name="tgl_lahir" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="alamat" class="form-label required">Alamat</label>
                                            <textarea class="form-control" id="alamat" name="alamat" rows="3"
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jabatan" class="form-label required">Jabatan</label>
                                            <input type="text" class="form-control" id="jabatan" name="jabatan"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="kantor" class="form-label required">Kantor</label>
                                            <select class="form-select" id="kantor" name="kantor" required>
                                                <option value="">Pilih Kantor</option>
                                                <?php
                                                $query = "SELECT DISTINCT kantor FROM ksu_karyawan WHERE kantor IS NOT NULL ORDER BY kantor";
                                                $result = mysqli_query($connect, $query);
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<option value='" . htmlspecialchars($row['kantor']) . "'>" . htmlspecialchars($row['kantor']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="pendidikan" class="form-label required">Pendidikan
                                                Terakhir</label>
                                            <select class="form-select" id="pendidikan" name="pendidikan" required>
                                                <option value="">Pilih Pendidikan</option>
                                                <option value="SD">SD</option>
                                                <option value="SMP">SMP</option>
                                                <option value="SMA/SMK">SMA/SMK</option>
                                                <option value="D1">D1</option>
                                                <option value="D2">D2</option>
                                                <option value="D3">D3</option>
                                                <option value="D4">D4</option>
                                                <option value="S1">S1</option>
                                                <option value="S2">S2</option>
                                                <option value="S3">S3</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tgl_masuk" class="form-label required">Tanggal Masuk</label>
                                            <input type="text" class="form-control date-input" id="tgl_masuk"
                                                name="tgl_masuk" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="status" class="form-label required">Status</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="">Pilih Status</option>
                                                <option value="TETAP">TETAP</option>
                                                <option value="KONTRAK">KONTRAK</option>
                                                <option value="TRAINEE">TRAINEE</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    <a href="data-karyawan.php" class="btn btn-secondary me-2">Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
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

    // Fungsi untuk mengkonversi dd-mm-yyyy ke yyyy-mm-dd
    function convertDateFormat(dateStr) {
        if (!dateStr) return '';

        // Split tanggal berdasarkan -
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;

        // Susun ulang dalam format yyyy-mm-dd
        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    // Modifikasi event listener form submit
    document.querySelector('form').addEventListener('submit', function(e) {
        // Konversi format tanggal sebelum submit
        const tglLahirInput = document.getElementById('tgl_lahir');
        const tglMasukInput = document.getElementById('tgl_masuk');

        // Simpan nilai asli untuk validasi
        const tglLahirOriginal = tglLahirInput.value;
        const tglMasukOriginal = tglMasukInput.value;

        // Konversi ke format yyyy-mm-dd untuk database
        tglLahirInput.value = convertDateFormat(tglLahirOriginal);
        tglMasukInput.value = convertDateFormat(tglMasukOriginal);

        const nik = document.getElementById('nik').value.trim();
        const nama = document.getElementById('nama').value.trim();
        const jk = document.getElementById('jk').value;
        const alamat = document.getElementById('alamat').value.trim();
        const jabatan = document.getElementById('jabatan').value.trim();
        const kantor = document.getElementById('kantor').value;
        const pendidikan = document.getElementById('pendidikan').value;
        const status = document.getElementById('status').value;

        // Validasi field wajib
        if (!nik || !nama || !jk || !tglLahirOriginal || !alamat || !jabatan || !kantor ||
            !pendidikan || !tglMasukOriginal || !status) {
            e.preventDefault();
            // Kembalikan format tanggal ke tampilan asli
            tglLahirInput.value = tglLahirOriginal;
            tglMasukInput.value = tglMasukOriginal;

            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Mohon lengkapi semua field yang wajib diisi (bertanda *)'
            });
            return false;
        }

        // Validasi format tanggal (dd-mm-yyyy)
        const dateRegex = /^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[012])-\d{4}$/;
        if (!dateRegex.test(tglLahirOriginal) || !dateRegex.test(tglMasukOriginal)) {
            e.preventDefault();
            // Kembalikan format tanggal ke tampilan asli
            tglLahirInput.value = tglLahirOriginal;
            tglMasukInput.value = tglMasukOriginal;

            Swal.fire({
                icon: 'error',
                title: 'Format Tanggal Salah',
                text: 'Format tanggal harus DD-MM-YYYY'
            });
            return false;
        }

        // Validasi tanggal lahir tidak lebih dari hari ini
        const today = new Date();
        const birthDate = new Date(convertDateFormat(tglLahirOriginal));
        if (birthDate > today) {
            e.preventDefault();
            // Kembalikan format tanggal ke tampilan asli
            tglLahirInput.value = tglLahirOriginal;
            tglMasukInput.value = tglMasukOriginal;

            Swal.fire({
                icon: 'error',
                title: 'Tanggal Lahir Tidak Valid',
                text: 'Tanggal lahir tidak boleh lebih dari hari ini'
            });
            return false;
        }

        // Validasi tanggal masuk tidak lebih dari hari ini
        const joinDate = new Date(convertDateFormat(tglMasukOriginal));
        if (joinDate > today) {
            e.preventDefault();
            // Kembalikan format tanggal ke tampilan asli
            tglLahirInput.value = tglLahirOriginal;
            tglMasukInput.value = tglMasukOriginal;

            Swal.fire({
                icon: 'error',
                title: 'Tanggal Masuk Tidak Valid',
                text: 'Tanggal masuk tidak boleh lebih dari hari ini'
            });
            return false;
        }

        // Validasi tanggal masuk harus lebih besar dari tanggal lahir
        if (joinDate <= birthDate) {
            e.preventDefault();
            // Kembalikan format tanggal ke tampilan asli
            tglLahirInput.value = tglLahirOriginal;
            tglMasukInput.value = tglMasukOriginal;

            Swal.fire({
                icon: 'error',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal masuk harus lebih besar dari tanggal lahir'
            });
            return false;
        }

        // Jika semua validasi berhasil, form akan disubmit dengan format yyyy-mm-dd
        return true;
    });

    // Tambahkan placeholder untuk input tanggal
    document.querySelectorAll('.date-input').forEach(input => {
        input.setAttribute('placeholder', 'DD-MM-YYYY');
    });

    // Auto-format NIK saat diketik
    document.getElementById('nik').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '').substr(0, 16);
    });
    </script>
</body>

</html>