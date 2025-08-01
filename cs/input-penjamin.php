<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Tambahkan pengecekan untuk request AJAX
if (isset($_GET['action']) && $_GET['action'] == 'get_penjamin') {
    header('Content-Type: application/json');

    if (isset($_GET['niknas'])) {
        $niknas = mysqli_real_escape_string($connect, $_GET['niknas']);
        // Ubah query untuk mengambil semua data penjamin dengan niknas yang sama
        $query = mysqli_query($connect, "SELECT * FROM penjamin WHERE niknas='$niknas' ORDER BY nikpenj DESC");

        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }

        if (count($data) > 0) {
            echo json_encode([
                'status' => 'success',
                'penjamin' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 'empty',
                'message' => 'Data tidak ditemukan'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Parameter niknas tidak ditemukan'
        ]);
    }
    exit();
}

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

// Handler untuk delete penjamin
if (isset($_POST['delete_penjamin']) && isset($_POST['nikpenj'])) {
    $nikpenj = mysqli_real_escape_string($connect, $_POST['nikpenj']);

    $query = "DELETE FROM penjamin WHERE nikpenj='$nikpenj'";

    mysqli_begin_transaction($connect);

    try {
        if (mysqli_query($connect, $query)) {
            mysqli_commit($connect);
            echo json_encode([
                'status' => 'success',
                'message' => 'Data penjamin berhasil dihapus!'
            ]);
        } else {
            throw new Exception("Gagal menghapus data");
        }
    } catch (Exception $e) {
        mysqli_rollback($connect);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus data penjamin: ' . $e->getMessage()
        ]);
    }
    exit();
}

// Handler untuk input/update penjamin
if (isset($_POST['nikpenj']) && !isset($_POST['delete_penjamin'])) {
    // Ambil data dari form
    $nikpenj = mysqli_real_escape_string($connect, $_POST['nikpenj']);
    $niknas = mysqli_real_escape_string($connect, $_POST['niknas']);
    $hub = mysqli_real_escape_string($connect, $_POST['hub']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $tmpt = mysqli_real_escape_string($connect, $_POST['tmpt']);
    $tgl = mysqli_real_escape_string($connect, $_POST['tgl']);
    $almt = mysqli_real_escape_string($connect, $_POST['almt']);
    $kec = mysqli_real_escape_string($connect, $_POST['kec']);
    $kab = mysqli_real_escape_string($connect, $_POST['kab']);
    $usia = mysqli_real_escape_string($connect, $_POST['usia']);
    $pek = mysqli_real_escape_string($connect, $_POST['pek']);

    // Proses pekerjaan - jika memilih Lainnya, gunakan input manual
    if ($pek == 'Lainnya') {
        if (isset($_POST['pek_lainnya']) && !empty($_POST['pek_lainnya'])) {
            $pek_lainnya = mysqli_real_escape_string($connect, $_POST['pek_lainnya']);

            // Validasi format input (hanya huruf, spasi, titik, dan koma)
            if (!preg_match('/^[A-Za-z\s\.\,]+$/', $pek_lainnya)) {
                $_SESSION['status'] = "error";
                $_SESSION['message'] = "Pekerjaan hanya boleh berisi huruf, spasi, titik, dan koma";
                header("Location: input-penjamin.php");
                exit();
            }

            $pek = $pek_lainnya;
        } else {
            // Jika memilih Lainnya tapi tidak mengisi input, kembalikan error
            $_SESSION['status'] = "error";
            $_SESSION['message'] = "Harap isi pekerjaan Anda jika memilih Lainnya";
            header("Location: input-penjamin.php");
            exit();
        }
    }

    $tlfn1 = mysqli_real_escape_string($connect, $_POST['tlfn1']);
    $tlfn2 = mysqli_real_escape_string($connect, $_POST['tlfn2']);
    $suis = mysqli_real_escape_string($connect, isset($_POST['suis']) ? $_POST['suis'] : 'Tidak Ada');
    $nik_suis = mysqli_real_escape_string($connect, isset($_POST['nik_suis']) ? $_POST['nik_suis'] : '');
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    // Debug: Log semua data yang diterima
    error_log("Penjamin POST Data: " . json_encode($_POST));
    error_log("Penjamin Processed Data: nikpenj=$nikpenj, niknas=$niknas, nama=$nama, pek=$pek");

    // Validasi nomor telepon
    $tlfn1 = empty($tlfn1) ? '+62' : $tlfn1;
    $tlfn2 = empty($tlfn2) ? '+62' : $tlfn2;

    mysqli_begin_transaction($connect);

    try {
        // Cek apakah ini update atau insert baru
        if (isset($_POST['update_penjamin'])) {
            // Debug: Log data yang akan diupdate
            error_log("UPDATE Penjamin Data: nikpenj=$nikpenj, niknas=$niknas, nama=$nama");

            // Update data yang ada - tidak perlu cek duplikasi karena NIK tidak berubah
            $query = "UPDATE penjamin SET 
                        niknas='$niknas', hub='$hub', nama='$nama', tmpt='$tmpt', 
                        tgl='$tgl', almt='$almt', kec='$kec', kab='$kab', 
                        usia='$usia', pek='$pek', tlfn1='$tlfn1', tlfn2='$tlfn2', 
                        suis='$suis', nik_suis='$nik_suis', status='$status'
                     WHERE nikpenj='$nikpenj'";

            // Debug: Log query yang akan dijalankan
            error_log("UPDATE Query: " . $query);

            $result = mysqli_query($connect, $query);
            if ($result) {
                mysqli_commit($connect);
                $_SESSION['status'] = "success";
                $_SESSION['message'] = "Data penjamin berhasil diperbarui!";
            } else {
                // Untuk UPDATE, langsung tampilkan error tanpa cek duplikasi
                $error_msg = mysqli_error($connect);
                error_log("UPDATE Penjamin Error: " . $error_msg);
                $_SESSION['status'] = "error";
                $_SESSION['message'] = "Gagal memperbarui data penjamin: " . $error_msg;
            }
        } else {
            // Insert data baru - perlu cek duplikasi
            $query = "INSERT INTO penjamin (nikpenj, niknas, hub, nama, tmpt, tgl, almt, kec, kab, usia, pek, tlfn1, tlfn2, suis, nik_suis, status) 
                     VALUES ('$nikpenj', '$niknas', '$hub', '$nama', '$tmpt', '$tgl', '$almt', '$kec', '$kab', '$usia', '$pek', '$tlfn1', '$tlfn2', '$suis', '$nik_suis', '$status')";

            $result = mysqli_query($connect, $query);
            if ($result) {
                mysqli_commit($connect);
                $_SESSION['status'] = "success";
                $_SESSION['message'] = "Data penjamin berhasil disimpan!";
            } else {
                // Untuk INSERT, throw exception agar masuk ke catch block untuk cek duplikasi
                throw new Exception(mysqli_error($connect));
            }
        }
    } catch (Exception $e) {
        mysqli_rollback($connect);
        $_SESSION['status'] = "error";

        // Debug: Log error untuk troubleshooting
        error_log("Penjamin Error: " . $e->getMessage() . " | Action: " . (isset($_POST['update_penjamin']) ? 'UPDATE' : 'INSERT'));

        // Cek apakah ini UPDATE atau INSERT
        $isUpdate = isset($_POST['update_penjamin']);
        $isDuplicateError = strpos($e->getMessage(), 'Duplicate entry') !== false;

        // Hanya tampilkan pesan duplikasi untuk INSERT, bukan UPDATE
        if (!$isUpdate && $isDuplicateError) {
            // Ambil NIK yang duplikat
            $nikpenj = mysqli_real_escape_string($connect, $_POST['nikpenj']);
            // Query untuk mendapatkan data penjamin yang sudah ada
            $query = mysqli_query($connect, "SELECT p.nikpenj, p.nama, n.nama as nama_nasabah, n.nik as nik_nasabah 
                                           FROM penjamin p 
                                           JOIN nasabah n ON p.niknas = n.nik 
                                           WHERE p.nikpenj='$nikpenj'");
            $existing_data = mysqli_fetch_assoc($query);

            if ($existing_data) {
                $_SESSION['message'] = "NIK Penjamin {$nikpenj} ({$existing_data['nama']}) sudah terdaftar sebagai penjamin untuk nasabah {$existing_data['nama_nasabah']} (NIK: {$existing_data['nik_nasabah']})";
            } else {
                $_SESSION['message'] = "NIK Penjamin {$nikpenj} sudah terdaftar dalam sistem";
            }
        } else {
            // Tampilkan error yang lebih detail untuk debugging
            $error_msg = $e->getMessage();
            if ($isUpdate) {
                $_SESSION['message'] = "Gagal memperbarui data penjamin. Error: " . $error_msg;
            } else {
                $_SESSION['message'] = "Gagal menyimpan data penjamin. Error: " . $error_msg;
            }
        }
    }

    header("Location: input-penjamin.php");
    exit();
}

// Tambahkan handler untuk mendapatkan data penjamin untuk edit
if (isset($_GET['action']) && $_GET['action'] == 'get_penjamin_detail') {
    header('Content-Type: application/json');

    if (isset($_GET['nikpenj'])) {
        $nikpenj = mysqli_real_escape_string($connect, $_GET['nikpenj']);
        $query = mysqli_query($connect, "SELECT * FROM penjamin WHERE nikpenj='$nikpenj'");
        $data = mysqli_fetch_assoc($query);

        if ($data) {
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
    exit();
}

// Tambahkan kode ini setelah tag <body>
if (isset($_SESSION['status'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '" . $_SESSION['status'] . "',
                title: '" . ($_SESSION['status'] == 'success' ? 'Berhasil' : 'Gagal') . "',
                text: '" . addslashes($_SESSION['message']) . "',
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
        });
    </script>";

    // Hapus session setelah ditampilkan
    unset($_SESSION['status']);
    unset($_SESSION['message']);
}

// Handler untuk mendapatkan data pendamping
if (isset($_GET['action']) && $_GET['action'] == 'get_pendamping') {
    header('Content-Type: application/json');

    if (isset($_GET['niknas'])) {
        $niknas = mysqli_real_escape_string($connect, $_GET['niknas']);

        // Query diubah sesuai struktur tabel pendamping
        $query = "SELECT p.nikpend, p.niknas, p.nama 
                 FROM pendamping p 
                 WHERE p.niknas='$niknas' 
                 AND p.status='Tersedia'";

        $result = mysqli_query($connect, $query);

        if ($result) {
            $pendamping = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $pendamping[] = $row;
            }

            echo json_encode([
                'status' => 'success',
                'pendamping' => $pendamping,
                'debug' => [
                    'query' => $query,
                    'count' => count($pendamping)
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Query error: ' . mysqli_error($connect),
                'debug' => [
                    'query' => $query
                ]
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Parameter niknas tidak ditemukan'
        ]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Penjamin</title>
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
            <h2 class="mb-4">Input Data Penjamin</h2>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Nasabah</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari NIK atau Nama...">
                            <button class="btn btn-primary" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-compact">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIK/NPWP</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No. Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                // Konfigurasi pagination
                                $limit = 5;
                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $limit;

                                // Tambahkan kondisi pencarian
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                $where = '';
                                if (!empty($search)) {
                                    $where = "WHERE nik LIKE '%$search%' OR nama LIKE '%$search%'";
                                }

                                // Query dengan kondisi pencarian
                                $query = mysqli_query($connect, "SELECT * FROM nasabah $where ORDER BY nik DESC LIMIT $start, $limit");

                                // Query untuk total data dengan kondisi pencarian
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM nasabah $where"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['nik']}</td>
                                        <td>{$row['nama']}</td>
                                        <td>{$row['almt']}</td>
                                        <td>{$row['tlfn1']}</td>
                                        <td>
                                            <button type='button' class='btn btn-sm btn-success' onclick='fillNikNasabah(\"{$row['nik']}\")'>
                                                <i class='bi bi-pencil'></i>
                                            </button>
                                        </td>
                                    </tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php
                                // Buat parameter URL untuk pagination dengan mempertahankan search
                                $url_params = array();
                                if (!empty($search)) {
                                    $url_params['search'] = $search;
                                }
                                // Previous button
                                $prev_params = $url_params;
                                $prev_params['page'] = $page - 1;
                                $prev_url = '?' . http_build_query($prev_params);
                                ?>
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $prev_url; ?>" tabindex="-1">Previous</a>
                                </li>

                                <?php
                                $start_number = max(1, min($page - 4, $total_pages - 9));
                                $end_number = min($total_pages, $start_number + 9);

                                if ($start_number > 1) {
                                    $first_params = $url_params;
                                    $first_params['page'] = 1;
                                    $first_url = '?' . http_build_query($first_params);
                                    echo '<li class="page-item"><a class="page-link" href="' . $first_url . '">1</a></li>';
                                    if ($start_number > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                }

                                for ($i = $start_number; $i <= $end_number; $i++) {
                                    $page_params = $url_params;
                                    $page_params['page'] = $i;
                                    $page_url = '?' . http_build_query($page_params);
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="' . $page_url . '">' . $i . '</a></li>';
                                }

                                if ($end_number < $total_pages) {
                                    if ($end_number < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                    $last_params = $url_params;
                                    $last_params['page'] = $total_pages;
                                    $last_url = '?' . http_build_query($last_params);
                                    echo '<li class="page-item"><a class="page-link" href="' . $last_url . '">' . $total_pages . '</a></li>';
                                }
                                // Next button
                                $next_params = $url_params;
                                $next_params['page'] = $page + 1;
                                $next_url = '?' . http_build_query($next_params);
                                ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $next_url; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Tambahkan bagian form input penjamin -->
            <div class="card mb-3 mt-3" id="formPenjamin" style="display: none;">
                <div class="card-body">
                    <!-- Tabel data penjamin yang sudah ada -->
                    <div id="existingPenjaminData" style="display: none;">
                        <div class="alert alert-info mb-3">
                            Data penjamin yang terdaftar untuk nasabah ini:
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>NIK Penjamin</th>
                                        <th>Nama</th>
                                        <th>Hubungan</th>
                                        <th>Alamat</th>
                                        <th>No. Telepon</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="penjaminTableBody">
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mb-3" onclick="showNewPenjaminForm()">
                            Tambah Penjamin Baru
                        </button>
                    </div>

                    <!-- Form input penjamin -->
                    <div id="penjaminForm">
                        <h5 class="card-title mb-4" style="background-color:rgb(155, 255, 208); padding: 12px; border-radius: 6px;">Form Tambah Data Penjamin</h5>
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="nikpenj" class="form-label">NIK Penjamin</label>
                                    <input type="text" class="form-control" id="nikpenj" name="nikpenj" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="niknas" class="form-label">NIK Nasabah</label>
                                    <input type="text" class="form-control readonly-green" id="niknas" name="niknas"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="hub" class="form-label">Hubungan dengan Nasabah</label>
                                    <input type="text" class="form-control" id="hub" name="hub" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tmpt" class="form-label">Tempat Lahir</label>
                                    <input type="text" class="form-control" id="tmpt" name="tmpt" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tgl" class="form-label">Tanggal Lahir</label>
                                    <input type="text" class="form-control date-input" id="tgl" name="tgl"
                                        placeholder="dd-mm-yyyy" required>
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (contoh: 01-12-2000)</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="almt" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="almt" name="almt" required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kab" class="form-label">Kabupaten</label>
                                    <select class="form-select" id="kabupaten" name="kab" required>
                                        <option value="">Pilih Kabupaten/Kota</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kec" class="form-label">Kecamatan</label>
                                    <select class="form-select" id="kecamatan" name="kec" required>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="usia" class="form-label">Usia</label>
                                    <input type="text" class="form-control" id="usia" name="usia" readonly required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="suis" class="form-label">Suami/Istri</label>
                                    <select class="form-select" id="suis" name="suis">
                                        <option value="Tidak Ada">Tidak Ada</option>
                                        <option value="SUAMI">ISTRI</option>
                                        <option value="ISTRI">SUAMI</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nik_suis" class="form-label">Pendamping</label>
                                    <select class="form-select" id="nik_suis" name="nik_suis">
                                        <option value="">Pilih Pendamping</option>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                        <?php
                                        $current_niknas = isset($_GET['niknas']) ? mysqli_real_escape_string($connect, $_GET['niknas']) : '';

                                        $query_pendamping = mysqli_query($connect, "SELECT nikpend, niknas, nama 
                                                                                  FROM pendamping 
                                                                                  WHERE niknas='$current_niknas' 
                                                                                  AND status='Tersedia'");

                                        if ($query_pendamping) {
                                            while ($pendamping = mysqli_fetch_array($query_pendamping)) {
                                                echo "<option value='" . $pendamping['nikpend'] . "'>" . $pendamping['nama'] . " (" . $pendamping['nikpend'] . ")</option>";
                                            }
                                        } else {
                                            echo "<!-- Query error: " . mysqli_error($connect) . " -->";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Pekerjaan</label>
                                    <div class="alert alert-info" role="alert">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Petunjuk:</strong> Pilih pekerjaan dari daftar di bawah. Jika pekerjaan Anda tidak ada dalam daftar, pilih "Lainnya" dan ketik pekerjaan Anda secara manual.
                                    </div>
                                    <select class="form-select" name="pek" id="pek" onchange="togglePekerjaanLainnya()" required>
                                        <option value="">Pilih Pekerjaan</option>
                                        <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/POLRI">TNI/POLRI</option>
                                        <option value="Karyawan Swasta">Karyawan Swasta</option>
                                        <option value="Karyawan Honorer">Karyawan Honorer</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Petani/Pekebun">Petani/Pekebun</option>
                                        <option value="Nelayan">Nelayan</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Guru">Guru</option>
                                        <option value="Dosen">Dosen</option>
                                        <option value="Dokter">Dokter</option>
                                        <option value="Perawat">Perawat</option>
                                        <option value="Pedagang">Pedagang</option>
                                        <option value="Pengacara">Pengacara</option>
                                        <option value="Notaris">Notaris</option>
                                        <option value="Arsitek">Arsitek</option>
                                        <option value="Akuntan">Akuntan</option>
                                        <option value="Konsultan">Konsultan</option>
                                        <option value="Freelancer">Freelancer</option>
                                        <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                                        <option value="Pensiunan">Pensiunan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <div class="mb-3" id="pekerjaanLainnyaDiv" style="display: none;">
                                        <label class="form-label">Sebutkan Pekerjaan</label>
                                        <input type="text" class="form-control" name="pek_lainnya" id="pekerjaanLainnya"
                                            placeholder="Masukkan pekerjaan Anda"
                                            pattern="[A-Za-z\s\.\,]+"
                                            title="Hanya huruf titik dan koma yang diperbolehkan"
                                            onkeypress="return /[A-Za-z\s\.\,]/.test(event.key)">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tlfn1" class="form-label">No. Telepon 1</label>
                                    <input type="tel" class="form-control" id="tlfn1" name="tlfn1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tlfn2" class="form-label">No. Telepon 2</label>
                                    <input type="tel" class="form-control" id="tlfn2" name="tlfn2">
                                </div>
                                <div class="col-md-6 mb-3" style="display: none;">
                                    <label for="status" class="form-label">Status</label>
                                    <input type="hidden" id="status" name="status" value="Tersedia">
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" onclick="return validateForm()">Simpan Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        // Tambahkan fungsi untuk memperbarui dropdown pendamping
        function updatePendampingDropdown(niknas) {
            fetch('input-penjamin.php?action=get_pendamping&niknas=' + niknas)
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('nik_suis');
                    select.innerHTML = '<option value="">Pilih Pendamping</option>';
                    select.innerHTML += '<option value="Tidak Ada">Tidak Ada</option>';

                    if (data.status === 'success' && data.pendamping) {
                        data.pendamping.forEach(p => {
                            select.innerHTML +=
                                `<option value="${p.nikpend}">${p.nama} (${p.nikpend})</option>`;
                        });
                    }
                });
        }

        // Update fungsi fillNikNasabah yang sudah ada
        function fillNikNasabah(nik) {
            document.getElementById('niknas').value = nik;
            document.getElementById('formPenjamin').style.display = 'block';

            // Update URL untuk request AJAX
            fetch('input-penjamin.php?action=get_penjamin&niknas=' + nik)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Tampilkan data dalam format tabel
                        document.getElementById('existingPenjaminData').style.display = 'block';
                        document.getElementById('penjaminForm').style.display = 'none';

                        const penjamins = Array.isArray(data.penjamin) ? data.penjamin : [data.penjamin];
                        const tableBody = document.getElementById('penjaminTableBody');
                        tableBody.innerHTML = penjamins.map(penjamin => `
                            <tr>
                                <td>${penjamin.nikpenj}</td>
                                <td>${penjamin.nama}</td>
                                <td>${penjamin.hub}</td>
                                <td>${penjamin.almt}, ${penjamin.kec}, ${penjamin.kab}</td>
                                <td>${penjamin.tlfn1}${penjamin.tlfn2 ? '<br>' + penjamin.tlfn2 : ''}</td>
                                <td>${penjamin.status}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editPenjamin('${penjamin.nikpenj}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deletePenjamin('${penjamin.nikpenj}', '${penjamin.nama}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        // Tampilkan form kosong untuk input baru
                        document.getElementById('existingPenjaminData').style.display = 'none';
                        document.getElementById('penjaminForm').style.display = 'block';
                        document.querySelector('form').reset();
                        document.getElementById('niknas').value = nik;
                    }
                    updatePendampingDropdown(nik);
                })
                .catch(error => console.error('Error:', error));

            document.getElementById('formPenjamin').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function showNewPenjaminForm() {
            const niknas = document.getElementById('niknas').value; // Simpan nilai niknas yang ada
            document.getElementById('existingPenjaminData').style.display = 'none';
            document.getElementById('penjaminForm').style.display = 'block';
            document.querySelector('form').reset();
            document.getElementById('niknas').value = niknas; // Set kembali nilai niknas setelah reset form
        }

        function formatDateForDisplay(dateString) {
            if (!dateString) return '';
            const [day, month, year] = dateString.split('-');
            return `${year}-${month}-${day}`;
        }

        // Ganti fungsi pencarian dengan tombol
        document.getElementById('searchButton').addEventListener('click', function() {
            let searchValue = document.getElementById('searchInput').value;
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', searchValue);
            currentUrl.searchParams.set('page', '1'); // Reset ke halaman pertama
            window.location.href = currentUrl.toString();
        });

        // Tambahkan event listener untuk tombol Enter pada input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
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

        function deletePenjamin(nikpenj, nama) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus data penjamin ${nama}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('delete_penjamin', '1');
                    formData.append('nikpenj', nikpenj);

                    fetch('input-penjamin.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.status,
                                title: data.status === 'success' ? 'Berhasil!' : 'Gagal!',
                                text: data.message
                            }).then(() => {
                                if (data.status === 'success') {
                                    window.location.reload();
                                }
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat memproses data'
                            });
                        });
                }
            });
        }

        function editPenjamin(nikpenj) {
            // Tampilkan form dan sembunyikan tabel
            document.getElementById('existingPenjaminData').style.display = 'none';
            document.getElementById('penjaminForm').style.display = 'block';

            // Ambil data penjamin yang akan diedit
            fetch(`input-penjamin.php?action=get_penjamin_detail&nikpenj=${nikpenj}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const penjamin = data.data;

                        // Isi form dengan data yang ada
                        document.getElementById('nikpenj').value = penjamin.nikpenj;
                        document.getElementById('nikpenj').readOnly = true; // NIK tidak bisa diubah
                        document.getElementById('niknas').value = penjamin.niknas;
                        document.getElementById('hub').value = penjamin.hub;
                        document.getElementById('nama').value = penjamin.nama;
                        document.getElementById('tmpt').value = penjamin.tmpt;
                        document.getElementById('tgl').value = penjamin.tgl;
                        document.getElementById('almt').value = penjamin.almt;
                        document.getElementById('usia').value = hitungUsia(penjamin.tgl);
                        document.getElementById('pek').value = penjamin.pek;
                        document.getElementById('tlfn1').value = penjamin.tlfn1;
                        document.getElementById('tlfn2').value = penjamin.tlfn2;
                        document.getElementById('suis').value = penjamin.suis;
                        document.getElementById('nik_suis').value = penjamin.nik_suis;
                        document.getElementById('status').value = penjamin.status;

                        // Tambahkan input hidden untuk menandai bahwa ini adalah update
                        const form = document.querySelector('form');
                        const updateInput = document.createElement('input');
                        updateInput.type = 'hidden';
                        updateInput.name = 'update_penjamin';
                        updateInput.value = '1';
                        form.appendChild(updateInput);

                        // Handle kabupaten dan kecamatan
                        const kabupatenSelect = document.getElementById('kabupaten');
                        const kecamatanSelect = document.getElementById('kecamatan');

                        // Set nilai kabupaten
                        const kabupatenOption = new Option(penjamin.kab, penjamin.kab, true, true);
                        kabupatenSelect.appendChild(kabupatenOption);
                        kabupatenSelect.value = penjamin.kab;

                        // Set nilai kecamatan
                        const kecamatanOption = new Option(penjamin.kec, penjamin.kec, true, true);
                        kecamatanSelect.appendChild(kecamatanOption);
                        kecamatanSelect.value = penjamin.kec;

                        // Scroll ke form
                        document.getElementById('formPenjamin').scrollIntoView({
                            behavior: 'smooth'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Data penjamin tidak ditemukan'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengambil data'
                    });
                });
        }

        // Tambahkan event listener untuk reset form
        document.querySelector('form').addEventListener('reset', function() {
            // Hapus input hidden update_penjamin jika ada
            const updateInput = this.querySelector('input[name="update_penjamin"]');
            if (updateInput) {
                updateInput.remove();
            }

            // Reset readonly pada nikpenj
            document.getElementById('nikpenj').readOnly = false;
        });

        // Fungsi sederhana untuk menghitung usia
        function hitungUsia(input) {
            // Hanya proses jika panjang input adalah 10 karakter (dd-mm-yyyy)
            if (input.length === 10) {
                const [day, month, year] = input.split('-');
                const birthDate = new Date(year, month - 1, day);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();

                // Kurangi 1 tahun jika belum ulang tahun
                if (today.getMonth() < birthDate.getMonth() ||
                    (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                return age;
            }
            return '';
        }

        // Event listener untuk input tanggal lahir
        document.getElementById('tgl').addEventListener('input', function() {
            const usia = hitungUsia(this.value);
            document.getElementById('usia').value = usia;
        });

        // Fungsi untuk toggle input pekerjaan lainnya
        function togglePekerjaanLainnya() {
            const pekerjaanSelect = document.getElementById('pek');
            const pekerjaanLainnyaDiv = document.getElementById('pekerjaanLainnyaDiv');
            const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');

            if (pekerjaanSelect.value === 'Lainnya') {
                pekerjaanLainnyaDiv.style.display = 'block';
                pekerjaanLainnyaInput.required = true;
            } else {
                pekerjaanLainnyaDiv.style.display = 'none';
                pekerjaanLainnyaInput.required = false;
                pekerjaanLainnyaInput.value = '';
            }
        }

        // Fungsi validasi form
        function validateForm() {
            const pekerjaanSelect = document.getElementById('pek');
            const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');

            if (pekerjaanSelect.value === 'Lainnya') {
                if (!pekerjaanLainnyaInput.value.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Harap isi pekerjaan Anda jika memilih Lainnya',
                        confirmButtonText: 'OK'
                    });
                    pekerjaanLainnyaInput.focus();
                    return false;
                }

                // Validasi format input (hanya huruf, spasi, titik, dan koma)
                const regex = /^[A-Za-z\s\.\,]+$/;
                if (!regex.test(pekerjaanLainnyaInput.value.trim())) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Pekerjaan hanya boleh berisi huruf, spasi, titik, dan koma',
                        confirmButtonText: 'OK'
                    });
                    pekerjaanLainnyaInput.focus();
                    return false;
                }
            }
            return true;
        }
    </script>

</body>

</html>