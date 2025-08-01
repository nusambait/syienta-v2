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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Droping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
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

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Input Droping Nasabah</h2>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Droping</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari No. Droping atau Nama...">
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
                                    <th>No. Reg</th>
                                    <th>Nama</th>
                                    <th>Tgl Pengajuan</th>
                                    <th>Tgl Droping</th>
                                    <th>Plafond</th>
                                    <th>Jangka Waktu</th>
                                    <th>Suku Bunga</th>
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
                                        d.plafond as plafond_disetujui
                                    FROM pengajuan p
                                    INNER JOIN nasabah n ON n.nik = p.niknas
                                    LEFT JOIN droping d ON d.noreg = p.noreg
                                    $where 
                                    ORDER BY d.tgl_droping DESC, p.noreg DESC 
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
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['noreg']}</td>
                                        <td>{$row['nama_nasabah']}</td>
                                        <td>{$row['tglpeng']}</td>
                                        <td>{$row['tgl_droping']}</td>
                                        <td>Pengajuan: Rp " . number_format($row['plaf'],0,',','.') . "<br>
                                            Disetujui: Rp " . number_format($row['plafond_disetujui'],0,',','.') . "</td>
                                        <td>{$row['jw']} Bulan</td>
                                        <td>{$row['sukbung']}%</td>
                                        <td>{$row['status']}</td>
                                        <td>
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
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                </li>

                                <?php
                                $start_number = max(1, min($page - 4, $total_pages - 9));
                                $end_number = min($total_pages, $start_number + 9);

                                if ($start_number > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                    if ($start_number > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                }

                                for ($i = $start_number; $i <= $end_number; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . '">' . $i . '</a>
                                          </li>';
                                }

                                if ($end_number < $total_pages) {
                                    if ($end_number < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
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
    </script>
</body>

</html>