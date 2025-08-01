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
    <title>Data Virtual Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Data Virtual Account</h2>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Virtual Account</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari No. Rekening atau Nama...">
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
                                    <th>No. Rekening</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No. VA</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                $limit = 10;
                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $limit;

                                $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                $where = '';
                                if (!empty($search)) {
                                    $where = "WHERE norek LIKE '%$search%' OR nama LIKE '%$search%'";
                                }

                                $query = mysqli_query($connect, "SELECT * FROM virtual_account $where ORDER BY tgl_input DESC LIMIT $start, $limit");
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM virtual_account $where"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo $row['norek']; ?></td>
                                    <td><?php echo ucwords(strtolower($row['nama'])); ?></td>
                                    <td><?php echo ucwords(strtolower($row['alamat'])); ?></td>
                                    <td><?php echo $row['no_va']; ?></td>
                                    <td class="text-center">
                                        <button type='button' class='btn btn-sm btn-info'
                                            onclick='copyData("<?php echo $row['nama']; ?>", "<?php echo $row['no_va']; ?>")'>
                                            <i class='bi bi-clipboard'></i>
                                        </button>
                                        <button type='button' class='btn btn-sm btn-success'
                                            onclick='showVAModal("<?php echo $row['id']; ?>", "<?php echo $row['nama']; ?>", "<?php echo $row['no_va']; ?>", "<?php echo $row['norek']; ?>")'>
                                            <i class='bi bi-download'></i>
                                        </button>
                                        <?php if (in_array($_SESSION['key_app'], ['ADMIN', 'CS', 'ADM'])): ?>
                                        <button type='button' class='btn btn-sm btn-warning'
                                            onclick='editVA("<?php echo $row['id']; ?>")'>
                                            <i class='bi bi-pencil'></i>
                                        </button>
                                        <button type='button' class='btn btn-sm btn-danger'
                                            onclick='deleteVA("<?php echo $row['id']; ?>")'>
                                            <i class='bi bi-trash'></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>

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
        </div>
    </div>

    <!-- Modal VA -->
    <div class="modal fade" id="vaModal" tabindex="-1" aria-labelledby="vaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vaModalLabel">Detail Virtual Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="va-card"
                        style="background: url('<?php echo $base_url; ?>assets/media/va-card.png') no-repeat center center; background-size: cover; padding: 15px; border-radius: 8px; width: 85.6mm; height: 53.98mm; position: relative;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="mb-0" style="font-size: 0.8rem; opacity: 0.8;">Virtual Account</p>
                                <p class="mb-0" style="font-size: 0.7rem; opacity: 0.8;">Bank Nusamba Tanjungsari</p>
                            </div>
                            <img src="<?php echo $base_url; ?>assets/logo.png" alt="Bank Logo" style="height: 35px;"
                                class="mb-2">
                        </div>
                        <div class="va-number mb-3" style="margin-top: 40px;">
                            <h3 id="modalVaNumber"
                                style="letter-spacing: 3px; font-size: 1.4rem; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);">
                            </h3>
                        </div>
                        <div class="card-bottom-info"
                            style="position: absolute; bottom: 15px; left: 15px; right: 15px;">
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <p class="mb-0" id="modalNama" style="font-size: 1rem;"></p>
                                </div>
                                <div style="text-align: right;">
                                    <p class="mb-1" style="font-size: 0.7rem; opacity: 0.8;">No. Rekening</p>
                                    <p class="mb-0" id="modalNorek" style="font-size: 0.8rem;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success w-100" onclick="downloadVACard()">Download</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

        if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 767) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });

    document.getElementById('searchButton').addEventListener('click', function() {
        let searchValue = document.getElementById('searchInput').value;
        let currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('search', searchValue);
        currentUrl.searchParams.set('page', '1');
        window.location.href = currentUrl.toString();
    });

    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchButton').click();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        let urlParams = new URLSearchParams(window.location.search);
        let searchValue = urlParams.get('search');
        if (searchValue) {
            document.getElementById('searchInput').value = searchValue;
        }
    });

    function deleteVA(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data virtual account akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete-va.php?id=${id}`;
            }
        });
    }

    function editVA(id) {
        window.location.href = `edit-va.php?id=${id}`;
    }

    function copyData(nama, noVa) {
        const textToCopy = `${nama}\n${noVa}`;

        navigator.clipboard.writeText(textToCopy).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil disalin!',
                text: 'Data telah disalin ke clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        }).catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal menyalin',
                text: 'Tidak dapat menyalin ke clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    function showVAModal(id, nama, noVa, norek) {
        document.getElementById('modalNama').textContent = nama.toUpperCase();
        document.getElementById('modalVaNumber').textContent = noVa.match(/.{1,4}/g).join(' ');
        document.getElementById('modalNorek').textContent = norek;

        const modal = new bootstrap.Modal(document.getElementById('vaModal'));
        modal.show();
    }

    function downloadVACard() {
        const element = document.querySelector('.va-card');
        const nama = document.getElementById('modalNama').textContent;

        html2canvas(element, {
            backgroundColor: null,
            scale: 2,
            logging: false,
            useCORS: true,
            allowTaint: true
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = `virtual-account-${nama}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    }
    </script>
</body>

</html>