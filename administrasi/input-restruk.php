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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Restrukturisasi</title>
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

        function editRestruk(nik) {
            selectedNik = nik;
            let restrukModal = new bootstrap.Modal(document.getElementById('restrukModal'));
            restrukModal.show();
        }

        function redirectToRestruk(type) {
            if (selectedNik) {
                window.location.href = `form-restruk-${type}.php?nik=${selectedNik}`;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchButton = document.getElementById('searchButton');
            const searchInput = document.getElementById('searchInput');

            if (searchButton && searchInput) {
                // Fungsi pencarian
                function performSearch() {
                    let searchValue = searchInput.value;
                    window.location.href = 'input-restruk.php?search=' + encodeURIComponent(searchValue);
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
            <h2 class="mb-4">Input Restrukturisasi</h2>

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
                                        <td class='d-flex justify-content-center'>
                                            <button type='button' class='btn btn-sm btn-success' onclick='editRestruk(\"{$row['nik']}\")'>
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

            <!-- Card untuk menampilkan data restrukturisasi -->
            <div class="row mt-4">
                <!-- Card untuk data restruk (CBS) -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header py-2">
                            <h5 class="card-title mb-0 py-3 fs-6">Data Restrukturisasi CBS</h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th class="py-1" width="5%">No</th>
                                            <th class="py-1" width="15%">NIK</th>
                                            <th class="py-1" width="20%">Nama</th>
                                            <th class="py-1" width="10%">Untuk</th>
                                            <th class="py-1" width="15%">No. Reg Lama</th>
                                            <th class="py-1" width="15%">No. Reg Baru</th>
                                            <th class="py-1" width="20%">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Query untuk data restruk
                                        $search_condition = !empty($search) ? "WHERE nik LIKE '%$search%' OR nama LIKE '%$search%'" : "";
                                        $restruk_query = mysqli_query($connect, "SELECT * FROM restruk $search_condition ORDER BY id DESC LIMIT 5");

                                        $no_restruk = 1;
                                        if (mysqli_num_rows($restruk_query) > 0) {
                                            while ($restruk_row = mysqli_fetch_array($restruk_query)) {
                                                echo "<tr>
                                                    <td class='text-center py-1'>$no_restruk</td>
                                                    <td class='py-1 text-center'>
                                                        <div class='d-flex flex-column gap-1'>
                                                            <div>{$restruk_row['nik']}</div>
                                                            <a href='../../../nusamba_api/production/system/dok_adendum_rest_copy.php?nik={$restruk_row['nik']}&noreg_lama={$restruk_row['noreg_lama']}&noreg_baru={$restruk_row['noreg_baru']}' class='btn btn-sm btn-primary mb-1'>PK Res Addendum</a>
                                                            <a href='../../../nusamba_api/production/system/dok_adendum_rest_blt.php?nik={$restruk_row['nik']}&noreg_lama={$restruk_row['noreg_lama']}&noreg_baru={$restruk_row['noreg_baru']}' class='btn btn-sm btn-info'>PK Res Addendum Bullet</a>
                                                        </div>
                                                    </td>
                                                    <td class='py-1'>{$restruk_row['nama']}</td>
                                                    <td class='py-1'>{$restruk_row['untuk']}</td>
                                                    <td class='py-1'>{$restruk_row['noreg_lama']}</td>
                                                    <td class='py-1'>{$restruk_row['noreg_baru']}</td>
                                                    <td class='py-1 small'>{$restruk_row['ket']}</td>
                                                </tr>";
                                                $no_restruk++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center py-2'>Tidak ada data restrukturisasi CBS</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card untuk data restruk_2 (Droping Ulang) -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header py-2">
                            <h5 class="card-title mb-0 py-3 fs-6">Data Restrukturisasi Droping Ulang</h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th class="py-1" width="5%">No</th>
                                            <th class="py-1" width="12%">NIK</th>
                                            <th class="py-1" width="15%">Nama</th>
                                            <th class="py-1" width="8%">Untuk</th>
                                            <th class="py-1" width="12%">No. Reg Awal</th>
                                            <th class="py-1" width="12%">Restruk 1</th>
                                            <th class="py-1" width="12%">Restruk 2</th>
                                            <th class="py-1" width="12%">Jatuh Tempo</th>
                                            <th class="py-1" width="12%">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Query untuk data restruk_2
                                        $search_condition_2 = !empty($search) ? "WHERE nik LIKE '%$search%' OR nama LIKE '%$search%'" : "";
                                        $restruk2_query = mysqli_query($connect, "SELECT * FROM restruk_2 $search_condition_2 ORDER BY id DESC LIMIT 5");

                                        $no_restruk2 = 1;
                                        if (mysqli_num_rows($restruk2_query) > 0) {
                                            while ($restruk2_row = mysqli_fetch_array($restruk2_query)) {
                                                echo "<tr>
                                                    <td class='text-center py-1'>$no_restruk2</td>
                                                    <td class='py-1 text-center'>
                                                        <div class='d-flex flex-column gap-1'>
                                                            <div>{$restruk2_row['nik']}</div>
                                                            <a href='../../../nusamba_api/production/system/dok_adendum_rest_2.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&type=noncbs' class='btn btn-sm btn-primary mb-1' target='_blank'>Addendum II</a>
                                                            
                                                            <div class='dropdown'>
                                                                <button class='btn btn-sm btn-info dropdown-toggle w-100' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                                                    Jatuh Tempo
                                                                </button>
                                                                <ul class='dropdown-menu'>
                                                                    <li><a class='dropdown-item' href='../../../nusamba_api/production/system/dok_perjanjiankredit_res.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&type=noncbs' target='_blank'>PK Res Jatuh Tempo V1</a></li>
                                                                    <li><a class='dropdown-item' href='../../../nusamba_api/production/system/dok_perjanjiankredit_res_2.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&noreg_jatuh_tempo={$restruk2_row['noreg_jatuh_tempo']}&type=noncbs' target='_blank'>PK Res Jatuh Tempo V2 (Non Reguler)</a></li>
                                                                    <li><a class='dropdown-item' href='../../../nusamba_api/production/system/dok_perjanjiankredit_res3.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&noreg_jatuh_tempo={$restruk2_row['noreg_jatuh_tempo']}&type=noncbs' target='_blank'>PK Res Jatuh Tempo V2 (Reguler)</a></li>
                                                                    <li><a class='dropdown-item' href='../../../nusamba_api/production/system/dok_perjanjiankredit_resbullet.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&noreg_jatuh_tempo={$restruk2_row['noreg_jatuh_tempo']}&type=noncbs' target='_blank'>PK Res Jatuh Tempo V2 (Bullet Payment)</a></li>
                                                                </ul>
                                                            </div>
                                                            
                                                            <div class='dropdown'>
                                                                <button class='btn btn-sm btn-success dropdown-toggle w-100' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                                                    Belum Jatuh Tempo
                                                                </button>
                                                                <ul class='dropdown-menu'>
                                                                    <li><a class='dropdown-item' href='../../../nusamba_api/production/system/dok_perjanjiankredit_res_2blmjtmp.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&noreg_jatuh_tempo={$restruk2_row['noreg_jatuh_tempo']}&type=noncbs' target='_blank'>PK Res Belum Jatuh Tempo V2 (Non Reguler)</a></li>
                                                                    <li><a class='dropdown-item' href='../../../nusamba_api/production/system/dok_perjanjiankredit_res3blmjtmp.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&noreg_jatuh_tempo={$restruk2_row['noreg_jatuh_tempo']}&type=noncbs' target='_blank'>PK Res Belum Jatuh Tempo V2 (Reguler)</a></li>
                                                                    <li><a class='dropdown-item' href='../../../nusamba_api/production/system/dok_perjanjiankredit_resbulletblmjtmp.php?nik={$restruk2_row['nik']}&noreg_awal={$restruk2_row['noreg_awal']}&noreg_restruk_1={$restruk2_row['noreg_restruk_1']}&noreg_restruk_2={$restruk2_row['noreg_restruk_2']}&noreg_jatuh_tempo={$restruk2_row['noreg_jatuh_tempo']}&type=noncbs' target='_blank'>PK Res Belum Jatuh Tempo V2 (Bullet Payment)</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class='py-1'>{$restruk2_row['nama']}</td>
                                                    <td class='py-1'>{$restruk2_row['untuk']}</td>
                                                    <td class='py-1 small'>{$restruk2_row['noreg_awal']}</td>
                                                    <td class='py-1 small'>{$restruk2_row['noreg_restruk_1']}</td>
                                                    <td class='py-1 small'>{$restruk2_row['noreg_restruk_2']}</td>
                                                    <td class='py-1 small'>{$restruk2_row['noreg_jatuh_tempo']}</td>
                                                    <td class='py-1 small'>{$restruk2_row['ket']}</td>
                                                </tr>";
                                                $no_restruk2++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='10' class='text-center py-2'>Tidak ada data restrukturisasi Droping Ulang</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Tambahkan modal di bagian bawah body sebelum closing tag -->
    <div class="modal fade" id="restrukModal" tabindex="-1" aria-labelledby="restrukModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restrukModalLabel">Pilih Jenis Restrukturisasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="redirectToRestruk('cbs')">CBS</button>
                        <button class="btn btn-primary" onclick="redirectToRestruk('noncbs')">Droping Ulang</button>
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