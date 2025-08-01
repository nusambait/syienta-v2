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
    <title>Data Kantor</title>
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
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Data Kantor</h2>
                <button class="btn btn-primary" onclick="tambahKantor()">
                    <i class="bi bi-plus-circle"></i> Tambah Kantor
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0" style="font-size: 0.9rem;">Data Kantor</h5>
                        <div class="col-md-4 d-flex gap-1">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama kantor...">
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
                                    <th>Kode Kantor</th>
                                    <th>Nama Kantor</th>
                                    <th>Singkatan</th>
                                    <th>Alamat</th>
                                    <th>Kecamatan</th>
                                    <th>Kabupaten</th>
                                    <th>Status</th>
                                    <th>Nama Perusahaan</th>
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
                                    $where = "WHERE nm_kantor LIKE '%$search%' OR kd_kantor LIKE '%$search%'";
                                }

                                $query = mysqli_query($connect, "SELECT * FROM kantor $where ORDER BY kd_kantor ASC LIMIT $start, $limit");
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM kantor $where"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    $status_badge = $row['status'] == 'AKTIF' ? 'bg-success' : 'bg-danger';
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['kd_kantor']}</td>
                                        <td>{$row['nm_kantor']}</td>
                                        <td>{$row['short']}</td>
                                        <td>{$row['almt']}</td>
                                        <td>{$row['kec']}</td>
                                        <td>{$row['kab']}</td>
                                        <td><span class='badge $status_badge'>{$row['status']}</span></td>
                                        <td>{$row['nm_perusahaan']}</td>
                                        <td>
                                            <button type='button' class='btn btn-sm btn-warning' onclick='editKantor(\"{$row['kd_kantor']}\")'>
                                                <i class='bi bi-pencil'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-danger' onclick='deleteKantor(\"{$row['kd_kantor']}\")'>
                                                <i class='bi bi-trash'></i>
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

    function tambahKantor() {
        window.location.href = 'tambah-kantor.php';
    }

    function editKantor(kd_kantor) {
        window.location.href = 'edit-kantor.php?kd_kantor=' + kd_kantor;
    }

    function viewKantor(kd_kantor) {
        window.location.href = 'view-kantor.php?kd_kantor=' + kd_kantor;
    }

    function deleteKantor(kd_kantor) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data kantor akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?action=delete&kd_kantor=' + kd_kantor;
            }
        });
    }
    </script>
</body>

</html>