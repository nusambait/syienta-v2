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

// Proses form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['login'])) {
    // Kode untuk proses form submission tracking berkas akan ditambahkan di sini
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Berkas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
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
            <h2 class="mb-4">Tracking Berkas</h2>

            <!-- Tambahkan bagian list data pengajuan -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Pengajuan</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari NIK, Nama, atau No. Registrasi...">
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
                                    <th>No. Registrasi</th>
                                    <th>NIK</th>
                                    <th>Nama Nasabah</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Jenis Kredit</th>
                                    <th>Plafon</th>
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
                                $where = '';
                                if (!empty($search)) {
                                    $where = "WHERE p.niknas LIKE '%$search%' OR p.noreg LIKE '%$search%' OR n.nama LIKE '%$search%'";
                                }

                                // Query dengan kondisi pencarian
                                $query = mysqli_query($connect, "SELECT p.*, n.nama as nama_nasabah 
                                FROM pengajuan p 
                                LEFT JOIN nasabah n ON p.niknas = n.nik 
                                $where 
                                ORDER BY STR_TO_DATE(p.tglpeng, '%d-%m-%Y') DESC 
                                LIMIT $start, $limit");

                                // Query untuk total data dengan kondisi pencarian
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM pengajuan p LEFT JOIN nasabah n ON p.niknas = n.nik $where"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['noreg']}</td>
                                        <td>{$row['niknas']}</td>
                                        <td>{$row['nama_nasabah']}</td>
                                        <td>{$row['tglpeng']}</td>
                                        <td>{$row['jns_kredit']}</td>
                                        <td>Rp. " . number_format($row['plaf'], 0, ',', '.') . "</td>
                                        <td>{$row['status']}</td>
                                        <td>
                                            <button type='button' class='btn btn-sm btn-primary' onclick='viewTracking(\"{$row['noreg']}\")'>
                                                <i class='bi bi-clock-history'></i>
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
                                    <a class="page-link"
                                        href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>"
                                        tabindex="-1">Previous</a>
                                </li>

                                <?php
                                $start_number = max(1, min($page - 4, $total_pages - 9));
                                $end_number = min($total_pages, $start_number + 9);

                                if ($start_number > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search) ? '&search=' . $search : '') . '">1</a></li>';
                                    if ($start_number > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                }

                                for ($i = $start_number; $i <= $end_number; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . (!empty($search) ? '&search=' . $search : '') . '">' . $i . '</a>
                                          </li>';
                                }

                                if ($end_number < $total_pages) {
                                    if ($end_number < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($search) ? '&search=' . $search : '') . '">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . $search : ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Modal untuk detail pengajuan -->
            <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailModalLabel">Detail Pengajuan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="detailContent">
                            <!-- Konten detail akan diisi melalui AJAX -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal untuk update status -->
            <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="statusModalLabel">Update Status Pengajuan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="updateStatusForm">
                            <div class="modal-body">
                                <input type="hidden" id="noregInput" name="noreg">
                                <div class="mb-3">
                                    <label for="statusSelect" class="form-label">Status</label>
                                    <select class="form-select" id="statusSelect" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Diajukan">Diajukan</option>
                                        <option value="Verifikasi">Verifikasi</option>
                                        <option value="Analisa">Analisa</option>
                                        <option value="Komite">Komite</option>
                                        <option value="Disetujui">Disetujui</option>
                                        <option value="Ditolak">Ditolak</option>
                                        <option value="Pencairan">Pencairan</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="keteranganInput" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="keteranganInput" name="keterangan"
                                        rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal untuk tracking pengajuan -->
            <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h5 class="modal-title" id="trackingModalLabel">Tracking Pengajuan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-2">
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <p class="mb-0 small"><strong>No. Registrasi:</strong> <span
                                            id="trackingNoreg"></span></p>
                                    <p class="mb-0 small"><strong>Nama Nasabah:</strong> <span id="trackingNama"></span>
                                    </p>
                                    <p class="mb-0 small"><strong>NIK:</strong> <span id="trackingNik"></span></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-0 small"><strong>Plafon:</strong> <span id="trackingPlafon"></span></p>
                                    <p class="mb-0 small"><strong>Jangka Waktu:</strong> <span id="trackingJw"></span>
                                    </p>
                                    <p class="mb-0 small"><strong>Jenis Kredit:</strong> <span
                                            id="trackingJnsKredit"></span></p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <p class="mb-0 small"><strong>Tanggal Pengajuan:</strong> <span
                                            id="trackingTglPengajuan"></span></p>
                                    <p class="mb-0 small"><strong>Status Terakhir:</strong> <span
                                            id="trackingStatusTerakhir" class="badge bg-primary"></span></p>
                                    <p class="mb-0 small"><strong>AO:</strong> <span id="trackingAo"></span></p>
                                </div>
                            </div>

                            <hr class="my-2">

                            <div class="tracking-history">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" style="font-size: 0.75rem;">
                                        <thead>
                                            <tr>
                                                <th width="15%">Tanggal</th>
                                                <th width="10%">Jam</th>
                                                <th width="18%">Status</th>
                                                <th width="15%">Operator</th>
                                                <th width="45%">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trackingContent">
                                            <!-- Data tracking akan diisi melalui AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer py-2">
                            <!-- <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button> -->
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

        // Fungsi untuk melihat detail pengajuan
        function viewDetail(noreg) {
            // Buat modal detail
            const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

            // Ambil data detail dari server
            fetch('get-detail-pengajuan.php?noreg=' + noreg)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Isi konten modal dengan data
                        let detailHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>No. Registrasi:</strong> ${data.data.noreg}</p>
                                    <p><strong>NIK:</strong> ${data.data.niknas}</p>
                                    <p><strong>Nama Nasabah:</strong> ${data.data.nama_nasabah || '-'}</p>
                                    <p><strong>Alamat:</strong> ${data.data.alamat_nasabah || '-'}</p>
                                    <p><strong>Telepon:</strong> ${data.data.telepon_nasabah || '-'}</p>
                                    <p><strong>Tanggal Pengajuan:</strong> ${data.data.tglpeng}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Jenis Kredit:</strong> ${data.data.jns_kredit}</p>
                                    <p><strong>Penggunaan:</strong> ${data.data.penggunaan}</p>
                                    <p><strong>Produk Kredit:</strong> ${data.data.prodkre}</p>
                                    <p><strong>Plafon:</strong> ${formatRupiah(data.data.plaf)}</p>
                                    <p><strong>Jangka Waktu:</strong> ${data.data.jw} bulan</p>
                                    <p><strong>Suku Bunga:</strong> ${data.data.sukbung}%</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Angsuran Pokok:</strong> ${formatRupiah(data.data.angpok)}</p>
                                    <p><strong>Angsuran Bunga:</strong> ${formatRupiah(data.data.angbung)}</p>
                                    <p><strong>Total Angsuran:</strong> ${formatRupiah(data.data.totang)}</p>
                                    <p><strong>Biaya Provisi:</strong> ${data.data.biaprov}%</p>
                                    <p><strong>Nominal Provisi:</strong> ${formatRupiah(data.data.nomprov)}</p>
                                    <p><strong>Biaya Administrasi:</strong> ${formatRupiah(data.data.adm)}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Tujuan Penggunaan:</strong> ${data.data.tujpeng}</p>
                                    <p><strong>Jaminan:</strong> ${data.data.jaminan}</p>
                                    <p><strong>Account Officer:</strong> ${data.data.ao}</p>
                                    <p><strong>Agen:</strong> ${data.data.agen}</p>
                                    <p><strong>Status:</strong> <span class="badge bg-primary">${data.data.status}</span></p>
                                    <p><strong>Keterangan:</strong> ${data.data.ket_peng}</p>
                                </div>
                            </div>
                        `;

                        document.getElementById('detailContent').innerHTML = detailHTML;
                        detailModal.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data pengajuan'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memuat data'
                    });
                });
        }

        // Fungsi untuk update status pengajuan
        function updateStatus(noreg) {
            // Set nilai noreg pada form
            document.getElementById('noregInput').value = noreg;

            // Ambil data status saat ini
            fetch('get-status-pengajuan.php?noreg=' + noreg)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Set nilai status dan keterangan pada form
                        document.getElementById('statusSelect').value = data.status;
                        document.getElementById('keteranganInput').value = data.keterangan;

                        // Tampilkan modal
                        const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
                        statusModal.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data status'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memuat data'
                    });
                });
        }

        // Handle form update status
        document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('update-status-pengajuan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Status pengajuan berhasil diperbarui',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Tutup modal
                            const statusModal = bootstrap.Modal.getInstance(document.getElementById(
                                'statusModal'));
                            statusModal.hide();

                            // Refresh halaman
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Gagal memperbarui status pengajuan'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memperbarui data'
                    });
                });
        });

        // Fungsi format rupiah
        function formatRupiah(angka) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
        }

        // Ganti fungsi pencarian dengan tombol
        document.getElementById('searchButton').addEventListener('click', function() {
            let searchValue = document.getElementById('searchInput').value;
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', searchValue);
            currentUrl.searchParams.set('page', '1'); // Reset ke halaman pertama
            window.location.href = currentUrl.toString();
        });

        // Tambahkan event listener untuk tombol Enter pada input
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });

        // Isi nilai pencarian dari parameter URL
        document.addEventListener('DOMContentLoaded', function() {
            let urlParams = new URLSearchParams(window.location.search);
            let searchValue = urlParams.get('search');
            if (searchValue) {
                document.getElementById('searchInput').value = searchValue;
            }
        });

        // Fungsi untuk melihat tracking pengajuan
        function viewTracking(noreg) {
            // Buat modal tracking
            const trackingModal = new bootstrap.Modal(document.getElementById('trackingModal'));

            // Ambil data tracking dari server
            fetch('get-tracking-pengajuan.php?noreg=' + noreg)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Isi informasi pengajuan
                        document.getElementById('trackingNoreg').textContent = data.pengajuan.noreg;
                        document.getElementById('trackingNama').textContent = data.pengajuan.nama_nasabah;
                        document.getElementById('trackingNik').textContent = data.pengajuan.niknas;
                        document.getElementById('trackingPlafon').textContent = formatRupiah(data.pengajuan.plaf);
                        document.getElementById('trackingJw').textContent = data.pengajuan.jw + ' bulan';
                        document.getElementById('trackingJnsKredit').textContent = data.pengajuan.jns_kredit;
                        document.getElementById('trackingTglPengajuan').textContent = data.pengajuan.tglpeng;
                        document.getElementById('trackingStatusTerakhir').textContent = data.pengajuan.status;
                        document.getElementById('trackingAo').textContent = data.pengajuan.nama_ao || data.pengajuan
                            .kd_ao || data.pengajuan.ao;

                        // Isi data tracking
                        let trackingHTML = '';
                        if (data.tracking.length > 0) {
                            data.tracking.forEach((item, index) => {
                                const statusClass = item.status === 'Ditolak' ? 'text-danger' : 'text-primary';
                                const operatorName = item.nama_operator || item.op;
                                trackingHTML += `
                                    <tr>
                                        <td>${item.tgl}</td>
                                        <td>${item.jam}</td>
                                        <td class="${statusClass} fw-bold">${item.status}</td>
                                        <td>${operatorName}</td>
                                        <td>${item.ket || '-'}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            trackingHTML = `
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data tracking</td>
                                </tr>
                            `;
                        }

                        document.getElementById('trackingContent').innerHTML = trackingHTML;
                        trackingModal.show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data tracking'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memuat data tracking'
                    });
                });
        }
    </script>
</body>

</html>