<?php
ob_start();
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
        $_SESSION['kantor'] = $data['kantor'];

        ob_end_clean();
        header("Location: ../dashboard.php");
        exit();
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}

// Proses form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['login'])) {
    $nomor_loan = mysqli_real_escape_string($connect, $_POST['nomor_loan']);
    $nama_nasabah = mysqli_real_escape_string($connect, $_POST['nama_nasabah']);

    // Pastikan format tanggal adalah yyyy-mm-dd
    $tanggal_kunjungan = mysqli_real_escape_string($connect, $_POST['tanggal_kunjungan']);
    $kunjungan_selanjutnya = mysqli_real_escape_string($connect, $_POST['kunjungan_selanjutnya']);
    $tgl_drop = !empty($_POST['tgl_drop']) ?
        mysqli_real_escape_string($connect, $_POST['tgl_drop']) :
        NULL;

    // Validasi format tanggal
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $tanggal_kunjungan)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Format Tanggal Salah',
                text: 'Format tanggal kunjungan harus yyyy-mm-dd'
            });
        </script>";
        exit;
    }

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $kunjungan_selanjutnya)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Format Tanggal Salah',
                text: 'Format tanggal kunjungan selanjutnya harus yyyy-mm-dd'
            });
        </script>";
        exit;
    }

    // Validasi format tanggal dropping hanya jika ada nilainya
    if (!is_null($tgl_drop) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $tgl_drop)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Format Tanggal Salah',
                text: 'Format tanggal dropping harus yyyy-mm-dd'
            });
        </script>";
        exit;
    }

    $komitmen_nasabah = mysqli_real_escape_string($connect, $_POST['komitmen_nasabah']);

    // Hapus koma dari nilai nominal
    $baki_debat = str_replace(',', '', mysqli_real_escape_string($connect, $_POST['baki_debat']));
    $tunggakan_pokok = str_replace(',', '', mysqli_real_escape_string($connect, $_POST['tunggakan_pokok']));
    $tunggakan_bunga = str_replace(',', '', mysqli_real_escape_string($connect, $_POST['tunggakan_bunga']));
    $plafond = str_replace(',', '', mysqli_real_escape_string($connect, $_POST['plafond']));

    $kolektabilitas = mysqli_real_escape_string($connect, $_POST['kolektabilitas']);
    $keterangan = mysqli_real_escape_string($connect, $_POST['keterangan']);
    $alamat = mysqli_real_escape_string($connect, $_POST['alamat']);
    $respon = mysqli_real_escape_string($connect, $_POST['respon']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $nomor_telepon = mysqli_real_escape_string($connect, $_POST['nomor_telepon']);
    $nama_penginput = $_SESSION['nama']; // Mengambil nama dari session login
    $tgl_input = date('Y-m-d'); // Format yyyy-mm-dd
    $kriteria_monitoring = mysqli_real_escape_string($connect, $_POST['kriteria_monitoring']);
    $penggunaan_proposal = mysqli_real_escape_string($connect, $_POST['penggunaan_proposal']);
    $realisasi_penggunaan = mysqli_real_escape_string($connect, $_POST['realisasi_penggunaan']);
    $jenis_jaminan = mysqli_real_escape_string($connect, $_POST['jenis_jaminan']);
    $kepemilikan_jaminan = mysqli_real_escape_string($connect, $_POST['kepemilikan_jaminan']);
    $kesimpulan_pascadroping = mysqli_real_escape_string($connect, $_POST['kesimpulan_pascadroping']);
    $kd_kantor = mysqli_real_escape_string($connect, $_POST['kd_kantor']);

    $query = "INSERT INTO tb_nasabah (nomor_loan, nama_nasabah, tanggal_kunjungan, kunjungan_selanjutnya, komitmen_nasabah, 
              baki_debat, tunggakan_pokok, tunggakan_bunga, kolektabilitas, keterangan, alamat, respon, status, 
              nomor_telepon, nama_penginput, tgl_input, kriteria_monitoring, tgl_drop, plafond, penggunaan_proposal, 
              realisasi_penggunaan, jenis_jaminan, kepemilikan_jaminan, kesimpulan_pascadroping, kd_kantor) 
              VALUES ('$nomor_loan', '$nama_nasabah', '$tanggal_kunjungan', '$kunjungan_selanjutnya', '$komitmen_nasabah', 
              '$baki_debat', '$tunggakan_pokok', '$tunggakan_bunga', '$kolektabilitas', '$keterangan', '$alamat', '$respon', 
              '$status', '$nomor_telepon', '$nama_penginput', '$tgl_input', '$kriteria_monitoring', " .
        (is_null($tgl_drop) ? "NULL" : "'$tgl_drop'") . ", '$plafond', 
              '$penggunaan_proposal', '$realisasi_penggunaan', '$jenis_jaminan', '$kepemilikan_jaminan', 
              '$kesimpulan_pascadroping', '$kd_kantor')";

    if (mysqli_query($connect, $query)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data kunjungan nasabah berhasil disimpan!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='kunjungan-nasabah.php';
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
    $query_check = mysqli_query($connect, "SELECT nama_penginput FROM tb_nasabah WHERE id = '$id'");

    if (mysqli_num_rows($query_check) > 0) {
        $data_check = mysqli_fetch_array($query_check);

        if ($_SESSION['key_app'] == 'ADMIN' || $_SESSION['nama'] == $data_check['nama_penginput']) {
            $query_delete = mysqli_query($connect, "DELETE FROM tb_nasabah WHERE id = '$id'");

            if ($query_delete) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data kunjungan nasabah berhasil dihapus!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.location.href='kunjungan-nasabah.php';
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
    <title>Kunjungan Nasabah</title>
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
            <h2 class="mb-4">Kunjungan Nasabah</h2>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Kunjungan Nasabah</h5>
                        <div class="col-md-8 d-flex gap-2">
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

                                $query_tahun = mysqli_query($connect, "SELECT DISTINCT YEAR(tanggal_kunjungan) as tahun 
                                                                     FROM tb_nasabah 
                                                                     WHERE tanggal_kunjungan IS NOT NULL 
                                                                     AND YEAR(tanggal_kunjungan) >= $start_year
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
                                $query_penginput = mysqli_query($connect, "SELECT DISTINCT nama 
                                                                         FROM account 
                                                                         WHERE status = 'AKTIF'
                                                                         ORDER BY nama ASC");
                                while ($row_penginput = mysqli_fetch_array($query_penginput)) {
                                    $selected = (isset($_GET['penginput']) && $_GET['penginput'] == $row_penginput['nama']) ? 'selected' : '';
                                    echo "<option value='{$row_penginput['nama']}' $selected>{$row_penginput['nama']}</option>";
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
                                    <th class="px-2 py-2">Nomor Loan</th>
                                    <th class="px-2 py-2">Nama Nasabah</th>
                                    <th class="px-2 py-2">Tgl Kunjungan</th>
                                    <th class="px-2 py-2">Kunjungan Selanjutnya</th>
                                    <th class="px-2 py-2">Baki Debet</th>
                                    <th class="px-2 py-2">Tunggakan Pokok</th>
                                    <th class="px-2 py-2">Tunggakan Bunga</th>
                                    <th class="px-2 py-2">Plafond</th>
                                    <th class="px-2 py-2">Nama Penginput</th>
                                    <th class="px-2 py-2">Tgl Input</th>
                                    <th class="px-2 py-2">Status</th>
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
                                    $where[] = "(nomor_loan LIKE '%$search%' OR nama_nasabah LIKE '%$search%')";
                                }

                                if (isset($_GET['bulan']) && !empty($_GET['bulan'])) {
                                    $bulan = mysqli_real_escape_string($connect, $_GET['bulan']);
                                    $where[] = "MONTH(tanggal_kunjungan) = '$bulan'";
                                }

                                if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
                                    $tahun = mysqli_real_escape_string($connect, $_GET['tahun']);
                                    $where[] = "YEAR(tanggal_kunjungan) = '$tahun'";
                                }

                                if (isset($_GET['penginput']) && !empty($_GET['penginput'])) {
                                    $penginput = mysqli_real_escape_string($connect, $_GET['penginput']);
                                    $where[] = "nama_penginput = '$penginput'";
                                }

                                $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

                                // Query dengan filter
                                $query = mysqli_query($connect, "SELECT * FROM tb_nasabah $where_clause ORDER BY id DESC LIMIT $start, $limit");

                                // Query untuk total data dengan filter
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM tb_nasabah $where_clause"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    // Format angka ke format rupiah dengan pengecekan null dan tipe data
                                    $baki_debat = (!empty($row['baki_debat']) && is_numeric($row['baki_debat'])) ?
                                        number_format((float)$row['baki_debat'], 0, ',', '.') : '0';

                                    $tunggakan_pokok = (!empty($row['tunggakan_pokok']) && is_numeric($row['tunggakan_pokok'])) ?
                                        number_format((float)$row['tunggakan_pokok'], 0, ',', '.') : '0';

                                    $tunggakan_bunga = (!empty($row['tunggakan_bunga']) && is_numeric($row['tunggakan_bunga'])) ?
                                        number_format((float)$row['tunggakan_bunga'], 0, ',', '.') : '0';

                                    $plafond = (!empty($row['plafond']) && is_numeric($row['plafond'])) ?
                                        number_format((float)$row['plafond'], 0, ',', '.') : '0';

                                    echo "<tr class='text-nowrap'>
                                        <td class='px-2 py-1'>$no</td>
                                        <td class='px-2 py-1'>{$row['nomor_loan']}</td>
                                        <td class='px-2 py-1'>{$row['nama_nasabah']}</td>
                                        <td class='px-2 py-1'>" . date('d-m-Y', strtotime($row['tanggal_kunjungan'])) . "</td>
                                        <td class='px-2 py-1'>" . date('d-m-Y', strtotime($row['kunjungan_selanjutnya'])) . "</td>
                                        <td class='px-2 py-1'>Rp {$baki_debat}</td>
                                        <td class='px-2 py-1'>Rp {$tunggakan_pokok}</td>
                                        <td class='px-2 py-1'>Rp {$tunggakan_bunga}</td>
                                        <td class='px-2 py-1'>Rp {$plafond}</td>
                                        <td class='px-2 py-1'>{$row['nama_penginput']}</td>
                                        <td class='px-2 py-1'>" . date('d-m-Y', strtotime($row['tgl_input'])) . "</td>
                                        <td class='px-2 py-1'>{$row['status']}</td>
                                        <td class='px-2 py-1'>";
                                    // Tampilkan tombol hapus hanya jika user adalah ADMIN atau penginput data tersebut
                                    if ($_SESSION['key_app'] == 'ADMIN' || $_SESSION['nama'] == $row['nama_penginput']) {
                                        echo "<button type='button' class='btn btn-sm btn-danger py-0 px-1' onclick='deleteNasabah(\"{$row['id']}\")'>
                                                    <i class='bi bi-trash'></i>
                                                </button>";
                                    }
                                    echo "</td>
                                    </tr>";
                                    $no++;
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
                    <h5 class="card-title mb-4" style="background-color:rgb(155, 255, 208); padding: 12px; border-radius: 6px;">Form Tambah Data Kunjungan</h5>
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Loan</label>
                                    <input type="text" class="form-control" name="nomor_loan" id="nomor_loan" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Nasabah</label>
                                    <input type="text" class="form-control" name="nama_nasabah" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Kunjungan</label>
                                    <input type="date" class="form-control" name="tanggal_kunjungan"
                                        value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kunjungan Selanjutnya</label>
                                    <input type="date" class="form-control" name="kunjungan_selanjutnya" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Komitmen Nasabah</label>
                                    <textarea class="form-control" name="komitmen_nasabah" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Baki Debet</label>
                                    <input type="text" class="form-control" name="baki_debat" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tunggakan Pokok</label>
                                    <input type="text" class="form-control" name="tunggakan_pokok" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tunggakan Bunga</label>
                                    <input type="text" class="form-control" name="tunggakan_bunga" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kolektabilitas</label>
                                    <select class="form-select" name="kolektabilitas" required>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="E">E</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <select class="form-select" name="keterangan" required>
                                        <option value="RES">RES</option>
                                        <option value="NON RES">NON RES</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="alamat" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Respon</label>
                                    <select class="form-select" name="respon" required>
                                        <option value="">Pilih Respon</option>
                                        <?php
                                        $query_respon = mysqli_query($connect, "SELECT * FROM tb_master_respon_histori_nasabah ORDER BY respon ASC");
                                        while ($row_respon = mysqli_fetch_array($query_respon)) {
                                            echo "<option value='{$row_respon['respon']}'>{$row_respon['respon']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="NAIK">NAIK</option>
                                        <option value="TURUN">TURUN</option>
                                        <option value="TETAP">TETAP</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" name="nomor_telepon" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Penginput</label>
                                    <input type="text" class="form-control" value="<?php echo $_SESSION['nama']; ?>"
                                        readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kriteria Monitoring</label>
                                    <select class="form-select" name="kriteria_monitoring" required>
                                        <option value="h-3">h-3</option>
                                        <option value="h+1">h+1</option>
                                        <option value="h+3">h+3</option>
                                        <option value="h+6">h+6</option>
                                        <option value="h+7">h+7</option>
                                        <option value="h+30">h+30</option>
                                        <option value="kunjungan">kunjungan</option>
                                        <option value="Pasca Droping">Pasca Droping</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Dropping</label>
                                    <input type="date" class="form-control" name="tgl_drop">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Plafond</label>
                                    <input type="text" class="form-control" name="plafond" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Penggunaan Proposal</label>
                                    <input type="text" class="form-control" name="penggunaan_proposal" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Realisasi Penggunaan</label>
                                    <input type="text" class="form-control" name="realisasi_penggunaan" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Jaminan</label>
                                    <select class="form-select" name="jenis_jaminan" required>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Tidak Sesuai">Tidak Sesuai</option>
                                        <option value="Belum Terverifikasi">Belum Terverifikasi</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kepemilikan Jaminan</label>
                                    <select class="form-select" name="kepemilikan_jaminan" required>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Tidak Sesuai">Tidak Sesuai</option>
                                        <option value="Belum Terverifikasi">Belum Terverifikasi</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kesimpulan Pasca Dropping</label>
                                    <select class="form-select" name="kesimpulan_pascadroping" required>
                                        <option value="Sesuai">Sesuai</option>
                                        <option value="Tidak Sesuai">Tidak Sesuai</option>
                                        <option value="Belum Terverifikasi">Belum Terverifikasi</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kode Kantor</label>
                                    <input type="text" class="form-control" name="kd_kantor"
                                        value="<?php echo $_SESSION['kantor']; ?>" readonly>
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

        // Format input nominal menjadi format rupiah
        function formatRupiah(angka, prefix) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
        }

        // Terapkan format rupiah pada input nominal
        document.addEventListener('DOMContentLoaded', function() {
            const rupiahInputs = [
                'baki_debat',
                'tunggakan_pokok',
                'tunggakan_bunga',
                'plafond'
            ];

            rupiahInputs.forEach(function(id) {
                const input = document.querySelector(`input[name="${id}"]`);

                input.addEventListener('keyup', function(e) {
                    input.value = formatRupiah(this.value, 'Rp ');
                });

                // Format nilai awal jika ada
                if (input.value) {
                    input.value = formatRupiah(input.value, 'Rp ');
                }
            });
        });

        // Pada saat form di-submit, hapus format rupiah
        document.querySelector('form').addEventListener('submit', function(e) {
            const rupiahInputs = [
                'baki_debat',
                'tunggakan_pokok',
                'tunggakan_bunga',
                'plafond'
            ];

            rupiahInputs.forEach(function(id) {
                const input = document.querySelector(`input[name="${id}"]`);
                // Hapus "Rp " dan titik, simpan hanya angka
                input.value = input.value.replace(/Rp /g, '').replace(/\./g, '');
            });
        });

        // Tambahkan fungsi untuk cek Nomor Loan real-time dan auto-fill
        document.getElementById('nomor_loan').addEventListener('input', function() {
            let nomor_loan = this.value;
            if (nomor_loan.length > 0) {
                fetch('check-loan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'nomor_loan=' + encodeURIComponent(nomor_loan) + '&action=check'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.found) {
                            // Auto-fill form fields dengan data dari database
                            document.querySelector('input[name="nama_nasabah"]').value = data.nasabah
                                .nama_nasabah || '';
                            document.querySelector('textarea[name="alamat"]').value = data.nasabah.alamat || '';
                            document.querySelector('input[name="nomor_telepon"]').value = data.nasabah
                                .nomor_telepon || '';
                            document.querySelector('input[name="tgl_drop"]').value = data.nasabah.tgl_drop ||
                                '';

                            // Format nilai nominal dengan Rupiah
                            const baki_debat = data.nasabah.baki_debat || '';
                            const tunggakan_pokok = data.nasabah.tunggakan_pokok || '';
                            const tunggakan_bunga = data.nasabah.tunggakan_bunga || '';
                            const plafond = data.nasabah.plafond || '';

                            document.querySelector('input[name="baki_debat"]').value = formatRupiah(baki_debat,
                                'Rp ');
                            document.querySelector('input[name="tunggakan_pokok"]').value = formatRupiah(
                                tunggakan_pokok, 'Rp ');
                            document.querySelector('input[name="tunggakan_bunga"]').value = formatRupiah(
                                tunggakan_bunga, 'Rp ');
                            document.querySelector('input[name="plafond"]').value = formatRupiah(plafond,
                                'Rp ');

                            document.querySelector('input[name="penggunaan_proposal"]').value = data.nasabah
                                .penggunaan_proposal || '';
                            document.querySelector('input[name="realisasi_penggunaan"]').value = data.nasabah
                                .realisasi_penggunaan || '';

                            // Set select elements
                            if (data.nasabah.jenis_jaminan) {
                                document.querySelector('select[name="jenis_jaminan"]').value = data.nasabah
                                    .jenis_jaminan;
                            }
                            if (data.nasabah.kepemilikan_jaminan) {
                                document.querySelector('select[name="kepemilikan_jaminan"]').value = data
                                    .nasabah.kepemilikan_jaminan;
                            }
                            if (data.nasabah.kolektabilitas) {
                                document.querySelector('select[name="kolektabilitas"]').value = data.nasabah
                                    .kolektabilitas;
                            }
                            if (data.nasabah.status) {
                                document.querySelector('select[name="status"]').value = data.nasabah.status;
                            }
                            if (data.nasabah.kriteria_monitoring) {
                                document.querySelector('select[name="kriteria_monitoring"]').value = data
                                    .nasabah.kriteria_monitoring;
                            }
                            if (data.nasabah.kesimpulan_pascadroping) {
                                document.querySelector('select[name="kesimpulan_pascadroping"]').value = data
                                    .nasabah.kesimpulan_pascadroping;
                            }
                            if (data.nasabah.keterangan) {
                                document.querySelector('select[name="keterangan"]').value = data.nasabah
                                    .keterangan;
                            }
                        }
                    });
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

        // Fungsi hapus nasabah
        function deleteNasabah(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data kunjungan nasabah akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `kunjungan-nasabah.php?action=delete&id=${id}`;
                }
            });
        }

        // Fungsi edit nasabah
        function editNasabah(id) {
            window.location.href = `edit-kunjungan.php?id=${id}`;
        }

        // Tambahkan script JavaScript untuk filter
        document.getElementById('filterBulan').addEventListener('change', function() {
            updateFilter();
        });

        document.getElementById('filterTahun').addEventListener('change', function() {
            updateFilter();
        });

        document.getElementById('filterPenginput').addEventListener('change', function() {
            updateFilter();
        });

        function updateFilter() {
            let currentUrl = new URL(window.location.href);
            let bulan = document.getElementById('filterBulan').value;
            let tahun = document.getElementById('filterTahun').value;
            let penginput = document.getElementById('filterPenginput').value;
            let search = document.getElementById('searchInput').value;

            // Reset page ke 1 ketika filter berubah
            currentUrl.searchParams.set('page', '1');

            if (bulan) {
                currentUrl.searchParams.set('bulan', bulan);
            } else {
                currentUrl.searchParams.delete('bulan');
            }

            if (tahun) {
                currentUrl.searchParams.set('tahun', tahun);
            } else {
                currentUrl.searchParams.delete('tahun');
            }

            if (penginput) {
                currentUrl.searchParams.set('penginput', penginput);
            } else {
                currentUrl.searchParams.delete('penginput');
            }

            if (search) {
                currentUrl.searchParams.set('search', search);
            } else {
                currentUrl.searchParams.delete('search');
            }

            window.location.href = currentUrl.toString();
        }

        // Tambahkan di bagian script
        function exportExcel() {
            let currentUrl = new URL(window.location.href);
            let bulan = document.getElementById('filterBulan').value;
            let tahun = document.getElementById('filterTahun').value;
            let penginput = document.getElementById('filterPenginput').value;

            let exportUrl = 'export-excel-kunjungan.php';
            let params = new URLSearchParams();

            if (bulan) params.append('bulan', bulan);
            if (tahun) params.append('tahun', tahun);
            if (penginput) params.append('penginput', penginput);

            if (params.toString()) {
                exportUrl += '?' + params.toString();
            }

            window.location.href = exportUrl;
        }

        // Fungsi untuk export PDF
        function exportPDF() {
            let currentUrl = new URL(window.location.href);
            let bulan = document.getElementById('filterBulan').value;
            let tahun = document.getElementById('filterTahun').value;
            let penginput = document.getElementById('filterPenginput').value;

            let exportUrl = 'export-pdf-kunjungan.php';
            let params = new URLSearchParams();

            if (bulan) params.append('bulan', bulan);
            if (tahun) params.append('tahun', tahun);
            if (penginput) params.append('penginput', penginput);

            if (params.toString()) {
                exportUrl += '?' + params.toString();
            }

            window.location.href = exportUrl;
        }
    </script>
</body>

</html>