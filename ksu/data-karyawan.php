<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Proses perubahan status
if (isset($_GET['nik']) && isset($_GET['status'])) {
    $nik = mysqli_real_escape_string($connect, $_GET['nik']);
    $status = mysqli_real_escape_string($connect, $_GET['status']);

    $query = mysqli_query($connect, "UPDATE account SET status='$status' WHERE nik='$nik'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Status user berhasil diubah!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='data-karyawan.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengubah status user!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='data-karyawan.php';
                });
            });
        </script>";
    }
}

// Tambahkan proses hapus user di bagian atas file setelah proses perubahan status
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['nik'])) {
    $nik = mysqli_real_escape_string($connect, $_GET['nik']);

    // Ambil nama file foto sebelum menghapus data
    $query_foto = mysqli_query($connect, "SELECT foto FROM account WHERE nik='$nik'");
    $data_foto = mysqli_fetch_array($query_foto);
    $foto = $data_foto['foto'];

    // Hapus data dari database
    $query = mysqli_query($connect, "DELETE FROM account WHERE nik='$nik'");

    if ($query) {
        // Hapus file foto jika ada dan bukan foto default
        if (!empty($foto) && $foto != 'default.png') {
            $path_foto = "../assets/media/profile/" . $foto;
            if (file_exists($path_foto)) {
                unlink($path_foto);
            }
        }

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data user berhasil dihapus!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='data-karyawan.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menghapus data user!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        </script>";
    }
}

// Proses hapus kantor
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['kd_kantor'])) {
    $kd_kantor = mysqli_real_escape_string($connect, $_GET['kd_kantor']);

    $query = mysqli_query($connect, "DELETE FROM kantor WHERE kd_kantor='$kd_kantor'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data kantor berhasil dihapus!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='index.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menghapus data kantor!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        </script>";
    }
}

// Proses hapus karyawan
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);

    $query = mysqli_query($connect, "DELETE FROM ksu_karyawan WHERE id='$id'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data karyawan berhasil dihapus!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='data-karyawan.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menghapus data karyawan!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        </script>";
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

// Tambahkan fungsi ini di bagian atas file setelah include
function updateMasaKerja($connect)
{
    // Query untuk mengambil semua karyawan
    $query = mysqli_query($connect, "SELECT id, tgl_masuk FROM ksu_karyawan");

    while ($row = mysqli_fetch_assoc($query)) {
        // Hitung masa kerja
        $masa_kerja_query = mysqli_query($connect, "
            SELECT 
                TIMESTAMPDIFF(YEAR, tgl_masuk, CURDATE()) as tahun,
                TIMESTAMPDIFF(MONTH, tgl_masuk, CURDATE()) % 12 as bulan
            FROM ksu_karyawan 
            WHERE id = '{$row['id']}'
        ");

        $masa_kerja = mysqli_fetch_assoc($masa_kerja_query);

        // Format masa kerja untuk disimpan
        $masa_kerja_text = '';
        if ($masa_kerja['tahun'] > 0) {
            $masa_kerja_text .= $masa_kerja['tahun'] . ' tahun ';
        }
        if ($masa_kerja['bulan'] > 0) {
            $masa_kerja_text .= $masa_kerja['bulan'] . ' bulan';
        }
        if (empty($masa_kerja_text)) {
            $masa_kerja_text = 'Baru masuk';
        }

        // Update masa_kerja di database
        mysqli_query($connect, "UPDATE ksu_karyawan SET masa_kerja = '$masa_kerja_text' WHERE id = '{$row['id']}'");
    }
}

// Panggil fungsi update masa kerja setiap kali halaman dimuat
updateMasaKerja($connect);
?>

<?php
// Tambahkan ini di bagian atas file index.php setelah session_start()
if (isset($_SESSION['success_message'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '" . $_SESSION['success_message'] . "',
                showConfirmButton: false,
                timer: 1500
            });
        });
    </script>";
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan</title>
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
                    window.location.href = 'data-karyawan.php?search=' + encodeURIComponent(searchValue);
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Data Karyawan</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="tambahKaryawan()">
                        <i class="bi bi-plus-circle"></i> Tambah Karyawan
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0" style="font-size: 0.9rem;">List Karyawan</h5>
                        <div class="col-md-4 d-flex gap-1">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari nama karyawan...">
                            <button class="btn btn-primary btn-sm" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tambahkan setelah search input dan sebelum tabel -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="d-flex gap-2">
                                <!-- Filter Kantor -->
                                <select class="form-select form-select-sm w-auto" id="filterKantor"
                                    onchange="applyFilters()">
                                    <option value="">Semua Kantor</option>
                                    <?php
                                    $query_kantor = mysqli_query($connect, "SELECT DISTINCT kantor FROM ksu_karyawan ORDER BY kantor");
                                    while ($kantor = mysqli_fetch_assoc($query_kantor)) {
                                        $selected = (isset($_GET['kantor']) && $_GET['kantor'] == $kantor['kantor']) ? 'selected' : '';
                                        echo "<option value='" . $kantor['kantor'] . "' $selected>" . $kantor['kantor'] . "</option>";
                                    }
                                    ?>
                                </select>

                                <!-- Filter Masa Kerja -->
                                <select class="form-select form-select-sm w-auto" id="filterMasaKerja"
                                    onchange="applyFilters()">
                                    <option value="">Semua Masa Kerja</option>
                                    <option value="5"
                                        <?php echo (isset($_GET['masa_kerja']) && $_GET['masa_kerja'] == '5') ? 'selected' : ''; ?>>
                                        > 5 Tahun</option>
                                    <option value="10"
                                        <?php echo (isset($_GET['masa_kerja']) && $_GET['masa_kerja'] == '10') ? 'selected' : ''; ?>>
                                        > 10 Tahun</option>
                                    <option value="15"
                                        <?php echo (isset($_GET['masa_kerja']) && $_GET['masa_kerja'] == '15') ? 'selected' : ''; ?>>
                                        > 15 Tahun</option>
                                    <option value="20"
                                        <?php echo (isset($_GET['masa_kerja']) && $_GET['masa_kerja'] == '20') ? 'selected' : ''; ?>>
                                        > 20 Tahun</option>
                                    <option value="25"
                                        <?php echo (isset($_GET['masa_kerja']) && $_GET['masa_kerja'] == '25') ? 'selected' : ''; ?>>
                                        > 25 Tahun</option>
                                    <option value="30"
                                        <?php echo (isset($_GET['masa_kerja']) && $_GET['masa_kerja'] == '30') ? 'selected' : ''; ?>>
                                        > 30 Tahun</option>
                                </select>

                                <!-- Tambahkan tombol filter milestone -->
                                <button class="btn btn-warning" onclick="filterMilestone()">
                                    <i class="bi bi-star-fill"></i> Tampilkan Milestone
                                </button>

                                <!-- Tombol Reset -->
                                <button class="btn btn-secondary" onclick="resetFilters()">
                                    <i class="bi bi-x-circle"></i> Reset Filter
                                </button>

                                <!-- Ubah tombol download menjadi fungsi JavaScript -->
                                <button class="btn btn-success" onclick="downloadData()">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Jabatan</th>
                                    <th>Kantor</th>
                                    <th>Masa Kerja</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                $limit = 50;
                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $limit;

                                $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                $where = [];
                                if (!empty($search)) {
                                    $where[] = "(nama LIKE '%$search%' OR nik LIKE '%$search%')";
                                }

                                if (isset($_GET['kantor']) && !empty($_GET['kantor'])) {
                                    $kantor = mysqli_real_escape_string($connect, $_GET['kantor']);
                                    $where[] = "kantor = '$kantor'";
                                }

                                if (isset($_GET['masa_kerja']) && !empty($_GET['masa_kerja'])) {
                                    $masa_kerja_tahun = (int)$_GET['masa_kerja'];
                                    $where[] = "TIMESTAMPDIFF(YEAR, tgl_masuk, CURDATE()) > $masa_kerja_tahun";
                                }

                                // Tambahkan filter untuk milestone
                                if (isset($_GET['milestone']) && $_GET['milestone'] == '1') {
                                    $milestone_years = implode(',', [5, 10, 15, 20, 25, 30, 35]);
                                    $where[] = "TIMESTAMPDIFF(YEAR, tgl_masuk, CURDATE()) IN ($milestone_years)";
                                    $where[] = "TIMESTAMPDIFF(MONTH, tgl_masuk, CURDATE()) % 12 < 12";
                                }

                                $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

                                $query = mysqli_query($connect, "SELECT *, 
                                    TIMESTAMPDIFF(YEAR, tgl_masuk, CURDATE()) as masa_kerja_tahun 
                                    FROM ksu_karyawan 
                                    $where_clause 
                                    ORDER BY id ASC 
                                    LIMIT $start, $limit");

                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM ksu_karyawan $where_clause"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    // Hitung masa kerja dalam tahun dan bulan
                                    $tgl_masuk = new DateTime($row['tgl_masuk']);
                                    $today = new DateTime();
                                    $interval = $tgl_masuk->diff($today);
                                    $tahun = $interval->y;
                                    $bulan = $interval->m;

                                    // Cek apakah masa kerja tepat di tahun milestone dan tidak melebihi 1 tahun
                                    $milestone_years = [5, 10, 15, 20, 25, 30, 35];
                                    $highlight_row = in_array($tahun, $milestone_years) && $bulan < 12;

                                    // Gunakan class table-warning yang lebih langsung
                                    $row_class = $highlight_row ? 'table-warning' : '';

                                    echo "<tr class='$row_class'>
                                        <td>$no</td>
                                        <td>{$row['nama']}</td>
                                        <td>{$row['nik']}</td>
                                        <td>{$row['jabatan']}</td>
                                        <td>{$row['kantor']}</td>
                                        <td>" . ($highlight_row ? "<strong>{$row['masa_kerja']}</strong>" : $row['masa_kerja']) . "</td>
                                        <td>
                                            <div class='d-flex gap-1'>
                                                <button type='button' class='btn btn-sm btn-primary btn-icon' onclick='viewKaryawan(\"{$row['id']}\")' title='Detail'>
                                                    <i class='bi bi-eye'></i>
                                                </button>
                                                <button type='button' class='btn btn-sm btn-info btn-icon' onclick='slipGaji(\"{$row['id']}\")' title='Slip Gaji'>
                                                    <i class='bi bi-cash-stack'></i>
                                                </button>
                                                <button type='button' class='btn btn-sm btn-warning btn-icon' onclick='editKaryawan(\"{$row['id']}\")' title='Edit'>
                                                    <i class='bi bi-pencil'></i>
                                                </button>
                                                <button type='button' class='btn btn-sm btn-danger btn-icon' onclick='deleteKaryawan(\"{$row['id']}\")' title='Hapus'>
                                                    <i class='bi bi-trash'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>"
                                        tabindex="-1">Previous</a>
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
                                    echo '<li class="page-item ' . ($page == $i ? 'AKTIF' : '') . '">
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

        function tambahKaryawan() {
            window.location.href = 'tambah-karyawan.php';
        }

        function editKaryawan(id) {
            window.location.href = 'edit-karyawan.php?id=' + id;
        }

        function deleteKaryawan(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data karyawan akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'data-karyawan.php?action=delete&id=' + id;
                }
            });
        }

        function viewKaryawan(id) {
            window.location.href = 'detail-karyawan.php?id=' + id;
        }

        function slipGaji(id) {
            window.location.href = 'slip-gaji.php?id=' + id;
        }

        // Tambahkan script JavaScript untuk handling filter
        function applyFilters() {
            let currentUrl = new URL(window.location.href);
            let searchParams = currentUrl.searchParams;

            const kantor = document.getElementById('filterKantor').value;
            const masaKerja = document.getElementById('filterMasaKerja').value;
            const searchValue = document.getElementById('searchInput').value;

            // Reset pagination dan milestone
            searchParams.delete('page');
            searchParams.delete('milestone');

            if (kantor) {
                searchParams.set('kantor', kantor);
            } else {
                searchParams.delete('kantor');
            }

            if (masaKerja) {
                searchParams.set('masa_kerja', masaKerja);
            } else {
                searchParams.delete('masa_kerja');
            }

            if (searchValue) {
                searchParams.set('search', searchValue);
            } else {
                searchParams.delete('search');
            }

            window.location.href = currentUrl.pathname + '?' + searchParams.toString();
        }

        function resetFilters() {
            window.location.href = 'data-karyawan.php';
        }

        // Tambahkan fungsi untuk filter milestone
        function filterMilestone() {
            let currentUrl = new URL(window.location.href);
            let searchParams = currentUrl.searchParams;

            // Reset pagination
            searchParams.delete('page');

            // Set parameter milestone
            searchParams.set('milestone', '1');

            // Hapus filter masa kerja lain jika ada
            searchParams.delete('masa_kerja');

            // Redirect dengan filter baru
            window.location.href = currentUrl.pathname + '?' + searchParams.toString();
        }

        // Tambahkan fungsi JavaScript untuk download
        function downloadData() {
            let currentUrl = new URL(window.location.href);
            let searchParams = currentUrl.searchParams;
            let downloadUrl = 'export-excel-karyawan.php';

            // Ambil semua parameter yang aktif
            let params = [];

            // Parameter search
            if (searchParams.has('search')) {
                params.push('search=' + searchParams.get('search'));
            }

            // Parameter kantor
            if (searchParams.has('kantor')) {
                params.push('kantor=' + searchParams.get('kantor'));
            }

            // Parameter masa_kerja
            if (searchParams.has('masa_kerja')) {
                params.push('masa_kerja=' + searchParams.get('masa_kerja'));
            }

            // Parameter milestone
            if (searchParams.has('milestone')) {
                params.push('milestone=' + searchParams.get('milestone'));
            }

            // Gabungkan semua parameter
            if (params.length > 0) {
                downloadUrl += '?' + params.join('&');
            }

            // Redirect ke file export dengan parameter yang sama
            window.location.href = downloadUrl;
        }
    </script>
</body>

</html>