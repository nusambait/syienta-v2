<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

// Cek apakah parameter NIK ada
if (!isset($_GET['nik'])) {
    $_SESSION['error_message'] = "NIK tidak ditemukan!";
    header("Location: index.php");
    exit();
}

$nik = mysqli_real_escape_string($connect, $_GET['nik']);

// Ambil data user berdasarkan NIK
$query = mysqli_query($connect, "SELECT * FROM account WHERE nik='$nik'");

// Cek apakah data ditemukan
if (mysqli_num_rows($query) == 0) {
    $_SESSION['error_message'] = "Data user tidak ditemukan!";
    header("Location: index.php");
    exit();
}

$data = mysqli_fetch_assoc($query);

// Ambil data role (jika tabel role tidak ada, gunakan nilai dari role_id saja)
$role_name = $data['role_id']; // Gunakan role_id sebagai default

// Coba ambil data role jika tabel role ada
try {
    $role_query = mysqli_query($connect, "SHOW TABLES LIKE 'role'");
    if (mysqli_num_rows($role_query) > 0) {
        $role_query = mysqli_query($connect, "SELECT * FROM role WHERE id='{$data['role_id']}'");
        if ($role_query && mysqli_num_rows($role_query) > 0) {
            $role_data = mysqli_fetch_assoc($role_query);
            $role_name = $role_data['role_name'];
        }
    }
} catch (Exception $e) {
    // Jika error, tetap gunakan role_id sebagai nama role
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - <?php echo $data['nama']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    .profile-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #f8f9fa;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .user-info-card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .info-label {
        font-weight: 600;
        color: #6c757d;
    }

    .info-value {
        font-weight: 400;
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.65em;
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
                <h2 class="mb-0">Detail User</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card user-info-card">
                        <div class="card-body text-center py-4">
                            <?php
                            $foto_path = "../../assets/media/profile/" . ($data['foto'] ? $data['foto'] : 'default.png');
                            ?>
                            <img src="<?php echo $foto_path; ?>" alt="Foto Profil" class="profile-image mb-3">
                            <h4 class="mb-1"><?php echo $data['nama']; ?></h4>
                            <p class="text-muted mb-2"><?php echo $data['jabatan']; ?></p>
                            <span
                                class="badge <?php echo $data['status'] == 'AKTIF' ? 'bg-success' : 'bg-danger'; ?> status-badge">
                                <?php echo $data['status']; ?>
                            </span>

                            <div class="mt-4">
                                <a href="edit-user.php?nik=<?php echo $data['nik']; ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit User
                                </a>
                                <button type="button"
                                    class="btn btn-<?php echo $data['status'] == 'AKTIF' ? 'danger' : 'success'; ?> btn-sm"
                                    onclick="toggleStatus('<?php echo $data['nik']; ?>', '<?php echo $data['status'] == 'AKTIF' ? 'NON-AKTIF' : 'AKTIF'; ?>')">
                                    <i
                                        class="bi bi-toggle-<?php echo $data['status'] == 'AKTIF' ? 'off' : 'on'; ?>"></i>
                                    <?php echo $data['status'] == 'AKTIF' ? 'Non-aktifkan' : 'Aktifkan'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card user-info-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informasi User</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">NIK</div>
                                <div class="col-md-8 info-value"><?php echo $data['nik']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Username</div>
                                <div class="col-md-8 info-value"><?php echo $data['username']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Nama Lengkap</div>
                                <div class="col-md-8 info-value"><?php echo $data['nama']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Jabatan</div>
                                <div class="col-md-8 info-value"><?php echo $data['jabatan']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Role</div>
                                <div class="col-md-8 info-value"><?php echo $role_name; ?> (ID:
                                    <?php echo $data['role_id']; ?>)</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Kantor</div>
                                <div class="col-md-8 info-value"><?php echo $data['kantor']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Kode AO</div>
                                <div class="col-md-8 info-value"><?php echo $data['kd_ao'] ?: '-'; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Key App</div>
                                <div class="col-md-8 info-value"><?php echo $data['key_app']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">ID</div>
                                <div class="col-md-8 info-value"><?php echo $data['id']; ?> (Kode FO)</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Status</div>
                                <div class="col-md-8 info-value">
                                    <span
                                        class="badge <?php echo $data['status'] == 'AKTIF' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $data['status']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Tanggal Dibuat</div>
                                <div class="col-md-8 info-value">
                                    <?php
                                    echo isset($data['created_at']) ? date('d-m-Y H:i:s', strtotime($data['created_at'])) : 'Tidak tersedia';
                                    ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 info-label">Terakhir Diperbarui</div>
                                <div class="col-md-8 info-value">
                                    <?php
                                    echo isset($data['updated_at']) ? date('d-m-Y H:i:s', strtotime($data['updated_at'])) : 'Tidak tersedia';
                                    ?>
                                </div>
                            </div>
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
        document.getElementById('sidebarOverlay').classList.toggle('show');
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
    </script>
</body>

</html>