<?php
ob_clean();
session_start();
include '../../config.php';
include '../config/config.php';

// Fungsi untuk export ke Excel
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="data_droping_' . date('Y-m-d') . '.xls"');

    // Query untuk mengambil data sesuai dengan yang ditampilkan di tabel
    $where = "WHERE p.status NOT LIKE 'BATAL%' 
             AND p.status != 'SURVEI'
             AND p.status NOT LIKE 'TOLAK%'";

    // Tambahkan filter berdasarkan kode kantor dari session
    if (isset($_SESSION['kantor']) && $_SESSION['key_app'] != 'ADMIN') {
        $kode_kantor = substr($_SESSION['kantor'], 0, 2);
        $where .= " AND p.noreg LIKE '$kode_kantor%'";
    }

    // Tambahkan kondisi pencarian jika ada
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = mysqli_real_escape_string($connect, $_GET['search']);
        $where .= " AND (p.noreg LIKE '%$search%' OR n.nama LIKE '%$search%')";
    }

    $query = mysqli_query($connect, "
        SELECT 
            p.*,
            n.nama as nama_nasabah,
            d.tgl_acc_direksi,
            d.tgl_droping,
            d.plafond as plafond_disetujui,
            a.nama as nama_ao
        FROM pengajuan p
        INNER JOIN nasabah n ON n.nik = p.niknas
        LEFT JOIN droping d ON d.noreg = p.noreg
        LEFT JOIN account a ON a.kd_ao = p.ao
        $where 
        ORDER BY STR_TO_DATE(p.tglpeng, '%d-%m-%Y') DESC, p.noreg DESC
    ");

    if (!$query) {
        die('Error: ' . mysqli_error($connect));
    }

    echo '<table border="1">';
    echo '<tr>
            <th>No</th>
            <th>No. Reg</th>
            <th>Nama</th>
            <th>Tgl Pengajuan</th>
            <th>Plafond</th>
            <th>Jangka Waktu</th>
            <th>Status</th>
            <th>NIK</th>
            <th>No. Rekening</th>
            <th>Pengajuan Ke</th>
            <th>Jenis Kredit</th>
            <th>Produk Kredit</th>
            <th>Plafond Disetujui</th>
            <th>Suku Bunga</th>
            <th>Angsuran Pokok</th>
            <th>Angsuran Bunga</th>
            <th>Total Angsuran</th>
            <th>Biaya Provisi</th>
            <th>Nominal Provisi</th>
            <th>Administrasi</th>
            <th>Penggunaan</th>
            <th>Tujuan Penggunaan</th>
            <th>Jaminan</th>
            <th>AO</th>
            <th>Agen</th>
            <th>Sumber</th>
            <th>Pembawa Berkas</th>
          </tr>';

    $no = 1;
    while ($row = mysqli_fetch_array($query)) {
        echo '<tr>';
        echo '<td>' . $no . '</td>';
        echo '<td>' . $row['noreg'] . '</td>';
        echo '<td>' . $row['nama_nasabah'] . '</td>';
        echo '<td>' . $row['tglpeng'] . '</td>';
        echo '<td>' . number_format($row['plaf'], 0, ',', '.') . '</td>';
        echo '<td>' . $row['jw'] . ' Bulan</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '<td>' . $row['niknas'] . '</td>';
        echo '<td>' . $row['norek'] . '</td>';
        echo '<td>' . $row['pengajuan'] . '</td>';
        echo '<td>' . $row['jns_kredit'] . '</td>';
        echo '<td>' . $row['prodkre'] . '</td>';
        echo '<td>' . number_format($row['plafond_disetujui'], 0, ',', '.') . '</td>';
        echo '<td>' . $row['sukbung'] . '%</td>';
        echo '<td>' . number_format($row['angpok'], 0, ',', '.') . '</td>';
        echo '<td>' . number_format($row['angbung'], 0, ',', '.') . '</td>';
        echo '<td>' . number_format($row['totang'], 0, ',', '.') . '</td>';
        echo '<td>' . $row['biaprov'] . '%</td>';
        echo '<td>' . number_format($row['nomprov'], 0, ',', '.') . '</td>';
        echo '<td>' . number_format($row['adm'], 0, ',', '.') . '</td>';
        echo '<td>' . $row['penggunaan'] . '</td>';
        echo '<td>' . $row['tujpeng'] . '</td>';
        echo '<td>' . $row['jaminan'] . '</td>';
        echo '<td>' . $row['nama_ao'] . ' (' . $row['ao'] . ')</td>';
        echo '<td>' . $row['agen'] . '</td>';
        echo '<td>' . $row['sumber'] . '</td>';
        echo '<td>' . $row['FO'] . '</td>';
        echo '</tr>';
        $no++;
    }
    echo '</table>';
    exit;
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
        $_SESSION['kantor'] = $data['kantor'];

        header("Location: ../dashboard.php");
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}
include '../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Droping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchButton = document.getElementById('searchButton');
            const searchInput = document.getElementById('searchInput');

            if (searchButton && searchInput) {
                // Fungsi pencarian
                function performSearch() {
                    let searchValue = searchInput.value;
                    window.location.href = 'input-droping.php?search=' + encodeURIComponent(searchValue);
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
        });
    </script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Input Droping Nasabah</h2>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Droping</h5>
                        <div class="d-flex gap-2">
                            <div class="col-md-10 d-flex gap-2">
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Cari No. Register atau Nama...">
                                <button class="btn btn-primary" id="searchButton">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <?php
                            $export_url = "?export=1";
                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $export_url .= "&search=" . urlencode($_GET['search']);
                            }
                            ?>
                            <a href="<?php echo $export_url; ?>" class="btn btn-success">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-compact">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>No. Reg</th>
                                    <th>Nama</th>
                                    <th>Tgl Pengajuan</th>
                                    <th>Plafond</th>
                                    <th>Jangka Waktu</th>
                                    <th>Status</th>
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
                                $where = "WHERE p.status NOT LIKE 'BATAL%' 
                                         AND p.status != 'SURVEI'
                                         AND p.status NOT LIKE 'TOLAK%'";

                                // Tambahkan filter berdasarkan kode kantor dari session
                                if (isset($_SESSION['kantor']) && $_SESSION['key_app'] != 'ADMIN') {
                                    $kode_kantor = substr($_SESSION['kantor'], 0, 2); // Ambil 2 digit pertama dari kantor
                                    $where .= " AND p.noreg LIKE '$kode_kantor%'";
                                }

                                if (!empty($search)) {
                                    $where .= " AND (p.noreg LIKE '%$search%' OR n.nama LIKE '%$search%')";
                                }

                                // Query dengan join dan kondisi pencarian
                                $query_string = "
                                    SELECT 
                                        p.*,
                                        n.nama as nama_nasabah,
                                        d.tgl_acc_direksi,
                                        d.tgl_droping,
                                        d.plafond as plafond_disetujui,
                                        a.nama as nama_ao
                                    FROM pengajuan p
                                    INNER JOIN nasabah n ON n.nik = p.niknas
                                    LEFT JOIN droping d ON d.noreg = p.noreg
                                    LEFT JOIN account a ON a.kd_ao = p.ao
                                    $where 
                                    ORDER BY STR_TO_DATE(p.tglpeng, '%d-%m-%Y') DESC, p.noreg DESC 
                                    LIMIT $start, $limit
                                ";

                                // Tambahkan debug untuk memeriksa data
                                $result = mysqli_query($connect, $query_string);
                                if (!$result) {
                                    echo "Error: " . mysqli_error($connect);
                                }

                                // Debug: Tampilkan query
                                echo "<!-- Query: " . htmlspecialchars($query_string) . " -->";

                                // Debug: Tampilkan data row pertama
                                if ($row = mysqli_fetch_array($result)) {
                                    echo "<!-- Debug Data: ";
                                    print_r($row);
                                    echo " -->";
                                    // Reset pointer query
                                    mysqli_data_seek($result, 0);
                                }

                                // Query untuk total data dengan kondisi pencarian
                                $total_query = mysqli_query($connect, "
                                    SELECT COUNT(*) 
                                    FROM pengajuan p
                                    INNER JOIN nasabah n ON n.nik = p.niknas
                                    $where
                                ");
                                $total_records = mysqli_fetch_array($total_query)[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($result)) {
                                    $nilai_plaf = ($row['plaf'] === null) ? 0 : $row['plaf'];
                                    $nilai_plafond_disetujui = ($row['plafond_disetujui'] === null) ? 0 : $row['plafond_disetujui'];
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['noreg']}</td>
                                        <td>{$row['nama_nasabah']}</td>
                                        <td>{$row['tglpeng']}</td>
                                        <td>Pengajuan: Rp " . number_format($nilai_plaf, 0, ',', '.') . "<br>
                                            Disetujui: Rp " . number_format($nilai_plafond_disetujui, 0, ',', '.') . "</td>
                                        <td>{$row['jw']} Bulan</td>
                                        <td>{$row['status']} <br><small class='text-muted'>{$row['update_at']}</small></td>
                                        <td>
                                            <button type='button' class='btn btn-sm btn-info me-1' 
                                                onclick='showDetail(\"{$row['noreg']}\", \"{$row['nama_nasabah']}\", \"{$row['niknas']}\", 
                                                \"{$row['norek']}\", \"{$row['tglpeng']}\", \"{$row['pengajuan']}\", 
                                                \"{$row['jns_kredit']}\", \"{$row['penggunaan']}\", \"{$row['prodkre']}\", 
                                                \"$nilai_plaf\", \"$nilai_plafond_disetujui\", \"{$row['jw']}\", 
                                                \"{$row['sukbung']}\", \"{$row['angpok']}\", \"{$row['angbung']}\", 
                                                \"{$row['totang']}\", \"{$row['biaprov']}\", \"{$row['nomprov']}\", 
                                                \"{$row['adm']}\", \"{$row['tujpeng']}\", \"{$row['jaminan']}\", 
                                                \"{$row['nama_ao']}\", \"{$row['ao']}\", \"{$row['agen']}\", 
                                                \"{$row['status']}\",  \"{$row['sumber']}\", 
                                                \"{$row['FO']}\")'>
                                                <i class='bi bi-eye'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-success' 
                                                onclick='window.location.href=\"edit-droping.php?noreg={$row['noreg']}\"'>
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
                                // Buat parameter tambahan untuk pagination
                                $pagination_params = '';
                                if (isset($_GET['search']) && !empty($_GET['search'])) {
                                    $pagination_params .= '&search=' . urlencode($_GET['search']);
                                }
                                ?>
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $pagination_params; ?>"
                                        tabindex="-1">Previous</a>
                                </li>

                                <?php
                                $start_number = max(1, min($page - 4, $total_pages - 9));
                                $end_number = min($total_pages, $start_number + 9);

                                if ($start_number > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1' . $pagination_params . '">1</a></li>';
                                    if ($start_number > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                }

                                for ($i = $start_number; $i <= $end_number; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . $pagination_params . '">' . $i . '</a>
                                          </li>';
                                }

                                if ($end_number < $total_pages) {
                                    if ($end_number < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . $pagination_params . '">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $pagination_params; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pengajuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Informasi Nasabah -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informasi Nasabah</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 40%"><strong>No. Register</strong></td>
                                                    <td>: <span id="modal_noreg"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nama Nasabah</strong></td>
                                                    <td>: <span id="modal_nama"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>NIK</strong></td>
                                                    <td>: <span id="modal_nik"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>No. Rekening</strong></td>
                                                    <td>: <span id="modal_norek"></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 40%"><strong>Tanggal Pengajuan</strong></td>
                                                    <td>: <span id="modal_tglpeng"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pengajuan Ke</strong></td>
                                                    <td>: <span id="modal_jenis_pengajuan"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jenis Kredit</strong></td>
                                                    <td>: <span id="modal_jenis_kredit"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Produk Kredit</strong></td>
                                                    <td>: <span id="modal_produk"></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Keuangan -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informasi Keuangan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 40%"><strong>Plafond Pengajuan</strong></td>
                                                    <td>: Rp <span id="modal_plafond"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Plafond Disetujui</strong></td>
                                                    <td>: Rp <span id="modal_plafond_disetujui"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jangka Waktu</strong></td>
                                                    <td>: <span id="modal_jw"></span> Bulan</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Suku Bunga</strong></td>
                                                    <td>: <span id="modal_sukbung"></span>%</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Biaya Provisi</strong></td>
                                                    <td>: <span id="modal_biaprov"></span>%</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 40%"><strong>Angsuran Pokok</strong></td>
                                                    <td>: Rp <span id="modal_angpok"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Angsuran Bunga</strong></td>
                                                    <td>: Rp <span id="modal_angbung"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Angsuran</strong></td>
                                                    <td>: Rp <span id="modal_totang"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Nominal Provisi</strong></td>
                                                    <td>: Rp <span id="modal_nomprov"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Administrasi</strong></td>
                                                    <td>: Rp <span id="modal_adm"></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Tambahan -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informasi Tambahan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 40%"><strong>Penggunaan</strong></td>
                                                    <td>: <span id="modal_penggunaan"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tujuan Penggunaan</strong></td>
                                                    <td>: <span id="modal_tujpeng"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jaminan</strong></td>
                                                    <td>: <span id="modal_jaminan"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status</strong></td>
                                                    <td>: <span id="modal_status"></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td style="width: 40%"><strong>AO</strong></td>
                                                    <td>: <span id="modal_ao"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Agen</strong></td>
                                                    <td>: <span id="modal_agen"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Sumber</strong></td>
                                                    <td>: <span id="modal_sumber"></span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pembawa Berkas</strong></td>
                                                    <td>: <span id="modal_fo"></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

        function showDetail(noreg, nama, nik, norek, tglpeng, pengajuan, jns_kredit, penggunaan, prodkre,
            plaf, plafond_disetujui, jw, sukbung, angpok, angbung, totang, biaprov, nomprov,
            adm, tujpeng, jaminan, nama_ao, ao, agen, status, sumber, fo) {
            // Format currency
            function formatCurrency(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            document.getElementById('modal_noreg').textContent = noreg;
            document.getElementById('modal_nama').textContent = nama;
            document.getElementById('modal_nik').textContent = nik;
            document.getElementById('modal_norek').textContent = norek;
            document.getElementById('modal_tglpeng').textContent = tglpeng;
            document.getElementById('modal_jenis_pengajuan').textContent = pengajuan;
            document.getElementById('modal_jenis_kredit').textContent = jns_kredit;
            document.getElementById('modal_penggunaan').textContent = penggunaan;
            document.getElementById('modal_produk').textContent = prodkre;
            document.getElementById('modal_plafond').textContent = formatCurrency(plaf);
            document.getElementById('modal_plafond_disetujui').textContent = formatCurrency(plafond_disetujui);
            document.getElementById('modal_jw').textContent = jw;
            document.getElementById('modal_sukbung').textContent = sukbung;
            document.getElementById('modal_angpok').textContent = formatCurrency(angpok);
            document.getElementById('modal_angbung').textContent = formatCurrency(angbung);
            document.getElementById('modal_totang').textContent = formatCurrency(totang);
            document.getElementById('modal_biaprov').textContent = biaprov;
            document.getElementById('modal_nomprov').textContent = formatCurrency(nomprov);
            document.getElementById('modal_adm').textContent = formatCurrency(adm);
            document.getElementById('modal_tujpeng').textContent = tujpeng;
            document.getElementById('modal_jaminan').textContent = jaminan;
            document.getElementById('modal_ao').textContent = nama_ao + ' (' + ao + ')';
            document.getElementById('modal_agen').textContent = agen;
            document.getElementById('modal_status').textContent = status;
            document.getElementById('modal_sumber').textContent = sumber;
            document.getElementById('modal_fo').textContent = fo;

            // Show modal
            var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
            myModal.show();
        }
    </script>
</body>

</html>