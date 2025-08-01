<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Proses Edit Data
if (isset($_POST['edit'])) {
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    $tgl_pengambilan = mysqli_real_escape_string($connect, $_POST['tgl_pengambilan']);
    $loan = mysqli_real_escape_string($connect, $_POST['loan']);
    $nm_peminjam = mysqli_real_escape_string($connect, $_POST['nm_peminjam']);
    $jaminan = mysqli_real_escape_string($connect, $_POST['jaminan']);
    $nm_pengambil = mysqli_real_escape_string($connect, $_POST['nm_pengambil']);

    $query = mysqli_query($connect, "UPDATE pengambilan SET 
        tgl_pengambilan='$tgl_pengambilan',
        loan='$loan',
        nm_peminjam='$nm_peminjam',
        jaminan='$jaminan',
        nm_pengambil='$nm_pengambil'
        WHERE id='$id'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil diperbarui!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'pengambilan-jaminan.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Data gagal diperbarui! " . mysqli_error($connect) . "',
                    showConfirmButton: true
                });
            });
        </script>";
    }
}

// Proses Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $query = mysqli_query($connect, "DELETE FROM pengambilan WHERE id='$id'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil dihapus!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'pengambilan-jaminan.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Data gagal dihapus!',
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        </script>";
    }
}

// Proses Tambah Data
if (isset($_POST['tambah'])) {
    $tgl_pengambilan = mysqli_real_escape_string($connect, $_POST['tgl_pengambilan']);
    $loan = mysqli_real_escape_string($connect, $_POST['loan']);
    $nm_peminjam = mysqli_real_escape_string($connect, $_POST['nm_peminjam']);
    $jaminan = mysqli_real_escape_string($connect, $_POST['jaminan']);
    $nm_pengambil = mysqli_real_escape_string($connect, $_POST['nm_pengambil']);

    $query = mysqli_query($connect, "INSERT INTO pengambilan (tgl_pengambilan, loan, nm_peminjam, jaminan, nm_pengambil) 
        VALUES ('$tgl_pengambilan', '$loan', '$nm_peminjam', '$jaminan', '$nm_pengambil')");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil ditambahkan!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'pengambilan-jaminan.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Data gagal ditambahkan! " . mysqli_error($connect) . "',
                    showConfirmButton: true
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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengambilan Jaminan</title>
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
                window.location.href = 'pengambilan-jaminan.php?search=' + encodeURIComponent(searchValue);
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
                <h2 class="mb-0">Pengambilan Jaminan</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Pengambilan Jaminan
                </button>
            </div>

            <!-- Tambahkan Modal Tambah Data -->
            <div class="modal fade" id="tambahModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Pengambilan Jaminan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pengambilan</label>
                                    <input type="text" class="form-control date-input" name="tgl_pengambilan"
                                        value="<?php echo date('d-m-Y'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Pinjaman</label>
                                    <input type="text" class="form-control" name="loan" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Peminjam</label>
                                    <input type="text" class="form-control" name="nm_peminjam" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jaminan</label>
                                    <textarea class="form-control" name="jaminan" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Pengambil</label>
                                    <input type="text" class="form-control" name="nm_pengambil" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Nasabah</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari No. Pinjaman atau Nama Peminjam...">
                            <button class="btn btn-primary" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="export-excel-pengambilan-jaminan.php" class="btn btn-success">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-compact">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Pengambilan</th>
                                    <th>No. Pinjaman</th>
                                    <th>Nama Peminjam</th>
                                    <th>Jaminan</th>
                                    <th>Nama Pengambil</th>
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
                                    $where = "WHERE loan LIKE '%$search%' OR nm_peminjam LIKE '%$search%'";
                                }

                                // Query dengan kondisi pencarian
                                $query = mysqli_query($connect, "SELECT * FROM pengambilan $where ORDER BY id DESC LIMIT $start, $limit");

                                // Query untuk total data dengan kondisi pencarian
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM pengambilan $where"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['tgl_pengambilan']}</td>
                                        <td>{$row['loan']}</td>
                                        <td>{$row['nm_peminjam']}</td>
                                        <td>{$row['jaminan']}</td>
                                        <td>{$row['nm_pengambil']}</td>
                                        <td class='d-flex gap-1 justify-content-center'>
                                        <a href='../../nusamba_api/production/system/dok_pengambilan.php?id={$row['id']}' class='btn btn-sm btn-info' target='_blank'>
                                            <i class='bi bi-printer'></i>
                                        </a>
                                            <button type='button' class='btn btn-sm btn-warning' onclick='editData(" . json_encode([
                                        'id' => $row['id'],
                                        'tgl_pengambilan' => $row['tgl_pengambilan'],
                                        'loan' => $row['loan'],
                                        'nm_peminjam' => $row['nm_peminjam'],
                                        'jaminan' => $row['jaminan'],
                                        'nm_pengambil' => $row['nm_pengambil']
                                    ]) . ")'>
                                                <i class='bi bi-pencil'></i>
                                                </button>
                                            <button type='button' class='btn btn-sm btn-danger' onclick='deleteData(\"{$row['id']}\")'>
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
                            <ul class="pagination justify-content-center">
                                <?php
                                $prev = $page - 1;
                                $next = $page + 1;

                                // Tentukan range halaman yang akan ditampilkan
                                $start_page = max(1, $page - 4);
                                $end_page = min($total_pages, $start_page + 9);

                                // Sesuaikan start_page jika end_page kurang dari 10 halaman
                                if ($end_page - $start_page < 9) {
                                    $start_page = max(1, $end_page - 9);
                                }
                                ?>

                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $prev; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">Previous</a>
                                </li>

                                <?php
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    echo "<li class='page-item " . ($page == $i ? 'active' : '') . "'><a class='page-link' href='?page=$i" . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '') . "'>$i</a></li>";
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $next; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Pengambilan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pengambilan</label>
                            <input type="text" class="form-control date-input" name="tgl_pengambilan"
                                id="edit_tgl_pengambilan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Pinjaman</label>
                            <input type="text" class="form-control" name="loan" id="edit_loan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Peminjam</label>
                            <input type="text" class="form-control" name="nm_peminjam" id="edit_nm_peminjam" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jaminan</label>
                            <textarea class="form-control" name="jaminan" id="edit_jaminan" rows="3"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Pengambil</label>
                            <input type="text" class="form-control" name="nm_pengambil" id="edit_nm_pengambil" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
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

    // Fungsi edit data
    function editData(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_tgl_pengambilan').value = data.tgl_pengambilan;
        document.getElementById('edit_loan').value = data.loan;
        document.getElementById('edit_nm_peminjam').value = data.nm_peminjam;
        document.getElementById('edit_jaminan').value = data.jaminan;
        document.getElementById('edit_nm_pengambil').value = data.nm_pengambil;

        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    // Fungsi hapus data
    function deleteData(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'pengambilan-jaminan.php?delete=' + id;
            }
        });
    }
    </script>
</body>

</html>