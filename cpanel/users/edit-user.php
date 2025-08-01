<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

// Cek apakah NIK ada di parameter URL
if (!isset($_GET['nik'])) {
    $_SESSION['error_message'] = "NIK tidak ditemukan!";
    header("Location: index.php");
    exit();
}

$nik = mysqli_real_escape_string($connect, $_GET['nik']);

// Ambil data user berdasarkan NIK
$query = mysqli_query($connect, "SELECT * FROM account WHERE nik='$nik'");
if (mysqli_num_rows($query) == 0) {
    $_SESSION['error_message'] = "Data user tidak ditemukan!";
    header("Location: index.php");
    exit();
}

$data = mysqli_fetch_assoc($query);

// Proses form edit user
if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $jabatan = mysqli_real_escape_string($connect, $_POST['jabatan']);
    $kantor = mysqli_real_escape_string($connect, $_POST['kantor']);
    $kd_ao = mysqli_real_escape_string($connect, $_POST['kd_ao']);
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    $key_app = mysqli_real_escape_string($connect, $_POST['key_app']);
    $role_id = mysqli_real_escape_string($connect, $_POST['role_id']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $new_nik = mysqli_real_escape_string($connect, $_POST['nik']);

    // Cek apakah NIK diubah
    $nik_changed = ($new_nik != $nik);

    // Jika NIK diubah, periksa apakah NIK baru sudah terpakai
    if ($nik_changed) {
        $check_nik = mysqli_query($connect, "SELECT * FROM account WHERE nik='$new_nik'");
        if (mysqli_num_rows($check_nik) > 0) {
            $_SESSION['error_message'] = "NIK $new_nik sudah terpakai oleh pengguna lain!";
            header("Location: edit-user.php?nik=$nik");
            exit();
        }
    }

    // Cek apakah password diubah
    $password_query = "";
    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($connect, $_POST['password']);
        $password_query = ", password='$password'";
    }

    // Proses upload foto jika ada
    $foto = $data['foto']; // Default menggunakan foto yang sudah ada

    if ($_FILES['foto']['name']) {
        $file_name = $_FILES['foto']['name'];
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $extensions = array("jpeg", "jpg", "png");

        if (in_array($file_ext, $extensions)) {
            // Hapus foto lama jika bukan default
            if ($foto != 'default.png' && file_exists("../assets/media/profile/" . $foto)) {
                unlink("../../assets/media/profile/" . $foto);
            }

            // Generate nama file baru
            $foto = "profile_" . $new_nik . "_" . time() . "." . $file_ext;
            move_uploaded_file($file_tmp, "../../assets/media/profile/" . $foto);
        } else {
            $_SESSION['error_message'] = "Format file foto tidak didukung! Gunakan format JPEG, JPG, atau PNG.";
            header("Location: edit-user.php?nik=$new_nik");
            exit();
        }
    }

    // Update data user
    $update_query = mysqli_query($connect, "UPDATE account SET 
        nik='$new_nik',
        username='$username', 
        nama='$nama', 
        jabatan='$jabatan', 
        kantor='$kantor', 
        kd_ao='$kd_ao', 
        id='$id',
        key_app='$key_app', 
        role_id='$role_id', 
        status='$status', 
        foto='$foto'
        $password_query 
        WHERE nik='$nik'");

    if ($update_query) {
        $_SESSION['success_message'] = "Data user berhasil diperbarui!";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data user: " . mysqli_error($connect);
        header("Location: edit-user.php?nik=$new_nik");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Edit User</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?php echo $_SESSION['error_message']; ?>',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
            </script>
            <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik"
                                    value="<?php echo $data['nik']; ?>" required>
                                <small class="text-muted">NIK dapat diubah selama belum digunakan oleh pengguna
                                    lain</small>
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?php echo $data['username']; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password</small>
                            </div>
                            <div class="col-md-6">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    value="<?php echo $data['nama']; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <input type="text" class="form-control" id="jabatan" name="jabatan"
                                    value="<?php echo $data['jabatan']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="kantor" class="form-label">Kantor</label>
                                <input type="text" class="form-control" id="kantor" name="kantor"
                                    value="<?php echo $data['kantor']; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kd_ao" class="form-label">Kode AO</label>
                                <input type="text" class="form-control" id="kd_ao" name="kd_ao"
                                    value="<?php echo $data['kd_ao']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="id" class="form-label">Kode FO</label>
                                <input type="text" class="form-control" id="id" name="id"
                                    value="<?php echo $data['id']; ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="key_app" class="form-label">Key App</label>
                                <input type="text" class="form-control" id="key_app" name="key_app"
                                    value="<?php echo $data['key_app']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Role</label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    <option value="">Pilih Role</option>
                                    <option value="1" <?php echo ($data['role_id'] == 1) ? 'selected' : ''; ?>>Admin
                                    </option>
                                    <option value="2" <?php echo ($data['role_id'] == 2) ? 'selected' : ''; ?>>User
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="AKTIF" <?php echo ($data['status'] == 'AKTIF') ? 'selected' : ''; ?>>
                                        AKTIF</option>
                                    <option value="NON-AKTIF"
                                        <?php echo ($data['status'] == 'NON-AKTIF') ? 'selected' : ''; ?>>NON-AKTIF
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="foto" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="foto" name="foto">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Foto Saat Ini</label>
                                <div>
                                    <?php
                                    $foto_path = "../../assets/media/profile/" . $data['foto'];
                                    if (empty($data['foto']) || !file_exists($foto_path)) {
                                        $foto_path = "../../assets/media/profile/default.png";
                                    }
                                    ?>
                                    <img src="<?php echo $foto_path; ?>" alt="Foto Profil" class="img-thumbnail"
                                        style="max-width: 100px;">
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
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
    </script>
</body>

</html>