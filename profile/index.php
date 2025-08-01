<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Perbaiki pengecekan akses
if (!isset($_GET['nik'])) {
    // Jika tidak ada parameter NIK, gunakan NIK dari session user yang sedang login
    if (isset($_SESSION['nik'])) {
        $nik = mysqli_real_escape_string($connect, $_SESSION['nik']);
    } else {
        $_SESSION['error_message'] = "NIK tidak ditemukan!";
        header("Location: " . $base_url . "dashboard.php");
        exit();
    }
} else {
    $nik = mysqli_real_escape_string($connect, $_GET['nik']);

    // Hanya batasi akses ke profil lain untuk non-admin
    // Admin bisa melihat semua profil, user biasa hanya bisa lihat profilnya sendiri
    if ($_SESSION['role_id'] != 1) {
        if ($_SESSION['nik'] != $nik) {
            $_SESSION['error_message'] = "Anda tidak memiliki akses untuk melihat profil ini!";
            header("Location: " . $base_url . "dashboard.php");
            exit();
        }
    }
}

// Tambahkan proses penghapusan cover di bagian atas file setelah pengecekan status
if (isset($_GET['action']) && $_GET['action'] == 'delete_cover') {
    // Ambil data cover dari database
    $query = mysqli_query($connect, "SELECT cover FROM account WHERE nik='$nik'");
    $data_cover = mysqli_fetch_assoc($query);

    if ($data_cover['cover']) {
        // Hapus file cover
        $cover_file = "../assets/media/profile/" . $data_cover['cover'];
        if (file_exists($cover_file)) {
            unlink($cover_file);
        }

        // Update database
        $update = mysqli_query($connect, "UPDATE account SET cover=NULL WHERE nik='$nik'");

        if ($update) {
            $_SESSION['success'] = true;
            $_SESSION['message'] = "Cover berhasil dihapus!";
        } else {
            $_SESSION['error'] = true;
            $_SESSION['message'] = "Gagal menghapus cover: " . mysqli_error($connect);
        }
    }

    // Redirect kembali ke halaman profil
    header("Location: index.php?nik=$nik");
    exit();
}

// Proses perubahan status jika ada
if (isset($_GET['status']) && $_SESSION['role_id'] == 1) {
    $newStatus = mysqli_real_escape_string($connect, $_GET['status']);
    $updateNik = mysqli_real_escape_string($connect, $_GET['nik']);

    // Update status user
    $updateQuery = mysqli_query($connect, "UPDATE account SET status='$newStatus' WHERE nik='$updateNik'");

    if ($updateQuery) {
        $_SESSION['success'] = true;
        $_SESSION['message'] = "Status user berhasil diubah menjadi $newStatus!";
    } else {
        $_SESSION['error'] = true;
        $_SESSION['message'] = "Gagal mengubah status user: " . mysqli_error($connect);
    }

    // Redirect ke halaman profil user
    header("Location: index.php?nik=$updateNik");
    exit();
}

// Ambil data user berdasarkan NIK
$query = mysqli_query($connect, "SELECT * FROM account WHERE nik='$nik'");

// Cek apakah data ditemukan
if (mysqli_num_rows($query) == 0) {
    $_SESSION['error_message'] = "Data user tidak ditemukan!";
    header("Location: " . $base_url . "dashboard.php");
    exit();
}

$data = mysqli_fetch_assoc($query);

// Tambahkan variabel untuk cover
$cover_path = "../assets/media/profile/" . ($data['cover'] ? $data['cover'] : 'default-cover.jpg');

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

// Tambahkan fungsi untuk menentukan warna gradient berdasarkan role
function getGradientColors($role_id)
{
    switch ($role_id) {
        case 1: // Admin
            return ['#4158D0', '#C850C0']; // Gradient biru-ungu
        case 2: // Manager
            return ['#43C6AC', '#191654']; // Gradient tosca-biru gelap
        case 3: // Staff
            return ['#FF512F', '#DD2476']; // Gradient oranye-merah
        default:
            return ['#E233FF', '#FF50B8']; // Gradient default (ungu-pink)
    }
}

$gradientColors = getGradientColors($data['role_id']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo $data['nama']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    .profile-header {
        background: linear-gradient(135deg, <?php echo $gradientColors[0]; ?> 0%, <?php echo $gradientColors[1]; ?> 100%);
        background-image: url('<?php echo $cover_path; ?>');
        background-size: cover;
        background-position: center;
        border-radius: 15px;
        padding: 20px;
        margin-top: 5px;
        margin-bottom: 20px;
        position: relative;
        padding-top: 140px;
        padding-bottom: 125px;
        min-height: 300px;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(94, 49, 255, 0.06);
        border-radius: 15px;
    }

    .profile-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid rgba(255, 255, 255, 0.8);
        position: absolute;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        background: white;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .profile-info {
        text-align: center;
        padding-top: 20px;
        margin-top: 75px;
    }

    .profile-name-text {
        color: white;
        font-size: 18px;
        margin-bottom: 5px;
    }

    .profile-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
    }

    .social-links {
        margin-top: 10px;
    }

    .social-links a {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        margin-right: 10px;
        text-decoration: none;
        font-size: 14px;
    }

    .about-section {
        padding: 15px;
        background: white;
        border-radius: 15px;
    }

    .about-title {
        font-size: 19px;
        font-weight: 900;
        text-align: center;
        margin-left: 0;
        margin-bottom: 15px;
    }

    .about-text {
        color: #666;
        font-size: 14px;
        line-height: 1.5;
        margin-top: 25px;
        padding: 0 15px;
    }

    @media (min-width: 768px) {
        .profile-image {
            left: 8%;
            top: 215px;
            transform: translateX(-50%);
        }

        .profile-info {
            margin-top: 0;
        }

        .about-title {
            text-align: left;
            margin-left: 13%;
        }
    }

    @media (max-width: 767px) {
        .profile-header {
            padding-top: 100px;
            padding-bottom: 100px;
        }

        .about-section {
            margin-top: 20px;
        }

        .about-text {
            padding: 0 10px;
        }
    }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="profile-header">
                <?php if ($_SESSION['nik'] == $nik): ?>
                <button type="button" class="btn btn-light btn-sm position-absolute top-0 end-0 m-3"
                    data-bs-toggle="modal" data-bs-target="#updateCoverModal">
                    <i class="bi bi-pencil"></i> Edit Cover
                </button>
                <?php endif; ?>
                <?php
                $foto_path = "../assets/media/profile/" . ($data['foto'] ? $data['foto'] : 'default.png');
                ?>
                <img src="<?php echo $foto_path; ?>" alt="Foto Profil" class="profile-image" style="cursor: pointer;"
                    data-bs-toggle="modal" data-bs-target="#updateFotoModal">
                <div class="profile-info">
                    <h4 class="profile-name-text"></h4>
                    <div class="profile-subtitle"></div>
                </div>
            </div>

            <div class="about-section">
                <div class="about-title"><?php echo $data['nama']; ?></div>
                <div class="about-text">
                    <?php echo $data['bio'] ?? 'Belum ada deskripsi.'; ?>
                </div>
            </div>

            <?php if ($_SESSION['nik'] == $nik): ?>
            <div class="about-section mt-4">
                <div class="about-title">Edit Profil</div>
                <form action="update-profile.php" method="POST" id="updateProfileForm">
                    <div class="row">
                        <input type="hidden" name="nik" value="<?php echo htmlspecialchars($nik); ?>">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama"
                                value="<?php echo htmlspecialchars($data['nama']); ?>" required readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($data['email']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir"
                                value="<?php echo htmlspecialchars($data['tgl_lahir']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_wa" class="form-label">Nomor WhatsApp</label>
                            <input type="number" class="form-control" id="no_wa" name="no_wa"
                                value="<?php echo htmlspecialchars($data['no_wa']); ?>"
                                placeholder="Contoh: 628123456789" pattern="[0-9]+" title="Masukkan hanya angka"
                                require>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4"
                            required><?php echo htmlspecialchars($data['bio']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"
                            required><?php echo htmlspecialchars($data['alamat']); ?></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" id="submitBtn" name="update_profile">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Update Cover -->
    <div class="modal fade" id="updateCoverModal" tabindex="-1" aria-labelledby="updateCoverModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateCoverModalLabel">Ganti Cover Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="update-cover.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="coverPreview" class="form-label">Preview Cover</label>
                            <div class="text-center mb-3">
                                <?php if ($data['cover'] && file_exists($cover_path)): ?>
                                <img id="coverPreview" src="<?php echo $cover_path; ?>" class="img-fluid rounded"
                                    style="max-height: 200px; width: 100%; object-fit: cover;">
                                <div class="mt-2">
                                    <a href="?nik=<?php echo $nik; ?>&action=delete_cover" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus cover?')">
                                        <i class="bi bi-trash"></i> Hapus Cover
                                    </a>
                                </div>
                                <?php else: ?>
                                <div id="noCoverText" class="p-4 bg-light rounded text-center">
                                    <p class="mb-0">Cover belum ditambahkan</p>
                                </div>
                                <img id="coverPreview" src="" class="img-fluid rounded d-none"
                                    style="max-height: 200px; width: 100%; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <label for="coverFile" class="form-label">Pilih Gambar Cover Baru</label>
                            <input type="file" class="form-control" id="coverFile" name="cover" accept="image/*"
                                required onchange="previewCover(this);">
                            <div class="form-text">Format yang diizinkan: JPG, JPEG, PNG, GIF. Ukuran maksimal: 10MB
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    function previewCover(input) {
        const preview = document.getElementById('coverPreview');
        const noCoverText = document.getElementById('noCoverText');
        const maxSize = 10 * 1024 * 1024; // 10MB dalam bytes

        if (input.files && input.files[0]) {
            if (input.files[0].size > maxSize) {
                alert('Ukuran file terlalu besar! Maksimal 10MB');
                input.value = '';
                return;
            }

            var reader = new FileReader();

            reader.onload = function(e) {
                if (noCoverText) {
                    noCoverText.classList.add('d-none');
                }
                preview.classList.remove('d-none');
                preview.src = e.target.result;
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('updateProfileForm');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Mencegah form submit default

                // Tampilkan loading state
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass"></i> Menyimpan...';

                // Submit form menggunakan AJAX
                fetch('update-profile.php', {
                        method: 'POST',
                        body: new FormData(this)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Terjadi kesalahan');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: error.message
                        });
                    })
                    .finally(() => {
                        // Kembalikan tombol ke keadaan semula
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-save"></i> Simpan Perubahan';
                    });
            });
        }
    });
    </script>
</body>

</html>