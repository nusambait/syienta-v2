<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

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
                    text: 'Gagal mengubah status user!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='index.php';
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
            $path_foto = "../../assets/media/profile/" . $foto;
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
                    text: 'Gagal menghapus data user!',
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

        header("Location: ../../dashboard.php");
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}
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
    <title>Manajemen Users</title>
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
                window.location.href = 'index.php?search=' + encodeURIComponent(searchValue);
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
    <style>
    .table-compact {
        font-size: 0.75rem;
        line-height: 1.2;
    }

    .table-compact th,
    .table-compact td {
        padding: 0.3rem 0.4rem;
        vertical-align: middle;
    }

    .btn-sm {
        padding: 0.15rem 0.3rem;
        font-size: 0.7rem;
        line-height: 1;
    }

    .card-body {
        padding: 0.6rem;
    }

    .form-control {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
        height: 1.8rem;
        line-height: 1;
    }

    .badge {
        font-size: 0.7rem;
        padding: 0.2em 0.4em;
    }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Manajemen Users</h2>
                <button class="btn btn-primary" onclick="tambahUser()">
                    <i class="bi bi-plus-circle"></i> Tambah User
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <?php
                        // Hitung total user
                        $total_user_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM account");
                        $total_user = mysqli_fetch_assoc($total_user_query)['total'];

                        // Hitung user aktif
                        $user_aktif_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM account WHERE status='AKTIF'");
                        $user_aktif = mysqli_fetch_assoc($user_aktif_query)['total'];
                        ?>
                        <h5 class="card-title mb-0 py-1" style="font-size: 0.9rem;">
                            Data Users | Total User: <?php echo $total_user; ?> | User Aktif: <?php echo $user_aktif; ?>
                        </h5>
                        <div class="col-md-4 d-flex gap-1">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari username atau nama...">
                            <button class="btn btn-primary btn-sm" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-compact">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Kantor</th>
                                    <th>Kode AO</th>
                                    <th>Key App</th>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                $limit = 50;
                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $limit;

                                $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                $where = '';
                                if (!empty($search)) {
                                    $where = "WHERE username LIKE '%$search%' OR nama LIKE '%$search%' OR nik LIKE '%$search%'";
                                }

                                $query = mysqli_query($connect, "SELECT * FROM account $where ORDER BY nama ASC LIMIT $start, $limit");
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM account $where"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    $status_badge = $row['status'] == 'AKTIF' ? 'bg-success' : 'bg-danger';
                                    $status_text = $row['status'] == 'AKTIF' ? 'AKTIF' : 'NON-AKTIF';
                                    $toggle_status = $row['status'] == 'AKTIF' ? 'NON-AKTIF' : 'AKTIF';
                                    $toggle_btn_class = $row['status'] == 'AKTIF' ? 'btn-danger' : 'btn-success';
                                    $toggle_icon = $row['status'] == 'AKTIF' ? 'bi-toggle-off' : 'bi-toggle-on';

                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['nik']}</td>
                                        <td>{$row['username']}</td>
                                        <td>{$row['nama']}</td>
                                        <td>{$row['jabatan']}</td>
                                        <td>{$row['kantor']}</td>
                                        <td>{$row['kd_ao']}</td>
                                        <td>{$row['key_app']}</td>
                                        <td>{$row['id']}</td>
                                        <td><span class='badge $status_badge'>$status_text</span></td>
                                        <td>
                                            <button type='button' class='btn btn-sm btn-warning' onclick='editUser(\"{$row['nik']}\")'>
                                                <i class='bi bi-pencil'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-info' onclick='viewUser(\"{$row['nik']}\")'>
                                                <i class='bi bi-eye'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-danger' onclick='deleteUser(\"{$row['nik']}\")'>
                                                <i class='bi bi-trash'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm $toggle_btn_class' onclick='toggleStatus(\"{$row['nik']}\", \"$toggle_status\")'>
                                                <i class='bi $toggle_icon'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-secondary' onclick='copyCredentials(\"{$row['username']}\", \"{$row['password']}\")'>
                                                <i class='bi bi-clipboard'></i>
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
                            <ul class="pagination pagination-sm justify-content-center">
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

    function tambahUser() {
        window.location.href = 'tambah-user.php';
    }

    function editUser(nik) {
        window.location.href = 'edit-user.php?nik=' + nik;
    }

    function viewUser(nik) {
        window.location.href = 'view-user.php?nik=' + nik;
    }

    function deleteUser(nik) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data user akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?action=delete&nik=' + nik;
            }
        });
    }

    function toggleStatus(nik, newStatus) {
        let statusText = newStatus === 'AKTIF' ? 'mengaktifkan' : 'menonaktifkan';
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Anda akan ${statusText} user ini!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, ubah!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `index.php?nik=${nik}&status=${newStatus}`;
            }
        });
    }

    function copyCredentials(username, password) {
        const textToCopy = `Username: ${username}\nPassword: ${password}`;

        navigator.clipboard.writeText(textToCopy)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disalin!',
                    text: 'Username dan password berhasil disalin ke clipboard',
                    showConfirmButton: false,
                    timer: 1500
                });
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyalin',
                    text: 'Tidak dapat menyalin ke clipboard: ' + err,
                    showConfirmButton: true
                });
            });
    }
    </script>
</body>

</html>