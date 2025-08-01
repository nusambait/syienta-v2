<?php
session_start();
require_once __DIR__ . '/../config/init.php';
include '../../config.php';
include '../config/config.php';

// Fungsi untuk generate ID record
function generateIdRecord($connect)
{
    do {
        $number = str_pad(mt_rand(1, 99999), 8, '0', STR_PAD_LEFT);
        $id_record = "B-ID" . $number;

        // Cek apakah ID sudah ada di database
        $check_query = mysqli_query($connect, "SELECT id_record FROM tb_nasabah_prosfecting WHERE id_record = '$id_record'");
        $exists = mysqli_num_rows($check_query) > 0;
    } while ($exists);

    return $id_record;
}

// Proses form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['login'])) {
    $no_ktp = mysqli_real_escape_string($connect, $_POST['no_ktp']);
    $nama_nasabah = mysqli_real_escape_string($connect, $_POST['nama_nasabah']);
    $jenis_kelamin = mysqli_real_escape_string($connect, $_POST['jenis_kelamin']);
    $usia = mysqli_real_escape_string($connect, $_POST['usia']);
    $alamat_nasabah = mysqli_real_escape_string($connect, $_POST['alamat_nasabah']);
    $desa = mysqli_real_escape_string($connect, $_POST['desa']);
    $kecamatan = mysqli_real_escape_string($connect, $_POST['kecamatan']);
    $kabupaten = mysqli_real_escape_string($connect, $_POST['kabupaten']);

    // Validasi format tanggal
    $tgl_kunjungan = mysqli_real_escape_string($connect, $_POST['tgl_kunjungan']);
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $tgl_kunjungan)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Format Tanggal Salah',
                text: 'Format tanggal kunjungan harus yyyy-mm-dd'
            });
        </script>";
        exit;
    }

    $kriteria_nasabah = mysqli_real_escape_string($connect, $_POST['kriteria_nasabah']);
    $kriteria_prospek = mysqli_real_escape_string($connect, $_POST['kriteria_prospek']);
    $jenis_prospek = mysqli_real_escape_string($connect, $_POST['jenis_prospek']);
    $hasil_kunjungan = mysqli_real_escape_string($connect, $_POST['hasil_kunjungan']);
    $respon_kunjungan = mysqli_real_escape_string($connect, $_POST['respon_kunjungan']);
    $status_prospek = mysqli_real_escape_string($connect, $_POST['status_prospek']);
    $ket_nasabah = mysqli_real_escape_string($connect, $_POST['ket_nasabah']);
    $no_hp = mysqli_real_escape_string($connect, $_POST['no_hp']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $facebook = mysqli_real_escape_string($connect, $_POST['facebook']);
    $instagram = mysqli_real_escape_string($connect, $_POST['instagram']);
    $jenis_usaha = mysqli_real_escape_string($connect, $_POST['jenis_usaha']);
    $nama_perusahaan = mysqli_real_escape_string($connect, $_POST['nama_perusahaan']);
    $detail_usaha = mysqli_real_escape_string($connect, $_POST['detail_usaha']);
    $mitra = mysqli_real_escape_string($connect, $_POST['mitra']);

    $nama_penginput = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
    $tgl_input = date('Y-m-d');
    $id_record = generateIdRecord($connect);
    $kantor = isset($_SESSION['kantor']) ? $_SESSION['kantor'] : '';
    $kas = mysqli_real_escape_string($connect, $_POST['kas']);
    $kd_ao = isset($_SESSION['kd_ao']) ? $_SESSION['kd_ao'] : '';

    // Validasi jika session kosong
    if (empty($nama_penginput) || empty($kantor) || empty($kd_ao)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Session Expired',
                text: 'Silahkan login kembali!'
            }).then(function() {
                window.location.href = 'login.php';
            });
        </script>";
        exit;
    }

    $query = "INSERT INTO tb_nasabah_prosfecting (
        no_ktp, nama_nasabah, jenis_kelamin, usia, alamat_nasabah, desa, kecamatan, kabupaten,
        kriteria_nasabah, kriteria_prospek, jenis_prospek, tgl_kunjungan, hasil_kunjungan,
        respon_kunjungan, status_prospek, ket_nasabah, no_hp, email, facebook, instagram,
        jenis_usaha, nama_perusahaan, detail_usaha, mitra, tgl_input, nama_penginput,
        id_record, kantor, kas, kd_ao
    ) VALUES (
        '$no_ktp', '$nama_nasabah', '$jenis_kelamin', '$usia', '$alamat_nasabah', '$desa', 
        '$kecamatan', '$kabupaten', '$kriteria_nasabah', '$kriteria_prospek', '$jenis_prospek',
        '$tgl_kunjungan', '$hasil_kunjungan', '$respon_kunjungan', '$status_prospek',
        '$ket_nasabah', '$no_hp', '$email', '$facebook', '$instagram', '$jenis_usaha',
        '$nama_perusahaan', '$detail_usaha', '$mitra', '$tgl_input', '$nama_penginput',
        '$id_record', '$kantor', '$kas', '$kd_ao'
    )";

    if (mysqli_query($connect, $query)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data prospekting nasabah berhasil disimpan!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='prospekting-nasabah.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error: " . mysqli_error($connect) . "'
                });
            });
        </script>";
    }
}

// Proses penghapusan data
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);

    // Cek apakah data exists
    $query_check = mysqli_query($connect, "SELECT nama_penginput FROM tb_nasabah_prosfecting WHERE id = '$id'");

    if (mysqli_num_rows($query_check) > 0) {
        $data_check = mysqli_fetch_array($query_check);

        if ($_SESSION['key_app'] == 'ADMIN' || $_SESSION['nama'] == $data_check['nama_penginput']) {
            $query_delete = mysqli_query($connect, "DELETE FROM tb_nasabah_prosfecting WHERE id = '$id'");

            if ($query_delete) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data prospekting nasabah berhasil dihapus!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.href='prospekting-nasabah.php';
                        });
                    });
                </script>";
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Gagal menghapus data: " . mysqli_error($connect) . "'
                        });
                    });
                </script>";
            }
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: 'Anda tidak memiliki hak untuk menghapus data ini!'
                    });
                });
            </script>";
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Data Tidak Ditemukan',
                    text: 'Data yang akan dihapus tidak ditemukan!'
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
    <title>Prospekting Nasabah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Prospekting Nasabah</h2>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Prospekting Nasabah</h5>
                        <div class="col-md-8 d-flex gap-2">
                            <!-- Tambahkan tombol Export Excel -->
                            <!-- <a href="#" onclick="exportExcel(); return false;" class="btn btn-success btn-sm">
                                <i class="bi bi-download"></i>
                            </a> -->

                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-download"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportExcel(); return false;">
                                            <i class="bi bi-file-earmark-excel"></i> Download XLS
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportPDF(); return false;">
                                            <i class="bi bi-file-earmark-pdf"></i> Download PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- Filter Bulan -->
                            <select class="form-select form-select-sm" id="filterBulan" style="width: auto;">
                                <option value="">Semua Bulan</option>
                                <?php
                                $bulan_array = array(
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember'
                                );

                                foreach ($bulan_array as $value => $label) {
                                    $selected = (isset($_GET['bulan']) && $_GET['bulan'] == $value) ? 'selected' : '';
                                    echo "<option value='$value' $selected>$label</option>";
                                }
                                ?>
                            </select>

                            <!-- Filter Tahun -->
                            <select class="form-select form-select-sm" id="filterTahun" style="width: auto;">
                                <option value="">Semua Tahun</option>
                                <?php
                                $current_year = date('Y');
                                $start_year = $current_year - 3;

                                $query_tahun = mysqli_query($connect, "SELECT DISTINCT YEAR(tgl_kunjungan) as tahun 
                                                                     FROM tb_nasabah_prosfecting 
                                                                     WHERE tgl_kunjungan IS NOT NULL 
                                                                     AND YEAR(tgl_kunjungan) >= $start_year
                                                                     ORDER BY tahun DESC");

                                while ($row_tahun = mysqli_fetch_array($query_tahun)) {
                                    $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $row_tahun['tahun']) ? 'selected' : '';
                                    echo "<option value='{$row_tahun['tahun']}' $selected>{$row_tahun['tahun']}</option>";
                                }
                                ?>
                            </select>

                            <!-- Filter Nama Penginput -->
                            <select class="form-select form-select-sm" id="filterPenginput" style="width: auto;">
                                <option value="">Semua Penginput</option>
                                <?php
                                $query_penginput = mysqli_query($connect, "SELECT DISTINCT nama_penginput 
                                                                         FROM tb_nasabah_prosfecting 
                                                                         WHERE nama_penginput IS NOT NULL 
                                                                         AND nama_penginput != ''
                                                                         ORDER BY nama_penginput ASC");
                                while ($row_penginput = mysqli_fetch_array($query_penginput)) {
                                    $selected = (isset($_GET['penginput']) && $_GET['penginput'] == $row_penginput['nama_penginput']) ? 'selected' : '';
                                    echo "<option value='{$row_penginput['nama_penginput']}' $selected>{$row_penginput['nama_penginput']}</option>";
                                }
                                ?>
                            </select>

                            <input type="text" id="searchInput" class="form-control form-control-sm"
                                placeholder="Cari Nomor Loan atau Nama...">
                            <button class="btn btn-primary btn-sm" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-compact">
                            <thead class="table-light">
                                <tr class="text-nowrap small">
                                    <th class="px-2 py-2">No</th>
                                    <th class="px-2 py-2">No KTP</th>
                                    <th class="px-2 py-2">Nama Nasabah</th>
                                    <th class="px-2 py-2">Jenis Kelamin</th>
                                    <th class="px-2 py-2">Usia</th>
                                    <th class="px-2 py-2">Alamat</th>
                                    <th class="px-2 py-2">Kriteria Nasabah</th>
                                    <th class="px-2 py-2">Kriteria Prospek</th>
                                    <th class="px-2 py-2">Jenis Prospek</th>
                                    <th class="px-2 py-2">Tgl Kunjungan</th>
                                    <th class="px-2 py-2">Status Prospek</th>
                                    <th class="px-2 py-2">Nama Penginput</th>
                                    <th class="px-2 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="small">
                                <?php
                                // Konfigurasi pagination
                                $limit = 5;
                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $limit;

                                // Tambahkan kondisi pencarian
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                $where = array();
                                if (!empty($search)) {
                                    $where[] = "(no_ktp LIKE '%$search%' OR nama_nasabah LIKE '%$search%')";
                                }

                                if (isset($_GET['bulan']) && !empty($_GET['bulan'])) {
                                    $bulan = mysqli_real_escape_string($connect, $_GET['bulan']);
                                    $where[] = "MONTH(tgl_kunjungan) = '$bulan'";
                                }

                                if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
                                    $tahun = mysqli_real_escape_string($connect, $_GET['tahun']);
                                    $where[] = "YEAR(tgl_kunjungan) = '$tahun'";
                                }

                                if (isset($_GET['penginput']) && !empty($_GET['penginput'])) {
                                    $penginput = mysqli_real_escape_string($connect, $_GET['penginput']);
                                    $where[] = "nama_penginput = '$penginput'";
                                }

                                $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

                                // Query dengan filter
                                $query = mysqli_query($connect, "SELECT * FROM tb_nasabah_prosfecting $where_clause ORDER BY id DESC LIMIT $start, $limit");

                                // Query untuk total data dengan filter
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM tb_nasabah_prosfecting $where_clause"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    echo "<tr class='text-nowrap'>
                                        <td class='px-2 py-1'>" . $no++ . "</td>
                                        <td class='px-2 py-1'>{$row['no_ktp']}</td>
                                        <td class='px-2 py-1'>{$row['nama_nasabah']}</td>
                                        <td class='px-2 py-1'>{$row['jenis_kelamin']}</td>
                                        <td class='px-2 py-1'>{$row['usia']}</td>
                                        <td class='px-2 py-1'>{$row['alamat_nasabah']}, {$row['desa']}, {$row['kecamatan']}, {$row['kabupaten']}</td>
                                        <td class='px-2 py-1'>{$row['kriteria_nasabah']}</td>
                                        <td class='px-2 py-1'>{$row['kriteria_prospek']}</td>
                                        <td class='px-2 py-1'>{$row['jenis_prospek']}</td>
                                        <td class='px-2 py-1'>" . date('d-m-Y', strtotime($row['tgl_kunjungan'])) . "</td>
                                        <td class='px-2 py-1'>{$row['status_prospek']}</td>
                                        <td class='px-2 py-1'>{$row['nama_penginput']}</td>
                                        <td class='px-2 py-1'>";

                                    // Tampilkan tombol hapus hanya jika user adalah ADMIN atau penginput data tersebut
                                    if ($_SESSION['key_app'] == 'ADMIN' || $_SESSION['nama'] == $row['nama_penginput']) {
                                        echo "<button type='button' class='btn btn-sm btn-danger py-0 px-1' onclick='deleteNasabah(\"{$row['id']}\")'>
                                                    <i class='bi bi-trash'></i>
                                                  </button>";
                                    }
                                    echo "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>"
                                        tabindex="-1">Previous</a>
                                </li>

                                <?php
                                $start_number = max(1, min($page - 4, $total_pages - 9));
                                $end_number = min($total_pages, $start_number + 9);

                                if ($start_number > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search) ? '&search=' . $search : '') . '">1</a></li>';
                                    if ($start_number > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                }

                                for ($i = $start_number; $i <= $end_number; $i++) {
                                    $filter_params = '';
                                    if (!empty($_GET['bulan'])) {
                                        $filter_params .= '&bulan=' . $_GET['bulan'];
                                    }
                                    if (!empty($_GET['tahun'])) {
                                        $filter_params .= '&tahun=' . $_GET['tahun'];
                                    }
                                    if (!empty($_GET['penginput'])) {
                                        $filter_params .= '&penginput=' . $_GET['penginput'];
                                    }
                                    if (!empty($_GET['search'])) {
                                        $filter_params .= '&search=' . $_GET['search'];
                                    }
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . $filter_params . '">' . $i . '</a>
                                          </li>';
                                }

                                if ($end_number < $total_pages) {
                                    if ($end_number < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($search) ? '&search=' . $search : '') . '">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="card mb-4 mt-3">
                <div class="card-body">
                    <h5 class="card-title mb-4" style="background-color:rgb(155, 255, 208); padding: 12px; border-radius: 6px;">Form Tambah Data Prospekting</h5>
                    <form method="POST" action="">
                        <!-- Row 1 -->
                        <div class="row mb-3">
                            <!-- Column 1 -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No. KTP</label>
                                    <input type="text" class="form-control" id="no_ktp" name="no_ktp" maxlength="16"
                                        placeholder="Masukkan 16 digit No KTP" required>
                                    <div class="form-text">Masukkan 16 digit nomor KTP</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Nasabah</label>
                                    <input type="text" class="form-control" name="nama_nasabah" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usia</label>
                                    <input type="number" class="form-control" name="usia" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="alamat_nasabah" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Desa</label>
                                    <input type="text" class="form-control" name="desa" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" class="form-control" name="kecamatan" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kabupaten</label>
                                    <input type="text" class="form-control" name="kabupaten" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kriteria Nasabah</label>
                                    <select class="form-select" name="kriteria_nasabah" required>
                                        <option value="">Pilih Kriteria Nasabah</option>
                                        <option value="Nasabah Baru">Nasabah Baru</option>
                                        <option value="Nasabah Exisiting">Nasabah Exisiting</option>
                                        <option value="Nasabah Pasif">Nasabah Pasif</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kriteria Prospek</label>
                                    <select class="form-select" name="kriteria_prospek" required>
                                        <option value="">Pilih Kriteria Prospek</option>
                                        <option value="Cold">Cold</option>
                                        <option value="Warm">Warm</option>
                                        <option value="Hot">Hot</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Column 2 -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Prospek</label>
                                    <select class="form-select" name="jenis_prospek" required>
                                        <option value="">Pilih Jenis Prospek</option>
                                        <option value="Kredit">Kredit</option>
                                        <option value="Tabungan">Tabungan</option>
                                        <option value="Deposito">Deposito</option>
                                        <option value="Agen PPOB">Agen PPOB</option>
                                        <option value="PPOB">PPOB</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Kunjungan</label>
                                    <input type="date" class="form-control" name="tgl_kunjungan" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Hasil Kunjungan</label>
                                    <textarea class="form-control" name="hasil_kunjungan" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Respon Kunjungan</label>
                                    <select class="form-select" name="respon_kunjungan" required>
                                        <option value="">Pilih Respon Kunjungan</option>
                                        <option value="Kurang Tertarik">Kurang Tertarik</option>
                                        <option value="Tidak Tertarik">Tidak Tertarik</option>
                                        <option value="Tertarik">Tertarik</option>
                                        <option value="Sangat Tertarik">Sangat Tertarik</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status Prospek</label>
                                    <select class="form-select" name="status_prospek" required>
                                        <option value="">Pilih Status Prospek</option>
                                        <option value="Closed">Closed</option>
                                        <option value="Deal">Deal</option>
                                        <option value="On Progres">On Progres</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan Nasabah</label>
                                    <textarea class="form-control" name="ket_nasabah"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. HP</label>
                                    <input type="text" class="form-control" name="no_hp">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" class="form-control" name="email">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Facebook</label>
                                    <input type="text" class="form-control" name="facebook">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Instagram</label>
                                    <input type="text" class="form-control" name="instagram">
                                </div>
                            </div>
                        </div>
                        <!-- Row 2 -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Usaha</label>
                                    <select class="form-select" name="jenis_usaha" required>
                                        <option value="">Pilih Jenis Usaha</option>
                                        <option value="Pertanian">Pertanian</option>
                                        <option value="Perindustrian">Perindustrian</option>
                                        <option value="Perdagangan">Perdagangan</option>
                                        <option value="Jasa-jasa">Jasa-jasa</option>
                                        <option value="Lain-lain">Lain-lain</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Perusahaan</label>
                                    <input type="text" class="form-control" name="nama_perusahaan">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Detail Usaha</label>
                                    <textarea class="form-control" name="detail_usaha"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mitra</label>
                                    <input type="text" class="form-control" name="mitra">
                                </div>
                                <!-- Sembunyikan field kas dan set nilai default -->
                                <input type="hidden" name="kas" value="-">
                                <div class="mb-3">
                                    <label class="form-label">Kode AO</label>
                                    <input type="text" class="form-control" name="kd_ao"
                                        value="<?php echo isset($_SESSION['kd_ao']) ? $_SESSION['kd_ao'] : ''; ?>"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
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

        // Ganti fungsi pencarian dengan tombol
        document.getElementById('searchButton').addEventListener('click', function() {
            updateFilter();
        });

        // Tambahkan event listener untuk tombol Enter pada input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                updateFilter();
            }
        });

        // Isi nilai pencarian dari parameter URL
        document.addEventListener('DOMContentLoaded', function() {
            let urlParams = new URLSearchParams(window.location.search);
            let searchValue = urlParams.get('search');
            if (searchValue) {
                document.getElementById('searchInput').value = searchValue;
            }
        });

        // Tambahkan event listener untuk filter
        document.getElementById('filterBulan').addEventListener('change', updateFilter);
        document.getElementById('filterTahun').addEventListener('change', updateFilter);
        document.getElementById('filterPenginput').addEventListener('change', updateFilter);

        function updateFilter() {
            const bulan = document.getElementById('filterBulan').value;
            const tahun = document.getElementById('filterTahun').value;
            const penginput = document.getElementById('filterPenginput').value;
            const search = document.getElementById('searchInput').value;

            let url = 'prospekting-nasabah.php?';
            let params = [];

            if (bulan) params.push('bulan=' + bulan);
            if (tahun) params.push('tahun=' + tahun);
            if (penginput) params.push('penginput=' + encodeURIComponent(penginput));
            if (search) params.push('search=' + encodeURIComponent(search));

            window.location.href = url + params.join('&');
        }

        // Isi nilai filter dari parameter URL saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);

            const bulan = urlParams.get('bulan');
            if (bulan) document.getElementById('filterBulan').value = bulan;

            const tahun = urlParams.get('tahun');
            if (tahun) document.getElementById('filterTahun').value = tahun;

            const penginput = urlParams.get('penginput');
            if (penginput) document.getElementById('filterPenginput').value = penginput;
        });

        // Fungsi hapus nasabah
        function deleteNasabah(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data prospekting nasabah akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `prospekting-nasabah.php?action=delete&id=${id}`;
                }
            });
        }

        function exportExcel() {
            const bulan = document.getElementById('filterBulan').value;
            const tahun = document.getElementById('filterTahun').value;
            const penginput = document.getElementById('filterPenginput').value;
            const search = document.getElementById('searchInput').value;

            let url = 'export-excel-prospekting.php?';
            let params = [];

            if (bulan) params.push('bulan=' + encodeURIComponent(bulan));
            if (tahun) params.push('tahun=' + encodeURIComponent(tahun));
            if (penginput) params.push('penginput=' + encodeURIComponent(penginput));
            if (search) params.push('search=' + encodeURIComponent(search));

            // Menggunakan filter yang sedang aktif dari URL jika ada
            const urlParams = new URLSearchParams(window.location.search);
            if (!bulan && urlParams.has('bulan')) params.push('bulan=' + urlParams.get('bulan'));
            if (!tahun && urlParams.has('tahun')) params.push('tahun=' + urlParams.get('tahun'));
            if (!penginput && urlParams.has('penginput')) params.push('penginput=' + urlParams.get('penginput'));
            if (!search && urlParams.has('search')) params.push('search=' + urlParams.get('search'));

            window.location.href = url + params.join('&');
        }

        function exportPDF() {
            const bulan = document.getElementById('filterBulan').value;
            const tahun = document.getElementById('filterTahun').value;
            const penginput = document.getElementById('filterPenginput').value;
            const search = document.getElementById('searchInput').value;

            let url = 'export-pdf-prospekting.php?';
            let params = [];

            if (bulan) params.push('bulan=' + encodeURIComponent(bulan));
            if (tahun) params.push('tahun=' + encodeURIComponent(tahun));
            if (penginput) params.push('penginput=' + encodeURIComponent(penginput));
            if (search) params.push('search=' + encodeURIComponent(search));

            // Menggunakan filter yang sedang aktif dari URL jika ada
            const urlParams = new URLSearchParams(window.location.search);
            if (!bulan && urlParams.has('bulan')) params.push('bulan=' + urlParams.get('bulan'));
            if (!tahun && urlParams.has('tahun')) params.push('tahun=' + urlParams.get('tahun'));
            if (!penginput && urlParams.has('penginput')) params.push('penginput=' + urlParams.get('penginput'));
            if (!search && urlParams.has('search')) params.push('search=' + urlParams.get('search'));

            window.location.href = url + params.join('&');
        }
    </script>
</body>

</html>