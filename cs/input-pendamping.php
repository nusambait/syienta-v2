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

if (isset($_POST['nikpend'])) {
    // Ambil data dari form
    $nikpend = mysqli_real_escape_string($connect, $_POST['nikpend']);
    $niknas = mysqli_real_escape_string($connect, $_POST['niknas']);
    $hub = mysqli_real_escape_string($connect, $_POST['hub']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $tmpt = mysqli_real_escape_string($connect, $_POST['tmpt']);

    // Ubah format tanggal dari yyyy-mm-dd menjadi dd-mm-yyyy
    $tgl_input = mysqli_real_escape_string($connect, $_POST['tgl']);
    $tgl = date('d-m-Y', strtotime($tgl_input));

    $pek = mysqli_real_escape_string($connect, $_POST['pek']);

    // Proses pekerjaan - jika memilih Lainnya, gunakan input manual
    if ($pek == 'Lainnya') {
        if (isset($_POST['pek_lainnya']) && !empty($_POST['pek_lainnya'])) {
            $pek_lainnya = mysqli_real_escape_string($connect, $_POST['pek_lainnya']);

            // Validasi format input (hanya huruf, spasi, titik, dan koma)
            if (!preg_match('/^[A-Za-z\s\.\,]+$/', $pek_lainnya)) {
                $_SESSION['status'] = "error";
                $_SESSION['message'] = "Pekerjaan hanya boleh berisi huruf, spasi, titik, dan koma";
                header("Location: input-pendamping.php");
                exit();
            }

            $pek = $pek_lainnya;
        } else {
            // Jika memilih Lainnya tapi tidak mengisi input, kembalikan error
            $_SESSION['status'] = "error";
            $_SESSION['message'] = "Harap isi pekerjaan Anda jika memilih Lainnya";
            header("Location: input-pendamping.php");
            exit();
        }
    }

    $tlfn1 = mysqli_real_escape_string($connect, $_POST['tlfn1']);
    $tlfn2 = mysqli_real_escape_string($connect, $_POST['tlfn2']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    // Validasi nomor telepon
    $tlfn1 = empty($tlfn1) ? '+62' : $tlfn1;
    $tlfn2 = empty($tlfn2) ? '+62' : $tlfn2;

    // Query untuk menyimpan data
    $query = "INSERT INTO pendamping (nikpend, niknas, hub, nama, tmpt, tgl, pek, tlfn1, tlfn2, status) 
              VALUES ('$nikpend', '$niknas', '$hub', '$nama', '$tmpt', '$tgl', '$pek', '$tlfn1', '$tlfn2', '$status')";

    // Eksekusi query dengan penanganan error duplicate entry
    try {
        if (mysqli_query($connect, $query)) {
            $_SESSION['status'] = "success";
            $_SESSION['message'] = "Data pendamping berhasil disimpan!";
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['status'] = "error";
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $_SESSION['message'] = "NIK Pendamping Sudah Ada!";
        } else {
            $_SESSION['message'] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }

    header("Location: input-pendamping.php");
    exit();
}

// Tambahkan kode ini setelah tag <body>
if (isset($_SESSION['status'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '" . $_SESSION['status'] . "',
                title: '" . ($_SESSION['status'] == 'success' ? 'Berhasil' : 'Gagal') . "',
                text: '" . $_SESSION['message'] . "',
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
        });
    </script>";

    unset($_SESSION['status']);
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Pendamping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Cek Data Pendamping</h2>
                <a href="data-pendamping.php" class="btn btn-primary">
                    <i class="bi bi-clipboard-data"></i> Cek Data Pendamping
                </a>
            </div>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Nasabah</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari NIK atau Nama...">
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

            <!-- Tambahkan bagian form input pendamping -->
            <div class="card mb-3 mt-3" id="formPendamping" style="display: none;">
                <div class="card-body">
                    <h5 class="card-title mb-4 bg-judul-card">Form Tambah Data Pendamping</h5>
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="nikpend" class="form-label">NIK Pendamping</label>
                                <input type="text" class="form-control" id="nikpend" name="nikpend" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="niknas" class="form-label">NIK Nasabah</label>
                                <input type="text" class="form-control readonly-green" id="niknas" name="niknas" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hub" class="form-label">Hubungan dengan Nasabah</label>
                                <select class="form-select" id="hub" name="hub" required onchange="toggleHubunganLain(this.value)">
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Suami">Suami</option>
                                    <option value="Istri">Istri</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Orang Tua">Orang Tua</option>
                                    <option value="Saudara">Saudara</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="hubunganLain" name="hubunganLain"
                                    placeholder="Masukkan hubungan lainnya" style="display: none;">
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
                                <input type="date" class="form-control" id="tgl" name="tgl" required>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mengisi NIK Nasabah
        function fillNikNasabah(nik) {
            document.getElementById('niknas').value = nik;
            document.getElementById('formPendamping').style.display = 'block'; // Menampilkan form
            // Scroll ke form
            document.getElementById('formPendamping').scrollIntoView({
                behavior: 'smooth'
            });
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

        function toggleHubunganLain(value) {
            const hubunganLainInput = document.getElementById('hubunganLain');
            if (value === 'Lainnya') {
                hubunganLainInput.style.display = 'block';
                hubunganLainInput.required = true;
                document.getElementById('hub').name = 'hub_temp';
                hubunganLainInput.name = 'hub';
            } else {
                hubunganLainInput.style.display = 'none';
                hubunganLainInput.required = false;
                hubunganLainInput.value = '';
                document.getElementById('hub').name = 'hub';
                hubunganLainInput.name = 'hubunganLain';
            }
        }

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