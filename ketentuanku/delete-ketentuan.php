<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek session
if (
    !isset($_SESSION['username']) || !isset($_SESSION['role_id']) ||
    !isset($_SESSION['nama']) || !isset($_SESSION['key_app']) ||
    !isset($_SESSION['foto']) || !isset($_SESSION['kantor']) ||
    !isset($_SESSION['kd_ao'])
) {
    header("Location: " . $base_url . "login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['file'])) {
    $id = $_GET['id'];
    $file = $_GET['file'];
    $file_path = 'uploads/' . $file;

    // Hapus file fisik jika ada
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Hapus data dari database
    $query = "DELETE FROM ketentuanku_surat WHERE id = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Data berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data!";
    }

    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_GET['id']) && isset($_GET['file'])) {
        $id = $_GET['id'];
        $file = $_GET['file'];
        $file_path = 'uploads/' . $file;

        // Hapus file fisik jika ada
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Hapus data dari database
        $query = "DELETE FROM ketentuanku_surat WHERE id = ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil dihapus!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = 'index.php';
                });
              </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal menghapus data!'
                }).then(function() {
                    window.location.href = 'index.php';
                });
              </script>";
        }
    } else {
        header("Location: index.php");
        exit();
    }
    ?>
</body>

</html>