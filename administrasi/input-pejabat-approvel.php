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
        $_SESSION['kantor'] = $data['kantor'];

        header("Location: ../dashboard.php");
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}

// Tambahkan fungsi untuk generate kode staff otomatis
function generateKodeStaff($kodeKantor)
{
    $numeric1 = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $numeric2 = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    return "$numeric1-$numeric2-NSB-$kodeKantor";
}

// Tambahkan endpoint untuk update PK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_pk') {
    $response = ['success' => false, 'message' => ''];

    if (isset($_POST['kode']) && isset($_POST['isi'])) {
        $kode = mysqli_real_escape_string($connect, $_POST['kode']);
        $isi = mysqli_real_escape_string($connect, $_POST['isi']);

        $query = "UPDATE pkno1 SET isi = '$isi' WHERE kode = '$kode'";

        if (mysqli_query($connect, $query)) {
            $response['success'] = true;
            $response['message'] = 'PK berhasil diperbarui';
        } else {
            $response['message'] = 'Gagal memperbarui PK: ' . mysqli_error($connect);
        }
    } else {
        $response['message'] = 'Data tidak lengkap';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Proses penambahan staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_staff') {
    $response = ['success' => false, 'message' => ''];

    try {
        if (isset($_POST['nama']) && isset($_POST['jabatan']) && isset($_POST['kantor'])) {
            $nama = mysqli_real_escape_string($connect, $_POST['nama']);
            $jabatan = mysqli_real_escape_string($connect, $_POST['jabatan']);
            $posisi = isset($_POST['posisi']) ? mysqli_real_escape_string($connect, $_POST['posisi']) : '';
            $gelar = isset($_POST['gelar']) ? mysqli_real_escape_string($connect, $_POST['gelar']) : '';
            $ket_gelar = isset($_POST['ket_gelar']) ? mysqli_real_escape_string($connect, $_POST['ket_gelar']) : '';
            $kantor = mysqli_real_escape_string($connect, $_POST['kantor']);

            // Generate kode staff
            $kd_staff = generateKodeStaff($kantor);

            // Debug log
            error_log("Attempting to insert staff: $kd_staff, $nama, $jabatan, $posisi, $gelar, $ket_gelar, $kantor");

            $query = "INSERT INTO staff (kd_staff, nama, jabatan, posisi, gelar, ket_gelar, kantor) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($connect, $query);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . mysqli_error($connect));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "sssssss",
                $kd_staff,
                $nama,
                $jabatan,
                $posisi,
                $gelar,
                $ket_gelar,
                $kantor
            );

            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Data staff berhasil ditambahkan';
                error_log("Staff inserted successfully");
            } else {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Data tidak lengkap");
        }
    } catch (Exception $e) {
        error_log("Error adding staff: " . $e->getMessage());
        $response['message'] = 'Gagal menambahkan data: ' . $e->getMessage();
    }

    // Pastikan tidak ada output sebelum JSON
    if (ob_get_length()) ob_clean();

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Fungsi untuk mengecek posisi yang belum terisi
function checkMissingPositions($connect, $kantor)
{
    $required_positions = [
        'Penanggung Jawab' . $kantor,
        'Direksi' . $kantor,
        'Customer Services' . $kantor,
        'Admin Kredit' . $kantor,
        'Kabid. Oprasional' . $kantor
    ];

    $missing_positions = [];

    foreach ($required_positions as $position) {
        $query = mysqli_query($connect, "SELECT COUNT(*) as count FROM staff WHERE kantor='$kantor' AND posisi='$position'");
        $result = mysqli_fetch_assoc($query);

        if ($result['count'] == 0) {
            $missing_positions[] = $position;
        }
    }

    return $missing_positions;
}

// Dapatkan kantor dari session
$kantor = $_SESSION['kantor'];
$missing_positions = checkMissingPositions($connect, $kantor);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Pejabat Approvel</title>
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
                window.location.href = 'input-pejabat-approvel.php?search=' + encodeURIComponent(searchValue);
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
                <h2>Input Pejabat Approvel</h2>


                <button type="button" class="btn btn-primary" onclick="showAddStaffModal()">
                    <i class="bi bi-plus-circle"></i> Tambah Data
                </button>
            </div>
            <div class="alert <?php echo empty($missing_positions) ? 'alert-success' : 'alert-warning'; ?> mb-4"
                role="alert">
                <h5 class="alert-heading">
                    <i
                        class="bi <?php echo empty($missing_positions) ? 'bi-check-circle' : 'bi-exclamation-triangle'; ?>"></i>
                    Status Pejabat Approvel:
                </h5>
                <?php if (empty($missing_positions)): ?>
                <p class="mb-0">Semua posisi pejabat approvel telah terisi lengkap.</p>
                <?php else: ?>
                <p>Beberapa posisi pejabat approvel belum terisi:</p>
                <ul class="mb-0">
                    <?php foreach ($missing_positions as $position): ?>
                    <li><?php echo $position; ?></li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <p class="mb-0">
                    <strong>Panduan:</strong> Untuk melengkapi data, silakan:
                <ol class="mb-0">
                    <li>Klik tombol "Tambah Data" untuk menambahkan pejabat baru, atau</li>
                    <li>Pilih staff yang sudah ada dan atur posisinya dengan mengklik tombol <button
                            class="btn btn-sm btn-primary" disabled><i class="bi bi-person-gear"></i></button> pada
                        kolom Aksi.</li>
                </ol>
                </p>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Pejabat Approvel</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari Kode Staff atau Nama...">
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
                                    <th>Kode Staff</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Posisi</th>
                                    <th>Gelar</th>
                                    <th>Kantor</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                $limit = 50;
                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $limit;

                                $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                $username = $_SESSION['username'];

                                // Modifikasi query dengan JOIN
                                $where = "WHERE a.username = '$username'";
                                if (!empty($search)) {
                                    $where .= " AND (s.kd_staff LIKE '%$search%' OR s.nama LIKE '%$search%')";
                                }

                                $query = mysqli_query($connect, "SELECT DISTINCT s.* 
                                                               FROM staff s 
                                                               JOIN account a ON s.kantor = a.kantor 
                                                               $where 
                                                               ORDER BY s.kd_staff DESC 
                                                               LIMIT $start, $limit");

                                $total_query = mysqli_query($connect, "SELECT COUNT(DISTINCT s.kd_staff) as total 
                                                                     FROM staff s 
                                                                     JOIN account a ON s.kantor = a.kantor 
                                                                     $where");
                                $total_data = mysqli_fetch_array($total_query);
                                $total_records = $total_data['total'];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['kd_staff']}</td>
                                        <td>{$row['nama']}</td>
                                        <td>{$row['jabatan']}</td>
                                        <td>{$row['posisi']}</td>
                                        <td>{$row['gelar']}</td>
                                        <td>{$row['kantor']}</td>
                                        <td>
                                            <div class='d-flex gap-1 justify-content-center'>
                                            <div class='btn-group'>
                                            <button type='button' class='btn btn-sm btn-primary dropdown-toggle' data-bs-toggle='dropdown'>
                                            <i class='bi bi-person-gear'></i>
                                            </button>
                                            <ul class='dropdown-menu dropdown-menu-end'>
                                            <li><a class='dropdown-item' href='#' onclick='updatePosition(\"{$row['kd_staff']}\", \"Customer Services\")'>Customer Services</a></li>
                                            <li><a class='dropdown-item' href='#' onclick='updatePosition(\"{$row['kd_staff']}\", \"Penanggung Jawab\")'>Penanggung Jawab</a></li>
                                            <li><a class='dropdown-item' href='#' onclick='updatePosition(\"{$row['kd_staff']}\", \"Kabid. Oprasional\")'>Kabid. Oprasional</a></li>
                                            <li><a class='dropdown-item' href='#' onclick='updatePosition(\"{$row['kd_staff']}\", \"Admin Kredit\")'>Admin Kredit</a></li>
                                            <li><a class='dropdown-item' href='#' onclick='updatePosition(\"{$row['kd_staff']}\", \"Direksi\")'>Direksi</a></li>
                                            <li><hr class='dropdown-divider'></li>
                                            <li><a class='dropdown-item' href='#' onclick='updatePosition(\"{$row['kd_staff']}\", \"\")'>Tidak Ada</a></li>
                                            </ul>
                                            </div>
                                            <button type='button' class='btn btn-sm btn-warning' onclick='editStaff(\"{$row['kd_staff']}\", \"{$row['nama']}\", \"{$row['jabatan']}\", \"{$row['gelar']}\", \"{$row['kantor']}\")'>
                                                <i class='bi bi-pencil'></i>
                                            </button>
                                                <button type='button' class='btn btn-sm btn-danger' onclick='deleteStaff(\"{$row['kd_staff']}\")'>
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
                            <ul class="pagination justify-content-center">
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

            <!-- Pindahkan card PK ke dalam container-fluid yang sama -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data PK</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-compact">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Isi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $username = $_SESSION['username'];
                                $query_pk = mysqli_query($connect, "SELECT p.* 
                                                                  FROM pkno1 p 
                                                                  WHERE p.kode = (
                                                                      SELECT kantor 
                                                                      FROM account 
                                                                      WHERE username = '$username' 
                                                                      LIMIT 1
                                                                  )");

                                if (mysqli_num_rows($query_pk) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_array($query_pk)) {
                                        // Escape data untuk keamanan JavaScript dan encode untuk JSON
                                        $kode = json_encode($row['kode']);
                                        $isi = json_encode($row['isi']);

                                        echo "<tr>
                                            <td>$no</td>
                                            <td>{$row['kode']}</td>
                                            <td>{$row['isi']}</td>
                                            <td>
                                                <div class='d-flex gap-1 justify-content-center'>
                                                    <button type='button' class='btn btn-sm btn-warning' 
                                                        onclick='editPK({$kode}, {$isi})'>
                                                        <i class='bi bi-pencil'></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>Tidak ada data PK untuk kantor ini</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editStaffForm">
                        <input type="hidden" id="edit_kd_staff" name="kd_staff">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <select class="form-control" id="edit_jabatan" name="jabatan" required>
                                <option value="">Pilih Jabatan</option>
                                <option value="Direktur Utama">Direktur Utama</option>
                                <option value="Customer Services">Customer Services</option>
                                <option value="Kabid. Oprasional">Kabid. Oprasional</option>
                                <option value="Kepala Cabang">Kepala Cabang</option>
                                <option value="Adm Kredit">Adm Kredit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gelar</label>
                            <input type="text" class="form-control" id="edit_gelar" name="gelar">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kantor</label>
                            <input type="text" class="form-control" id="edit_kantor" name="kantor" readonly required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveEdit()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan modal untuk edit PK -->
    <div class="modal fade" id="editPKModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit PK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editPKForm">
                        <input type="hidden" id="edit_pk_kode" name="kode">
                        <div class="mb-3">
                            <label class="form-label">Isi PK</label>
                            <textarea class="form-control" id="edit_pk_isi" name="isi" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="savePKEdit()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Staff -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pejabat Approvel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" id="add_nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <select class="form-control" id="add_jabatan" name="jabatan" required>
                                <option value="">Pilih Jabatan</option>
                                <option value="Direktur Utama">Direktur Utama</option>
                                <option value="Customer Services">Customer Services</option>
                                <option value="Kabid. Oprasional">Kabid. Oprasional</option>
                                <option value="Kepala Cabang">Kepala Cabang</option>
                                <option value="Adm Kredit">Adm Kredit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Posisi</label>
                            <input type="text" class="form-control" id="add_posisi" name="posisi" value="Tidak Ada"
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gelar</label>
                            <input type="text" class="form-control" id="add_gelar" name="gelar">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan Gelar</label>
                            <select class="form-control" id="add_ket_gelar" name="ket_gelar">
                                <option value="">Pilih Gelar</option>
                                <option value="Ahli Madya">Ahli Madya</option>
                                <option value="Diploma I">Diploma I</option>
                                <option value="Diploma II">Diploma II</option>
                                <option value="Diploma III">Diploma III</option>
                                <option value="Sarjana Komputer">Sarjana Komputer</option>
                                <option value="Sarjana Hukum">Sarjana Hukum</option>
                                <option value="Sarjana Ekonomi">Sarjana Ekonomi</option>
                                <option value="Sarjana Teknik">Sarjana Teknik</option>
                                <option value="Sarjana Pendidikan">Sarjana Pendidikan</option>
                                <option value="Sarjana Sosial">Sarjana Sosial</option>
                                <option value="Sarjana Psikologi">Sarjana Psikologi</option>
                                <option value="Magister Hukum">Magister Hukum</option>
                                <option value="Magister Manajemen">Magister Manajemen</option>
                                <option value="Magister Komputer">Magister Komputer</option>
                                <option value="Magister Sains">Magister Sains</option>
                                <option value="Magister Teknik">Magister Teknik</option>
                                <option value="Doktor">Doktor</option>
                                <option value="Profesor">Profesor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kantor</label>
                            <select class="form-control" id="add_kantor" name="kantor" required>
                                <option value="">Pilih Kantor</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="300">300</option>
                                <option value="400">400</option>
                                <option value="500">500</option>
                                <option value="600">600</option>
                                <option value="700">700</option>
                                <option value="800">800</option>
                                <option value="900">900</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewStaff()">Simpan</button>
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

    function deleteStaff(kdStaff) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data staff akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Kirim request Ajax untuk menghapus data
                fetch('delete-staff.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'kd_staff=' + encodeURIComponent(kdStaff)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Terhapus!',
                                'Data staff berhasil dihapus.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menghapus data.',
                                'error'
                            );
                        }
                    });
            }
        });
    }

    function updatePosition(kdStaff, newPosition) {
        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: `Apakah Anda yakin ingin mengubah posisi menjadi ${newPosition}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, ubah!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('update-staff.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `kd_staff=${encodeURIComponent(kdStaff)}&position=${encodeURIComponent(newPosition)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Berhasil!',
                                'Posisi berhasil diperbarui.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Gagal!',
                                data.message || 'Terjadi kesalahan saat mengubah posisi.',
                                'error'
                            );
                        }
                    });
            }
        });
    }

    function editStaff(kdStaff, nama, jabatan, gelar, kantor) {
        document.getElementById('edit_kd_staff').value = kdStaff;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_jabatan').value = jabatan;
        document.getElementById('edit_gelar').value = gelar;
        document.getElementById('edit_kantor').value = kantor;

        new bootstrap.Modal(document.getElementById('editStaffModal')).show();
    }

    function saveEdit() {
        const formData = new FormData(document.getElementById('editStaffForm'));

        fetch('edit-staff.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berhasil!',
                        'Data staff berhasil diperbarui.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Gagal!',
                        data.message || 'Terjadi kesalahan saat memperbarui data.',
                        'error'
                    );
                }
            });
    }

    // Update fungsi JavaScript updatePK()
    function updatePK(kode, isi) {
        fetch('input-pejabat-approvel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_pk&kode=${encodeURIComponent(kode)}&isi=${encodeURIComponent(isi)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berhasil!',
                        'PK berhasil diperbarui.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Gagal!',
                        data.message || 'Terjadi kesalahan saat mengubah PK.',
                        'error'
                    );
                }
            });
    }

    function editPK(kode, isi) {
        // Debug log
        console.log('Edit PK:', kode, isi);

        // Set nilai ke form
        document.getElementById('edit_pk_kode').value = kode;
        document.getElementById('edit_pk_isi').value = isi;

        // Tampilkan modal menggunakan Bootstrap 5
        let editPKModal = new bootstrap.Modal(document.getElementById('editPKModal'));
        editPKModal.show();
    }

    function savePKEdit() {
        const kode = document.getElementById('edit_pk_kode').value;
        const isi = document.getElementById('edit_pk_isi').value;

        // Debug log
        console.log('Saving PK:', kode, isi);

        updatePK(kode, isi);
    }

    // Tambahkan event listener untuk debugging
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded');

        // Log setiap klik tombol edit
        document.querySelectorAll('[onclick^="editPK"]').forEach(button => {
            button.addEventListener('click', function(e) {
                console.log('Edit button clicked:', e);
            });
        });
    });

    function showAddStaffModal() {
        document.getElementById('addStaffForm').reset();
        new bootstrap.Modal(document.getElementById('addStaffModal')).show();
    }

    function saveNewStaff() {
        const form = document.getElementById('addStaffForm');

        // Validasi form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'add_staff');

        // Debug log
        console.log('Sending form data:', Object.fromEntries(formData));

        fetch('input-pejabat-approvel.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text().then(text => {
                    console.log('Raw response:', text); // Debug log
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response text:', text);
                        throw new Error('Invalid response format');
                    }
                });
            })
            .then(data => {
                console.log('Parsed response:', data); // Debug log

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message
                });
            });
    }
    </script>
</body>

</html>