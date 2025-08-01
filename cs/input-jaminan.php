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
    <script>
        let selectedNik = '';

        function showJaminanModal(nik, nama) {
            selectedNik = nik;
            document.getElementById('selectedNasabah').textContent = `${nik} - ${nama}`;
            let jaminanModal = new bootstrap.Modal(document.getElementById('jaminanModal'));
            jaminanModal.show();
        }

        function redirectToJaminan(type) {
            if (selectedNik) {
                window.location.href = `form-${type}.php?nik=${selectedNik}`;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Kode search yang sudah ada
            const searchButton = document.getElementById('searchButton');
            const searchInput = document.getElementById('searchInput');

            if (searchButton && searchInput) {
                // Fungsi pencarian
                function performSearch() {
                    let searchValue = searchInput.value;
                    window.location.href = 'input-jaminan.php?search=' + encodeURIComponent(searchValue);
                }

                searchButton.addEventListener('click', performSearch);

                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        performSearch();
                    }
                });

                let urlParams = new URLSearchParams(window.location.search);
                let searchValue = urlParams.get('search');
                if (searchValue) {
                    searchInput.value = searchValue;
                }
            }

            // Cek keberadaan modal
            const modalElement = document.getElementById('jaminanModal');
            if (modalElement) {
                console.log('Modal element ditemukan');
            } else {
                console.error('Modal element tidak ditemukan');
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Input Jaminan Nasabah</h2>
                <a href="data-jaminan.php" class="btn btn-primary">
                    <i class="bi bi-clipboard-data"></i> Cek Data Jaminan
                </a>
            </div>

            <!-- Tambahkan panduan penggunaan -->
            <div class="alert alert-info mb-4" role="alert">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Panduan Penggunaan:</h5>
                <ol class="mb-0">
                    <li>Cari data nasabah dengan memasukkan NIK atau Nama pada kotak pencarian di atas tabel.</li>
                    <li>Setelah menemukan nasabah yang dicari, klik tombol <button class="btn btn-sm btn-success"
                            disabled><i class="bi bi-pencil"></i></button> pada kolom Aksi.</li>
                    <li>Pilih jenis jaminan yang sesuai (BPKB, Sertifikat, dll).</li>
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
                                            <button type='button' class='btn btn-sm btn-success' onclick='showJaminanModal(\"{$row['nik']}\", \"{$row['nama']}\")'>
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

    <!-- Tambahkan modal di bagian bawah body sebelum closing tag -->
    <div class="modal fade" id="jaminanModal" tabindex="-1" aria-labelledby="jaminanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jaminanModalLabel">Pilih Jenis Jaminan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Nasabah: <span id="selectedNasabah"></span></p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="redirectToJaminan('bpkb')">BPKB</button>
                        <button class="btn btn-primary" onclick="redirectToJaminan('sertifikat')">SERTIFIKAT</button>
                        <button class="btn btn-primary" onclick="redirectToJaminan('akta')">AKTA</button>
                        <button class="btn btn-primary" onclick="redirectToJaminan('kios')">KIOS</button>
                        <button class="btn btn-primary" onclick="redirectToJaminan('bilyet')">BILYET</button>
                        <button class="btn btn-primary" onclick="redirectToJaminan('manulife')">MANULIFE</button>
                        <button class="btn btn-primary" onclick="redirectToJaminan('bpih')">HAJI BPIH</button>
                        <button class="btn btn-primary" onclick="redirectToJaminan('spph')">HAJI SPPH</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    </script>
</body>

</html>