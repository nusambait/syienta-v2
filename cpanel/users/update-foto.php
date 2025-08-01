<?php
session_start();
include '../../config/config.php';
include '../../../config.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . $base_url . "index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nik = $_SESSION['nik'];
    $foto = $_FILES['foto'];

    // Validasi file
    $allowed = ['jpg', 'jpeg', 'png'];
    $filename = $foto['name'];
    $filesize = $foto['size'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if ($filesize > 10 * 1024 * 1024) { // 10MB
        $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 10MB.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Generate nama file baru
    $newfilename = $nik . '_' . time() . '.' . $ext;
    $upload_path = '../../assets/media/profile/' . $newfilename;

    if (move_uploaded_file($foto['tmp_name'], $upload_path)) {
        // Hapus foto lama jika ada
        if ($_SESSION['foto'] && $_SESSION['foto'] != 'default.png') {
            $old_file = '../../assets/media/profile/' . $_SESSION['foto'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Update database
        $query = "UPDATE account SET foto = ? WHERE nik = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("ss", $newfilename, $nik);

        if ($stmt->execute()) {
            $_SESSION['foto'] = $newfilename;
            $_SESSION['success'] = "success";
            $_SESSION['message'] = "Foto profil berhasil diperbarui!";
        } else {
            $_SESSION['error'] = "error";
            $_SESSION['message'] = "Gagal memperbarui foto profil!";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "error";
        $_SESSION['message'] = "Gagal mengupload file!";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Tambahkan script di bawah ini
if (isset($_SESSION['success'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '" . $_SESSION['message'] . "',
                confirmButtonColor: '#3085d6'
            });
        });
    </script>";
    unset($_SESSION['success']);
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '" . $_SESSION['message'] . "',
                confirmButtonColor: '#d33'
            });
        });
    </script>";
    unset($_SESSION['error']);
    unset($_SESSION['message']);
}