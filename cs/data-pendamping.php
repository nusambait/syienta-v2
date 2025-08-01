<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Handler for AJAX requests
if (isset($_GET['action']) || isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];

    // GET request for fetching data
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'get') {
        try {
            $id = $_GET['id'];
            $query = "SELECT * FROM pendamping WHERE nikpend = ?";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            echo json_encode($data);
            exit;
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    // POST request for updating data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
        try {
            // Proses pekerjaan - jika memilih Lainnya, gunakan input manual
            $pek = $_POST['pek'];
            if ($pek == 'Lainnya') {
                if (isset($_POST['pek_lainnya']) && !empty($_POST['pek_lainnya'])) {
                    $pek_lainnya = $_POST['pek_lainnya'];

                    // Validasi format input (hanya huruf, spasi, titik, dan koma)
                    if (!preg_match('/^[A-Za-z\s\.\,]+$/', $pek_lainnya)) {
                        echo json_encode(['success' => false, 'error' => 'Pekerjaan hanya boleh berisi huruf, spasi, titik, dan koma']);
                        exit;
                    }

                    $pek = $pek_lainnya;
                } else {
                    // Jika memilih Lainnya tapi tidak mengisi input, kembalikan error
                    echo json_encode(['success' => false, 'error' => 'Harap isi pekerjaan Anda jika memilih Lainnya']);
                    exit;
                }
            }

            $stmt = $connect->prepare("UPDATE pendamping SET 
                niknas = ?, nama = ?, hub = ?, tmpt = ?, 
                tgl = ?, pek = ?, tlfn1 = ?, tlfn2 = ?, 
                status = ? WHERE nikpend = ?");

            $stmt->bind_param(
                "ssssssssss",
                $_POST['niknas'],
                $_POST['nama'],
                $_POST['hub'],
                $_POST['tmpt'],
                $_POST['tgl'],
                $pek,
                $_POST['tlfn1'],
                $_POST['tlfn2'],
                $_POST['status'],
                $_POST['nikpend']
            );

            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['error'] = $stmt->error;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
        exit;
    }
}

// Handler for POST request (login)
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

// Konfigurasi pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Query dengan pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE nikpend LIKE '%$search%' OR nama LIKE '%$search%' OR niknas LIKE '%$search%'";
}

$query = "SELECT * FROM pendamping $where ORDER BY nikpend DESC LIMIT $start, $limit";
$result = mysqli_query($connect, $query);

// Hitung total halaman
$total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM pendamping $where"))[0];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Data Pendamping - Nusamba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Cek Data Pendamping</h2>
                <a href="input-pendamping.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Pendamping
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Daftar Pendamping</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari NIK atau Nama..."
                                value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIK Pendamping</th>
                                    <th>Terhubung Dengan NIKNAS</th>
                                    <th>Nama</th>
                                    <th>Hubungan</th>
                                    <th>Tempat, Tgl Lahir</th>
                                    <th>Pekerjaan</th>
                                    <th>Terhubung dengan Noreg</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($result)) {
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['nikpend']}</td>
                                        <td>{$row['niknas']}</td>
                                        <td>{$row['nama']}</td>
                                        <td>{$row['hub']}</td>
                                        <td>{$row['tmpt']}, {$row['tgl']}</td>
                                        <td>{$row['pek']}</td>
                                        <td>{$row['status']}</td>
                                        <td>
                                            <div class='btn-group'>
                                                <a href='javascript:void(0)' onclick='editPendamping(\"{$row['nikpend']}\")' class='btn btn-sm btn-warning'>
                                                    <i class='bi bi-pencil'></i>
                                                </a>
                                                <button type='button' class='btn btn-sm btn-danger' onclick='confirmDelete(\"{$row['nikpend']}\")'>
                                                    <i class='bi bi-trash'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>";
                                    $no++;
                                }
                                if (mysqli_num_rows($result) == 0) {
                                    echo "<tr><td colspan='10' class='text-center'>Tidak ada data</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php
                                // Previous button
                                echo '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">
                                    <a class="page-link" href="?page=' . ($page - 1) . '&search=' . urlencode($search) . '">Previous</a>
                                </li>';

                                // Calculate range of visible page numbers
                                $range = 5; // Numbers to show before and after current page
                                $start_page = max(1, $page - $range);
                                $end_page = min($total_pages, $page + $range);

                                // First page and ellipsis
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($search) . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }

                                // Page numbers
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                        <a class="page-link" href="?page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a>
                                    </li>';
                                }

                                // Last page and ellipsis
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($search) . '">' . $total_pages . '</a></li>';
                                }

                                // Next button
                                echo '<li class="page-item ' . ($page >= $total_pages ? 'disabled' : '') . '">
                                    <a class="page-link" href="?page=' . ($page + 1) . '&search=' . urlencode($search) . '">Next</a>
                                </li>';
                                ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data Pendamping</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST">
                        <input type="hidden" name="action" value="update">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">NIK Pendamping</label>
                                <input type="text" class="form-control" name="nikpend" id="edit_nikpend" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Terhubung Dengan NIKNAS</label>
                                <input type="text" class="form-control" name="niknas" id="edit_niknas" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" id="edit_nama" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hubungan</label>
                                <input type="text" class="form-control" name="hub" id="edit_hub" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tmpt" id="edit_tmpt" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="text" class="form-control date-input" name="tgl" id="edit_tgl" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pekerjaan</label>
                                <div class="alert alert-info" role="alert">
                                    <!-- <i class="bi bi-info-circle"></i> -->
                                    <strong>Petunjuk:</strong> Pilih pekerjaan dari daftar di bawah. Jika pekerjaan Anda tidak ada dalam daftar, pilih "Lainnya" dan ketik pekerjaan Anda secara manual.
                                </div>
                                <select class="form-select" name="pek" id="edit_pek" onchange="togglePekerjaanLainnya()" required>
                                    <option value="">Pilih Pekerjaan</option>
                                    <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
                                    <option value="PNS">PNS</option>
                                    <option value="TNI/POLRI">TNI/POLRI</option>
                                    <option value="Karyawan Swasta">Karyawan Swasta</option>
                                    <option value="Karyawan Honorer">Karyawan Honorer</option>
                                    <option value="Wiraswasta">Wiraswasta</option>
                                    <option value="Petani/Pekebun">Petani/Pekebun</option>
                                    <option value="Nelayan">Nelayan</option>
                                    <option value="Buruh">Buruh</option>
                                    <option value="Guru">Guru</option>
                                    <option value="Dosen">Dosen</option>
                                    <option value="Dokter">Dokter</option>
                                    <option value="Perawat">Perawat</option>
                                    <option value="Pedagang">Pedagang</option>
                                    <option value="Pengacara">Pengacara</option>
                                    <option value="Notaris">Notaris</option>
                                    <option value="Arsitek">Arsitek</option>
                                    <option value="Akuntan">Akuntan</option>
                                    <option value="Konsultan">Konsultan</option>
                                    <option value="Freelancer">Freelancer</option>
                                    <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                                    <option value="Pensiunan">Pensiunan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                <div class="mb-3" id="pekerjaanLainnyaDiv" style="display: none;">
                                    <label class="form-label">Sebutkan Pekerjaan</label>
                                    <input type="text" class="form-control" name="pek_lainnya" id="pekerjaanLainnya"
                                        placeholder="Masukkan pekerjaan Anda"
                                        pattern="[A-Za-z\s\.\,]+"
                                        title="Hanya huruf titik dan koma yang diperbolehkan"
                                        onkeypress="return /[A-Za-z\s\.\,]/.test(event.key)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon 1</label>
                                <input type="text" class="form-control" name="tlfn1" id="edit_tlfn1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon 2</label>
                                <input type="text" class="form-control" name="tlfn2" id="edit_tlfn2">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Terhubung dengan Noreg</label>
                                <input type="text" class="form-control" name="status" id="edit_status" readonly>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="updatePendamping()">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        // Fungsi pencarian
        document.getElementById('searchButton').addEventListener('click', function() {
            let searchValue = document.getElementById('searchInput').value;
            window.location.href = '?search=' + encodeURIComponent(searchValue);
        });

        // Event listener untuk tombol Enter pada input pencarian
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });

        // Fungsi konfirmasi hapus
        function confirmDelete(nikpend) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Apakah Anda yakin ingin menghapus data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete-pendamping.php?id=' + encodeURIComponent(nikpend);
                }
            });
        }

        function editPendamping(nikpend) {
            fetch(`data-pendamping.php?action=get&id=${encodeURIComponent(nikpend)}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) {
                        throw new Error('No data received');
                    }

                    // Populate modal fields
                    Object.keys(data).forEach(key => {
                        const element = document.getElementById(`edit_${key}`);
                        if (element) {
                            element.value = data[key] || '';
                        }
                    });

                    // Handle custom pekerjaan
                    const pekerjaanSelect = document.getElementById('edit_pek');
                    const pekerjaanLainnyaDiv = document.getElementById('pekerjaanLainnyaDiv');
                    const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');

                    // Daftar pekerjaan yang ada di dropdown
                    const pekerjaanOptions = ['PNS', 'TNI/POLRI', 'Karyawan Swasta', 'Karyawan Honorer', 'Wiraswasta', 'Petani/Pekebun', 'Nelayan', 'Buruh', 'Guru', 'Dosen', 'Dokter', 'Perawat', 'Pedagang', 'Pengacara', 'Notaris', 'Arsitek', 'Akuntan', 'Konsultan', 'Freelancer', 'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan', 'Lainnya'];

                    // Cek apakah pekerjaan yang tersimpan adalah custom
                    const savedPekerjaan = data.pek;
                    const isCustomPekerjaan = !pekerjaanOptions.includes(savedPekerjaan) && savedPekerjaan !== '';

                    if (isCustomPekerjaan) {
                        // Set dropdown ke "Lainnya" dan tampilkan input
                        pekerjaanSelect.value = 'Lainnya';
                        pekerjaanLainnyaDiv.style.display = 'block';
                        pekerjaanLainnyaInput.value = savedPekerjaan;
                        pekerjaanLainnyaInput.required = true;
                    } else {
                        // Sembunyikan input jika pekerjaan ada di dropdown
                        pekerjaanLainnyaDiv.style.display = 'none';
                        pekerjaanLainnyaInput.required = false;
                    }

                    // Show modal
                    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                    editModal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data pendamping'
                    });
                });
        }

        function updatePendamping() {
            // Validasi form terlebih dahulu
            if (!validateEditForm()) {
                return;
            }

            const form = document.getElementById('editForm');
            const formData = new FormData(form);

            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('data-pendamping.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data pendamping berhasil diperbarui',
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'Gagal memperbarui data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Terjadi kesalahan pada server'
                    });
                });
        }

        // Check for status parameter in URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const message = urlParams.get('message');

        if (status && message) {
            if (status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: decodeURIComponent(message),
                    timer: 1500
                });
            } else if (status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: decodeURIComponent(message)
                });
            }

            // Clean up URL after showing alert
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Fungsi untuk toggle input pekerjaan lainnya
        function togglePekerjaanLainnya() {
            const pekerjaanSelect = document.getElementById('edit_pek');
            const pekerjaanLainnyaDiv = document.getElementById('pekerjaanLainnyaDiv');
            const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');

            if (pekerjaanSelect.value === 'Lainnya') {
                pekerjaanLainnyaDiv.style.display = 'block';
                pekerjaanLainnyaInput.required = true;
            } else {
                pekerjaanLainnyaDiv.style.display = 'none';
                pekerjaanLainnyaInput.required = false;
                pekerjaanLainnyaInput.value = '';
            }
        }

        // Fungsi validasi form
        function validateEditForm() {
            const pekerjaanSelect = document.getElementById('edit_pek');
            const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');

            if (pekerjaanSelect.value === 'Lainnya') {
                if (!pekerjaanLainnyaInput.value.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Harap isi pekerjaan Anda jika memilih Lainnya',
                        confirmButtonText: 'OK'
                    });
                    pekerjaanLainnyaInput.focus();
                    return false;
                }

                // Validasi format input (hanya huruf, spasi, titik, dan koma)
                const regex = /^[A-Za-z\s\.\,]+$/;
                if (!regex.test(pekerjaanLainnyaInput.value.trim())) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Pekerjaan hanya boleh berisi huruf, spasi, titik, dan koma',
                        confirmButtonText: 'OK'
                    });
                    pekerjaanLainnyaInput.focus();
                    return false;
                }
            }
            return true;
        }
    </script>
</body>

</html>