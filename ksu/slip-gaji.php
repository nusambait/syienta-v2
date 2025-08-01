<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Array nama bulan - pindahkan ke bagian atas file setelah session_start()
$nama_bulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// Cek apakah parameter id tersedia
if (!isset($_GET['id'])) {
    echo "<script>window.location.href='data-karyawan.php';</script>";
    exit;
}

$id = mysqli_real_escape_string($connect, $_GET['id']);

// Ambil data karyawan berdasarkan id
$query_karyawan = mysqli_query($connect, "SELECT * FROM ksu_karyawan WHERE id='$id'");
if (mysqli_num_rows($query_karyawan) == 0) {
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
$karyawan = mysqli_fetch_assoc($query_karyawan);

// Ambil bulan dan tahun dari URL, jika tidak ada gunakan bulan dan tahun sekarang
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Debug untuk memeriksa nilai bulan
error_log("Nilai bulan dari URL: " . $bulan);

// Pastikan nilai bulan valid (1-12)
$bulan = max(1, min(12, $bulan));

// Debug untuk memeriksa nilai
error_log("Debug - Bulan: $bulan");
error_log("Debug - Nama Bulan: " . $nama_bulan[$bulan]);

// Debug - tambahkan ini untuk memeriksa nilai
error_log("Bulan: " . $bulan . " - Nama Bulan: " . (isset($nama_bulan[$bulan]) ? $nama_bulan[$bulan] : 'tidak ditemukan'));

// Fungsi untuk mendapatkan nama bulan
function getNamaBulan($bulan)
{
    $nama_bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    return isset($nama_bulan[(int)$bulan]) ? $nama_bulan[(int)$bulan] : '';
}

// Cek apakah ada data gaji untuk bulan dan tahun yang dipilih
$query_gaji = mysqli_query($connect, "SELECT *, CAST(bulan AS SIGNED) as bulan_int 
    FROM ksu_gaji_karyawan 
    WHERE id_karyawan='$id' AND bulan='$bulan' AND tahun='$tahun' LIMIT 1");
$gaji = mysqli_fetch_assoc($query_gaji);

// Debug untuk memeriksa nilai
error_log("Debug - Bulan dari DB: " . (isset($gaji['bulan']) ? $gaji['bulan'] : 'tidak ada'));
error_log("Debug - Bulan Integer: " . (isset($gaji['bulan_int']) ? $gaji['bulan_int'] : 'tidak ada'));

// Proses tambah/update data gaji
if (isset($_POST['submit'])) {
    // Ambil nilai bulan dan tahun dari form
    $bulan = (int)$_POST['bulan'];
    $tahun = (int)$_POST['tahun'];

    // Validasi periode
    if ($bulan < 1 || $bulan > 12) {
        $_SESSION['error_message'] = "Bulan tidak valid!";
        header("Location: slip-gaji.php?id=$id");
        exit;
    }

    // Cek apakah data gaji untuk periode tersebut sudah ada
    $check_periode = mysqli_query($connect, "SELECT id FROM ksu_gaji_karyawan 
        WHERE id_karyawan='$id' AND bulan='$bulan' AND tahun='$tahun'");

    if (mysqli_num_rows($check_periode) > 0 && !isset($gaji)) {
        $_SESSION['error_message'] = "Data gaji untuk periode " . getNamaBulan($bulan) . " $tahun sudah ada!";
        header("Location: slip-gaji.php?id=$id");
        exit;
    }

    // Konversi nilai form ke format angka
    $gaji_pokok = str_replace(['Rp', '.', ' '], '', $_POST['gaji_pokok']);
    $tunj_jabatan = str_replace(['Rp', '.', ' '], '', $_POST['tunj_jabatan']);
    $tunj_makan = str_replace(['Rp', '.', ' '], '', $_POST['tunj_makan']);
    $tunj_transport = str_replace(['Rp', '.', ' '], '', $_POST['tunj_transport']);
    $tunj_pulsa = str_replace(['Rp', '.', ' '], '', $_POST['tunj_pulsa']);
    $tunj_cuti = str_replace(['Rp', '.', ' '], '', $_POST['tunj_cuti']);
    $tunj_thr = str_replace(['Rp', '.', ' '], '', $_POST['tunj_thr']);
    $tunj_lembur = str_replace(['Rp', '.', ' '], '', $_POST['tunj_lembur']);
    $tunj_lainnya = str_replace(['Rp', '.', ' '], '', $_POST['tunj_lainnya']);
    $tunj_bpjs_jht = str_replace(['Rp', '.', ' '], '', $_POST['tunj_bpjs_jht']);
    $tunj_bpjs_pensiun = str_replace(['Rp', '.', ' '], '', $_POST['tunj_bpjs_pensiun']);
    $tunj_bpjs_kesehatan = str_replace(['Rp', '.', ' '], '', $_POST['tunj_bpjs_kesehatan']);
    $tunj_dplk = str_replace(['Rp', '.', ' '], '', $_POST['tunj_dplk']);

    // Potongan
    $potongan_bpjs_jht = str_replace(['Rp', '.', ' '], '', $_POST['potongan_bpjs_jht']);
    $potongan_bpjs_pensiun = str_replace(['Rp', '.', ' '], '', $_POST['potongan_bpjs_pensiun']);
    $potongan_bpjs_kesehatan = str_replace(['Rp', '.', ' '], '', $_POST['potongan_bpjs_kesehatan']);
    $potongan_dplk = str_replace(['Rp', '.', ' '], '', $_POST['potongan_dplk']);
    $potongan_pinjaman = str_replace(['Rp', '.', ' '], '', $_POST['potongan_pinjaman']);
    $potongan_simpanan_pokok = str_replace(['Rp', '.', ' '], '', $_POST['potongan_simpanan_pokok']);
    $potongan_simpanan_wajib = str_replace(['Rp', '.', ' '], '', $_POST['potongan_simpanan_wajib']);
    $potongan_simpanan_sukarela = str_replace(['Rp', '.', ' '], '', $_POST['potongan_simpanan_sukarela']);
    $potongan_iuran_piknik = str_replace(['Rp', '.', ' '], '', $_POST['potongan_iuran_piknik']);
    $potongan_pinjaman_koperasi = str_replace(['Rp', '.', ' '], '', $_POST['potongan_pinjaman_koperasi']);
    $potongan_bon_barang = str_replace(['Rp', '.', ' '], '', $_POST['potongan_bon_barang']);
    $potongan_koperasi_lainnya = str_replace(['Rp', '.', ' '], '', $_POST['potongan_koperasi_lainnya']);
    $potongan_lainnya_ang_mhb = str_replace(['Rp', '.', ' '], '', $_POST['potongan_lainnya_ang_mhb']);
    $potongan_lainnya_arisan = str_replace(['Rp', '.', ' '], '', $_POST['potongan_lainnya_arisan']);
    $potongan_lainnya_platinum_pulsa = str_replace(['Rp', '.', ' '], '', $_POST['potongan_lainnya_platinum_pulsa']);
    $potongan_lainnya = str_replace(['Rp', '.', ' '], '', $_POST['potongan_lainnya']);

    // Convert empty strings to 0
    $fields = [
        'gaji_pokok',
        'tunj_jabatan',
        'tunj_makan',
        'tunj_transport',
        'tunj_pulsa',
        'tunj_cuti',
        'tunj_thr',
        'tunj_lembur',
        'tunj_lainnya',
        'tunj_bpjs_jht',
        'tunj_bpjs_pensiun',
        'tunj_bpjs_kesehatan',
        'tunj_dplk',
        'potongan_bpjs_jht',
        'potongan_bpjs_pensiun',
        'potongan_bpjs_kesehatan',
        'potongan_dplk',
        'potongan_pinjaman',
        'potongan_simpanan_pokok',
        'potongan_simpanan_wajib',
        'potongan_simpanan_sukarela',
        'potongan_iuran_piknik',
        'potongan_pinjaman_koperasi',
        'potongan_bon_barang',
        'potongan_koperasi_lainnya',
        'potongan_lainnya_ang_mhb',
        'potongan_lainnya_arisan',
        'potongan_lainnya_platinum_pulsa',
        'potongan_lainnya'
    ];

    foreach ($fields as $field) {
        ${$field} = ${$field} === '' ? '0' : ${$field};
    }

    // Hitung total
    $jumlah_gaji = $gaji_pokok + $tunj_jabatan + $tunj_makan + $tunj_transport +
        $tunj_pulsa + $tunj_cuti + $tunj_thr + $tunj_lembur +
        $tunj_lainnya + $tunj_bpjs_jht + $tunj_bpjs_pensiun +
        $tunj_bpjs_kesehatan + $tunj_dplk;

    $total_potongan = $potongan_bpjs_jht + $potongan_bpjs_pensiun +
        $potongan_bpjs_kesehatan + $potongan_dplk +
        $potongan_pinjaman + $potongan_simpanan_pokok +
        $potongan_simpanan_wajib + $potongan_simpanan_sukarela +
        $potongan_iuran_piknik + $potongan_pinjaman_koperasi +
        $potongan_bon_barang + $potongan_koperasi_lainnya +
        $potongan_lainnya_ang_mhb + $potongan_lainnya_arisan +
        $potongan_lainnya_platinum_pulsa + $potongan_lainnya;

    $total_gaji_bersih = $jumlah_gaji - $total_potongan;

    // Debug
    error_log("Attempting to process salary data for ID: $id, Month: $bulan, Year: $tahun");

    // Cek apakah data gaji sudah ada
    $check_query = mysqli_query($connect, "SELECT id FROM ksu_gaji_karyawan WHERE id_karyawan='$id' AND bulan='$bulan' AND tahun='$tahun'");

    if (mysqli_num_rows($check_query) > 0) {
        // Update existing data
        $sql = "UPDATE ksu_gaji_karyawan SET 
                gaji_pokok='$gaji_pokok',
                tunj_jabatan='$tunj_jabatan',
                tunj_makan='$tunj_makan',
                tunj_transport='$tunj_transport',
                tunj_pulsa='$tunj_pulsa',
                tunj_cuti='$tunj_cuti',
                tunj_thr='$tunj_thr',
                tunj_lembur='$tunj_lembur',
                tunj_lainnya='$tunj_lainnya',
                tunj_bpjs_jht='$tunj_bpjs_jht',
                tunj_bpjs_pensiun='$tunj_bpjs_pensiun',
                tunj_bpjs_kesehatan='$tunj_bpjs_kesehatan',
                tunj_dplk='$tunj_dplk',
                jumlah_gaji='$jumlah_gaji',
                potongan_bpjs_jht='$potongan_bpjs_jht',
                potongan_bpjs_pensiun='$potongan_bpjs_pensiun',
                potongan_bpjs_kesehatan='$potongan_bpjs_kesehatan',
                potongan_dplk='$potongan_dplk',
                potongan_pinjaman='$potongan_pinjaman',
                potongan_simpanan_pokok='$potongan_simpanan_pokok',
                potongan_simpanan_wajib='$potongan_simpanan_wajib',
                potongan_simpanan_sukarela='$potongan_simpanan_sukarela',
                potongan_iuran_piknik='$potongan_iuran_piknik',
                potongan_pinjaman_koperasi='$potongan_pinjaman_koperasi',
                potongan_bon_barang='$potongan_bon_barang',
                potongan_koperasi_lainnya='$potongan_koperasi_lainnya',
                potongan_lainnya_ang_mhb='$potongan_lainnya_ang_mhb',
                potongan_lainnya_arisan='$potongan_lainnya_arisan',
                potongan_lainnya_platinum_pulsa='$potongan_lainnya_platinum_pulsa',
                potongan_lainnya='$potongan_lainnya',
                total_potongan='$total_potongan',
                total_gaji_bersih='$total_gaji_bersih'
                WHERE id_karyawan='$id' AND bulan='$bulan' AND tahun='$tahun'";
    } else {
        // Insert new data
        $sql = "INSERT INTO ksu_gaji_karyawan (
            id_karyawan, bulan, tahun, gaji_pokok, 
                tunj_jabatan, tunj_makan, tunj_transport, tunj_pulsa, 
                tunj_cuti, tunj_thr, tunj_lembur, tunj_lainnya,
                tunj_bpjs_jht, tunj_bpjs_pensiun, tunj_bpjs_kesehatan, 
                tunj_dplk, jumlah_gaji, potongan_bpjs_jht, 
                potongan_bpjs_pensiun, potongan_bpjs_kesehatan, 
                potongan_dplk, potongan_pinjaman, potongan_simpanan_pokok,
                potongan_simpanan_wajib, potongan_simpanan_sukarela,
                potongan_iuran_piknik, potongan_pinjaman_koperasi,
                potongan_bon_barang, potongan_koperasi_lainnya,
                potongan_lainnya_ang_mhb, potongan_lainnya_arisan,
                potongan_lainnya_platinum_pulsa, potongan_lainnya,
                total_potongan, total_gaji_bersih
        ) VALUES (
            '$id', '$bulan', '$tahun', '$gaji_pokok',
                '$tunj_jabatan', '$tunj_makan', '$tunj_transport', '$tunj_pulsa',
                '$tunj_cuti', '$tunj_thr', '$tunj_lembur', '$tunj_lainnya',
                '$tunj_bpjs_jht', '$tunj_bpjs_pensiun', '$tunj_bpjs_kesehatan',
                '$tunj_dplk', '$jumlah_gaji', '$potongan_bpjs_jht',
                '$potongan_bpjs_pensiun', '$potongan_bpjs_kesehatan',
                '$potongan_dplk', '$potongan_pinjaman', '$potongan_simpanan_pokok',
                '$potongan_simpanan_wajib', '$potongan_simpanan_sukarela',
                '$potongan_iuran_piknik', '$potongan_pinjaman_koperasi',
                '$potongan_bon_barang', '$potongan_koperasi_lainnya',
                '$potongan_lainnya_ang_mhb', '$potongan_lainnya_arisan',
                '$potongan_lainnya_platinum_pulsa', '$potongan_lainnya',
                '$total_potongan', '$total_gaji_bersih'
            )";
    }

    // Execute query and handle result
    if (mysqli_query($connect, $sql)) {
        $_SESSION['success_message'] = "Data gaji berhasil disimpan!";
    } else {
        $_SESSION['error_message'] = "Error: " . mysqli_error($connect);
        error_log("MySQL Error: " . mysqli_error($connect));
    }

    // Redirect
    header("Location: slip-gaji.php?id=$id&bulan=$bulan&tahun=$tahun");
    exit;
}

// Fungsi format rupiah
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.6/dist/jquery.inputmask.min.js"></script>
    <style>
    .modal-xl {
        max-width: 1200px;
    }

    .modal-body {
        max-height: 80vh;
        overflow-y: auto;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.3rem;
    }

    .form-control {
        padding: 0.5rem 0.75rem;
    }

    .form-control:hover {
        border-color: #80bdff;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }

    .modal h6 {
        color: #2c3e50;
        border-bottom: 2px solid #eee;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.5rem;
    }

    .badge {
        font-weight: normal;
    }

    .btn-group-sm>.btn {
        padding: .25rem .5rem;
        font-size: .75rem;
    }

    .card {
        border: none;
        margin-bottom: 1rem;
    }

    .shadow-sm {
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, .075);
    }

    .table-striped>tbody>tr:nth-of-type(odd)>* {
        background-color: rgba(0, 0, 0, .02);
    }

    .pagination .page-link {
        color: #435ebe;
        padding: 0.375rem 0.75rem;
    }

    .pagination .page-item.active .page-link {
        background-color: #435ebe;
        border-color: #435ebe;
    }

    .pagination .page-link:hover {
        color: #2c3e50;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .pagination .page-link:focus {
        box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
    }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Data Gaji Karyawan</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-person"></i> <?php echo $karyawan['nama']; ?> -
                        <small><?php echo $karyawan['jabatan']; ?></small>
                    </p>
                </div>
                <a href="data-karyawan.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']);
            endif; ?>

            <!-- Main Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="card-title mb-0">Riwayat Gaji</h5>
                        </div>
                        <div class="col-md-8 text-md-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalGaji">
                                    <i class="bi bi-plus-circle"></i> Input Gaji Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Tabel Riwayat Gaji -->
                    <div class="table-responsive">
                        <?php
                        // Konfigurasi pagination
                        $per_page = 12;
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $start = ($page - 1) * $per_page;

                        // Query untuk total data
                        $total_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM ksu_gaji_karyawan WHERE id_karyawan='$id'");
                        $total_data = mysqli_fetch_assoc($total_query)['total'];
                        $total_pages = ceil($total_data / $per_page);

                        // Query dengan limit untuk pagination
                        $query_all_gaji = mysqli_query($connect, "
                            SELECT *, CAST(bulan AS SIGNED) as bulan_int 
                            FROM ksu_gaji_karyawan 
                            WHERE id_karyawan='$id'
                            ORDER BY tahun DESC, bulan DESC
                            LIMIT $start, $per_page
                        ");
                        ?>

                        <table class="table table-hover table-striped table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Periode</th>
                                    <th class="text-end">Gaji Pokok</th>
                                    <th class="text-end">Total Tunjangan</th>
                                    <th class="text-end">Total Potongan</th>
                                    <th class="text-end">Gaji Bersih</th>
                                    <th class="text-center" style="width: 100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($query_all_gaji) > 0):
                                    while ($row = mysqli_fetch_assoc($query_all_gaji)):
                                        $total_tunjangan = $row['jumlah_gaji'] - $row['gaji_pokok'];
                                        $is_current = ($row['bulan_int'] == $bulan && $row['tahun'] == $tahun);
                                ?>
                                <tr <?php echo $is_current ? 'class="table-active"' : ''; ?>>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo getNamaBulan($row['bulan_int']) . ' ' . $row['tahun']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?php echo formatRupiah($row['gaji_pokok']); ?></td>
                                    <td class="text-end"><?php echo formatRupiah($total_tunjangan); ?></td>
                                    <td class="text-end"><?php echo formatRupiah($row['total_potongan']); ?></td>
                                    <td class="text-end fw-bold"><?php echo formatRupiah($row['total_gaji_bersih']); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="?id=<?php echo $id; ?>&bulan=<?php echo $row['bulan_int']; ?>&tahun=<?php echo $row['tahun']; ?>"
                                                class="btn btn-outline-primary btn-sm" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalGaji"
                                                onclick="editGaji(<?php echo $row['bulan_int']; ?>, <?php echo $row['tahun']; ?>)"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="cetak-slip.php?id=<?php echo $id; ?>&bulan=<?php echo $row['bulan_int']; ?>&tahun=<?php echo $row['tahun']; ?>"
                                                class="btn btn-outline-success btn-sm" target="_blank" title="Cetak">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                    ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3">
                                        <div class="text-muted">
                                            <i class="bi bi-info-circle"></i> Belum ada data gaji
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Menampilkan <?php echo $start + 1; ?> -
                                <?php echo min($start + $per_page, $total_data); ?> dari <?php echo $total_data; ?> data
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?id=<?php echo $id; ?>&page=<?php echo ($page - 1); ?>"
                                            aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php
                                        // Tampilkan maksimal 5 nomor halaman
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $start_page + 4);

                                        if ($end_page - $start_page < 4) {
                                            $start_page = max(1, $end_page - 4);
                                        }

                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?id=<?php echo $id; ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?id=<?php echo $id; ?>&page=<?php echo ($page + 1); ?>"
                                            aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Detail Gaji Card -->
            <?php if (isset($gaji)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        Detail Gaji - <?php echo getNamaBulan($gaji['bulan_int']) . ' ' . $tahun; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Kolom Pendapatan -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0">Pendapatan</h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>Gaji Pokok</td>
                                            <td class="text-end"><?php echo formatRupiah($gaji['gaji_pokok']); ?></td>
                                        </tr>
                                        <!-- ... tambahkan baris tunjangan lainnya ... -->
                                        <tr class="table-success">
                                            <th>Total Pendapatan</th>
                                            <th class="text-end"><?php echo formatRupiah($gaji['jumlah_gaji']); ?></th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Potongan -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-danger text-white py-2">
                                    <h6 class="mb-0">Potongan</h6>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <!-- ... tambahkan baris potongan ... -->
                                        <tr class="table-danger">
                                            <th>Total Potongan</th>
                                            <th class="text-end"><?php echo formatRupiah($gaji['total_potongan']); ?>
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Gaji Bersih -->
                    <div class="card bg-primary text-white mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Total Gaji Bersih</h6>
                                <h5 class="mb-0"><?php echo formatRupiah($gaji['total_gaji_bersih']); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Input/Edit Gaji -->
    <div class="modal fade" id="modalGaji" tabindex="-1" aria-labelledby="modalGajiLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGajiLabel">
                        <?php echo mysqli_num_rows($query_gaji) > 0 ? 'Edit' : 'Input'; ?> Gaji Karyawan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="mb-3">Periode Gaji</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Bulan</label>
                                                <select name="bulan" class="form-select" required>
                                                    <?php
                                                    $bulan_sekarang = isset($gaji['bulan_int']) ? $gaji['bulan_int'] : date('n');
                                                    foreach ($nama_bulan as $num => $nama) :
                                                    ?>
                                                    <option value="<?php echo $num; ?>"
                                                        <?php echo ($bulan_sekarang == $num) ? 'selected' : ''; ?>>
                                                        <?php echo $nama; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tahun</label>
                                                <select name="tahun" class="form-select" required>
                                                    <?php
                                                    $tahun_sekarang = isset($gaji['tahun']) ? $gaji['tahun'] : date('Y');
                                                    for ($i = $tahun_sekarang - 1; $i <= $tahun_sekarang + 1; $i++) :
                                                    ?>
                                                    <option value="<?php echo $i; ?>"
                                                        <?php echo ($tahun_sekarang == $i) ? 'selected' : ''; ?>>
                                                        <?php echo $i; ?>
                                                    </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="mb-3">Informasi Karyawan</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="100">Nama</th>
                                                <td>: <?php echo $karyawan['nama']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Jabatan</th>
                                                <td>: <?php echo $karyawan['jabatan']; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <!-- Pendapatan -->
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3 fw-bold">Pendapatan</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Gaji Pokok</label>
                                        <input type="text" class="form-control currency" name="gaji_pokok"
                                            value="<?php echo isset($gaji['gaji_pokok']) ? $gaji['gaji_pokok'] : ''; ?>"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan Jabatan</label>
                                        <input type="text" class="form-control currency" name="tunj_jabatan"
                                            value="<?php echo isset($gaji['tunj_jabatan']) ? $gaji['tunj_jabatan'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan Makan</label>
                                        <input type="text" class="form-control currency" name="tunj_makan"
                                            value="<?php echo isset($gaji['tunj_makan']) ? $gaji['tunj_makan'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan Transport</label>
                                        <input type="text" class="form-control currency" name="tunj_transport"
                                            value="<?php echo isset($gaji['tunj_transport']) ? $gaji['tunj_transport'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan Pulsa</label>
                                        <input type="text" class="form-control currency" name="tunj_pulsa"
                                            value="<?php echo isset($gaji['tunj_pulsa']) ? $gaji['tunj_pulsa'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan Cuti</label>
                                        <input type="text" class="form-control currency" name="tunj_cuti"
                                            value="<?php echo isset($gaji['tunj_cuti']) ? $gaji['tunj_cuti'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan THR</label>
                                        <input type="text" class="form-control currency" name="tunj_thr"
                                            value="<?php echo isset($gaji['tunj_thr']) ? $gaji['tunj_thr'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan Lembur</label>
                                        <input type="text" class="form-control currency" name="tunj_lembur"
                                            value="<?php echo isset($gaji['tunj_lembur']) ? $gaji['tunj_lembur'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan Lainnya</label>
                                        <input type="text" class="form-control currency" name="tunj_lainnya"
                                            value="<?php echo isset($gaji['tunj_lainnya']) ? $gaji['tunj_lainnya'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan BPJS JHT</label>
                                        <input type="text" class="form-control currency" name="tunj_bpjs_jht"
                                            value="<?php echo isset($gaji['tunj_bpjs_jht']) ? $gaji['tunj_bpjs_jht'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan BPJS Pensiun</label>
                                        <input type="text" class="form-control currency" name="tunj_bpjs_pensiun"
                                            value="<?php echo isset($gaji['tunj_bpjs_pensiun']) ? $gaji['tunj_bpjs_pensiun'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan BPJS Kesehatan</label>
                                        <input type="text" class="form-control currency" name="tunj_bpjs_kesehatan"
                                            value="<?php echo isset($gaji['tunj_bpjs_kesehatan']) ? $gaji['tunj_bpjs_kesehatan'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tunjangan DPLK</label>
                                        <input type="text" class="form-control currency" name="tunj_dplk"
                                            value="<?php echo isset($gaji['tunj_dplk']) ? $gaji['tunj_dplk'] : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Potongan -->
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-3 fw-bold">Potongan</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan BPJS JHT</label>
                                        <input type="text" class="form-control currency" name="potongan_bpjs_jht"
                                            value="<?php echo isset($gaji['potongan_bpjs_jht']) ? $gaji['potongan_bpjs_jht'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan BPJS Pensiun</label>
                                        <input type="text" class="form-control currency" name="potongan_bpjs_pensiun"
                                            value="<?php echo isset($gaji['potongan_bpjs_pensiun']) ? $gaji['potongan_bpjs_pensiun'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan BPJS Kesehatan</label>
                                        <input type="text" class="form-control currency" name="potongan_bpjs_kesehatan"
                                            value="<?php echo isset($gaji['potongan_bpjs_kesehatan']) ? $gaji['potongan_bpjs_kesehatan'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan DPLK</label>
                                        <input type="text" class="form-control currency" name="potongan_dplk"
                                            value="<?php echo isset($gaji['potongan_dplk']) ? $gaji['potongan_dplk'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Pinjaman</label>
                                        <input type="text" class="form-control currency" name="potongan_pinjaman"
                                            value="<?php echo isset($gaji['potongan_pinjaman']) ? $gaji['potongan_pinjaman'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Simpanan Pokok</label>
                                        <input type="text" class="form-control currency" name="potongan_simpanan_pokok"
                                            value="<?php echo isset($gaji['potongan_simpanan_pokok']) ? $gaji['potongan_simpanan_pokok'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Simpanan Wajib</label>
                                        <input type="text" class="form-control currency" name="potongan_simpanan_wajib"
                                            value="<?php echo isset($gaji['potongan_simpanan_wajib']) ? $gaji['potongan_simpanan_wajib'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Simpanan Sukarela</label>
                                        <input type="text" class="form-control currency"
                                            name="potongan_simpanan_sukarela"
                                            value="<?php echo isset($gaji['potongan_simpanan_sukarela']) ? $gaji['potongan_simpanan_sukarela'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Iuran Piknik</label>
                                        <input type="text" class="form-control currency" name="potongan_iuran_piknik"
                                            value="<?php echo isset($gaji['potongan_iuran_piknik']) ? $gaji['potongan_iuran_piknik'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Pinjaman Koperasi</label>
                                        <input type="text" class="form-control currency"
                                            name="potongan_pinjaman_koperasi"
                                            value="<?php echo isset($gaji['potongan_pinjaman_koperasi']) ? $gaji['potongan_pinjaman_koperasi'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Bon Barang</label>
                                        <input type="text" class="form-control currency" name="potongan_bon_barang"
                                            value="<?php echo isset($gaji['potongan_bon_barang']) ? $gaji['potongan_bon_barang'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Koperasi Lainnya</label>
                                        <input type="text" class="form-control currency"
                                            name="potongan_koperasi_lainnya"
                                            value="<?php echo isset($gaji['potongan_koperasi_lainnya']) ? $gaji['potongan_koperasi_lainnya'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan ANG/MHB</label>
                                        <input type="text" class="form-control currency" name="potongan_lainnya_ang_mhb"
                                            value="<?php echo isset($gaji['potongan_lainnya_ang_mhb']) ? $gaji['potongan_lainnya_ang_mhb'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Arisan</label>
                                        <input type="text" class="form-control currency" name="potongan_lainnya_arisan"
                                            value="<?php echo isset($gaji['potongan_lainnya_arisan']) ? $gaji['potongan_lainnya_arisan'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Platinum Pulsa</label>
                                        <input type="text" class="form-control currency"
                                            name="potongan_lainnya_platinum_pulsa"
                                            value="<?php echo isset($gaji['potongan_lainnya_platinum_pulsa']) ? $gaji['potongan_lainnya_platinum_pulsa'] : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Potongan Lainnya</label>
                                        <input type="text" class="form-control currency" name="potongan_lainnya"
                                            value="<?php echo isset($gaji['potongan_lainnya']) ? $gaji['potongan_lainnya'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

        if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 767) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });

    // Format currency input
    $(document).ready(function() {
        $('.currency').inputmask({
            alias: 'numeric',
            groupSeparator: '.',
            autoGroup: true,
            digits: 0,
            digitsOptional: false,
            prefix: 'Rp ',
            placeholder: '0',
            removeMaskOnSubmit: true
        });
    });

    // Print slip gaji
    function printSlip() {
        window.print();
    }

    // Mencegah form auto-submit saat dropdown berubah
    document.getElementById('bulanSelect').addEventListener('change', function(e) {
        e.preventDefault(); // Mencegah form submit otomatis
    });

    document.getElementById('tahunSelect').addEventListener('change', function(e) {
        e.preventDefault(); // Mencegah form submit otomatis
    });

    function editGaji(bulan, tahun) {
        // Set nilai bulan dan tahun pada form
        document.querySelector('select[name="bulan"]').value = bulan;
        document.querySelector('select[name="tahun"]').value = tahun;

        // Ambil data gaji menggunakan AJAX
        $.ajax({
            url: 'get-gaji.php',
            type: 'POST',
            data: {
                id: <?php echo $id; ?>,
                bulan: bulan,
                tahun: tahun
            },
            success: function(response) {
                const data = JSON.parse(response);

                // Set nilai form dengan data yang diterima
                document.querySelector('input[name="gaji_pokok"]').value = data.gaji_pokok;
                document.querySelector('input[name="tunj_jabatan"]').value = data.tunj_jabatan;
                document.querySelector('input[name="tunj_makan"]').value = data.tunj_makan;
                document.querySelector('input[name="tunj_transport"]').value = data.tunj_transport;
                document.querySelector('input[name="tunj_pulsa"]').value = data.tunj_pulsa;
                document.querySelector('input[name="tunj_cuti"]').value = data.tunj_cuti;
                document.querySelector('input[name="tunj_thr"]').value = data.tunj_thr;
                document.querySelector('input[name="tunj_lembur"]').value = data.tunj_lembur;
                document.querySelector('input[name="tunj_lainnya"]').value = data.tunj_lainnya;
                document.querySelector('input[name="tunj_bpjs_jht"]').value = data.tunj_bpjs_jht;
                document.querySelector('input[name="tunj_bpjs_pensiun"]').value = data.tunj_bpjs_pensiun;
                document.querySelector('input[name="tunj_bpjs_kesehatan"]').value = data
                .tunj_bpjs_kesehatan;
                document.querySelector('input[name="tunj_dplk"]').value = data.tunj_dplk;

                // Set nilai potongan
                document.querySelector('input[name="potongan_bpjs_jht"]').value = data.potongan_bpjs_jht;
                document.querySelector('input[name="potongan_bpjs_pensiun"]').value = data
                    .potongan_bpjs_pensiun;
                document.querySelector('input[name="potongan_bpjs_kesehatan"]').value = data
                    .potongan_bpjs_kesehatan;
                document.querySelector('input[name="potongan_dplk"]').value = data.potongan_dplk;
                document.querySelector('input[name="potongan_pinjaman"]').value = data.potongan_pinjaman;
                document.querySelector('input[name="potongan_simpanan_pokok"]').value = data
                    .potongan_simpanan_pokok;
                document.querySelector('input[name="potongan_simpanan_wajib"]').value = data
                    .potongan_simpanan_wajib;
                document.querySelector('input[name="potongan_simpanan_sukarela"]').value = data
                    .potongan_simpanan_sukarela;
                document.querySelector('input[name="potongan_iuran_piknik"]').value = data
                    .potongan_iuran_piknik;
                document.querySelector('input[name="potongan_pinjaman_koperasi"]').value = data
                    .potongan_pinjaman_koperasi;
                document.querySelector('input[name="potongan_bon_barang"]').value = data
                .potongan_bon_barang;
                document.querySelector('input[name="potongan_koperasi_lainnya"]').value = data
                    .potongan_koperasi_lainnya;
                document.querySelector('input[name="potongan_lainnya_ang_mhb"]').value = data
                    .potongan_lainnya_ang_mhb;
                document.querySelector('input[name="potongan_lainnya_arisan"]').value = data
                    .potongan_lainnya_arisan;
                document.querySelector('input[name="potongan_lainnya_platinum_pulsa"]').value = data
                    .potongan_lainnya_platinum_pulsa;
                document.querySelector('input[name="potongan_lainnya"]').value = data.potongan_lainnya;

                // Reinitialize currency mask
                $('.currency').inputmask({
                    alias: 'numeric',
                    groupSeparator: '.',
                    autoGroup: true,
                    digits: 0,
                    digitsOptional: false,
                    prefix: 'Rp ',
                    placeholder: '0',
                    removeMaskOnSubmit: true
                });
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengambil data gaji!'
                });
            }
        });
    }
    </script>
</body>

</html>