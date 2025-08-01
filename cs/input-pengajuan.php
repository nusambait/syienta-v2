<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Tambahkan endpoint untuk mengambil data pengajuan
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'get_pengajuan' && isset($_GET['nik'])) {
        $nik = mysqli_real_escape_string($connect, $_GET['nik']);

        $query = "SELECT p.noreg as no_pengajuan, 
                         n.nama, 
                         p.tglpeng as tgl_pengajuan, 
                         p.plaf as jumlah, 
                         p.status 
                 FROM pengajuan p 
                 JOIN nasabah n ON p.niknas = n.nik 
                 WHERE n.nik = '$nik' 
                 ORDER BY STR_TO_DATE(p.tglpeng, '%d-%m-%Y') DESC";

        $result = mysqli_query($connect, $query);

        if (!$result) {
            // Jika query error, kirim pesan error
            header('Content-Type: application/json');
            echo json_encode(['error' => mysqli_error($connect)]);
            exit;
        }

        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            // Format tanggal dari dd-mm-yyyy ke d-m-Y
            $date = DateTime::createFromFormat('d-m-Y', $row['tgl_pengajuan']);
            if ($date) {
                $row['tgl_pengajuan'] = $date->format('d-m-Y');
            }
            // Format jumlah ke rupiah
            $row['jumlah'] = number_format((float)$row['jumlah'], 0, ',', '.');
            // Pastikan status tidak null
            $row['status'] = empty($row['status']) ? '-' : $row['status'];
            $data[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    // Tambahkan endpoint baru untuk mendapatkan detail pengajuan
    else if ($_GET['action'] == 'get_detail_pengajuan' && isset($_GET['noreg'])) {
        $noreg = mysqli_real_escape_string($connect, $_GET['noreg']);

        $query = "SELECT p.*, n.* FROM pengajuan p 
                  JOIN nasabah n ON p.niknas = n.nik 
                  WHERE p.noreg = '$noreg'";

        $result = mysqli_query($connect, $query);

        if (!$result) {
            header('Content-Type: application/json');
            echo json_encode(['error' => mysqli_error($connect)]);
            exit;
        }

        $data = mysqli_fetch_assoc($result);

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.onload = function() {
            <?php if (isset($_SESSION['success_message'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses',
                    text: '<?php echo $_SESSION['success_message']; ?>',
                    showConfirmButton: false,
                    timer: 1500
                });
            <?php unset($_SESSION['success_message']);
            endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $_SESSION['error_message']; ?>'
                });
            <?php unset($_SESSION['error_message']);
            endif; ?>
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchButton = document.getElementById('searchButton');
            const searchInput = document.getElementById('searchInput');

            if (searchButton && searchInput) {
                // Fungsi pencarian
                function performSearch() {
                    let searchValue = searchInput.value;
                    window.location.href = 'input-pengajuan.php?search=' + encodeURIComponent(searchValue);
                }

                // Event listener untuk tombol search
                searchButton.addEventListener('click', performSearch);

                // Event listener untuk tombol Enter
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        performSearch();
                    }
                });

                // Isi nilai pencarian dari parameter URL
                let urlParams = new URLSearchParams(window.location.search);
                let searchValue = urlParams.get('search');
                if (searchValue) {
                    searchInput.value = searchValue;
                }
            }

            // Tampilkan notifikasi sukses jika ada
            <?php if (isset($_SESSION['success_message'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses',
                    text: <?php echo json_encode($_SESSION['success_message']); ?>,
                    showConfirmButton: false,
                    timer: 1500
                });
            <?php unset($_SESSION['success_message']);
            endif; ?>

            // Tampilkan notifikasi error jika ada
            <?php if (isset($_SESSION['error_message'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: <?php echo json_encode($_SESSION['error_message']); ?>
                });
            <?php unset($_SESSION['error_message']);
            endif; ?>
        });
    </script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Input Jaminan Nasabah</h2>

            <!-- Tambahkan panduan penggunaan -->
            <div class="alert alert-info mb-4" role="alert">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Panduan Penggunaan:</h5>
                <ol class="mb-0">
                    <li>Cari data nasabah dengan memasukkan NIK atau Nama pada kotak pencarian di atas tabel.</li>
                    <li>Setelah menemukan nasabah yang dicari, klik tombol <button class="btn btn-sm btn-success"
                            disabled><i class="bi bi-pencil"></i></button> pada kolom Aksi.</li>
                    <li>Di form ini ada pilihan pengajuan yang sudah ada, atau anda bisa membuat pengajuan baru.</li>
                    <li>Isi formulir data jaminan dengan lengkap dan benar.</li>
                </ol>
            </div>

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
                                            <button type='button' class='btn btn-sm btn-success' data-bs-toggle='modal' data-bs-target='#pengajuanModal' onclick='listPengajuan(\"{$row['nik']}\")'>
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

        </div>
    </div>

    <!-- Tambahkan Modal di atas tag penutup body -->
    <div class="modal fade" id="pengajuanModal" tabindex="-1" aria-labelledby="pengajuanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pengajuanModalLabel">Data Pengajuan Nasabah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-primary"
                            onclick="window.location.href='form-pengajuan.php?nik=' + encodeURIComponent(window.currentNik)">
                            <i class="bi bi-plus-circle"></i> Tambah Pengajuan Baru
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>No Pengajuan</th>
                                    <th>Nama</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="pengajuanTableBody">
                                <!-- Data akan diisi melalui AJAX -->
                            </tbody>
                        </table>
                    </div>
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

        // Fungsi untuk mengambil data kabupaten
        function getKabupaten() {
            fetch('https://www.emsifa.com/api-wilayah-indonesia/api/regencies/32.json')
                .then(response => response.json())
                .then(kabupaten => {
                    let kabupatenSelect = document.getElementById('kabupaten');
                    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';

                    kabupaten.forEach(kab => {
                        let option = document.createElement('option');
                        option.value = kab.name;
                        option.textContent = kab.name;
                        option.setAttribute('data-id', kab.id);
                        kabupatenSelect.appendChild(option);
                    });
                });
        }

        // Fungsi untuk mengambil data kecamatan berdasarkan kabupaten
        function getKecamatan(kabupatenId) {
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${kabupatenId}.json`)
                .then(response => response.json())
                .then(kecamatan => {
                    let kecamatanSelect = document.getElementById('kecamatan');
                    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';

                    kecamatan.forEach(kec => {
                        let option = document.createElement('option');
                        option.value = kec.name;
                        option.textContent = kec.name;
                        kecamatanSelect.appendChild(option);
                    });
                });
        }

        // Load kabupaten saat halaman dimuat
        document.addEventListener('DOMContentLoaded', getKabupaten);

        // Event listener untuk perubahan kabupaten
        document.getElementById('kabupaten').addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const kabupatenId = selectedOption.getAttribute('data-id');
                getKecamatan(kabupatenId);
            }
        });

        function getBadgeClass(status) {
            // Pastikan status adalah string dan tidak null/undefined
            if (!status || typeof status !== 'string') return 'bg-secondary';

            // Bersihkan status dari spasi dan ubah ke lowercase
            status = status.trim().toLowerCase();

            switch (status) {
                case 'cair':
                case 'siap cair':
                case 'acc_direksi':
                case 'acc_kabid':
                case 'acc_kacab':
                    return 'bg-success';
                case 'tolak':
                case 'batal':
                    return 'bg-danger';
                case 'mcc_kabid':
                case 'mcc_kacab':
                case 'mcc_direksi':
                    return 'bg-warning';
                default:
                    return 'bg-secondary';
            }
        }

        function listPengajuan(nik) {
            // Simpan NIK ke variabel global
            window.currentNik = nik;

            fetch(`input-pengajuan.php?action=get_pengajuan&nik=${nik}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('pengajuanTableBody');
                    tbody.innerHTML = '';

                    // Check if there's an error
                    if (data.error) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-danger">Error: ${data.error}</td>
                            </tr>
                        `;
                        return;
                    }

                    if (data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data pengajuan</td>
                            </tr>
                        `;
                        return;
                    }

                    data.forEach((item, index) => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.no_pengajuan || '-'}</td>
                                <td>${item.nama || '-'}</td>
                                <td>${item.tgl_pengajuan || '-'}</td>
                                <td>Rp ${item.jumlah || '0'}</td>
                                <td>
                                    <span class="badge ${getBadgeClass(item.status)}">${item.status || '-'}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="window.location.href='edit-pengajuan.php?noreg=${encodeURIComponent(item.no_pengajuan)}'">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('pengajuanTableBody').innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-danger">Terjadi kesalahan saat mengambil data</td>
                        </tr>
                    `;
                });
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(angka);
        }
    </script>
</body>

</html>