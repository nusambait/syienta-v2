<?php
session_start();
include '../../config.php';

if(isset($_GET['nik'])) {
    $nik = mysqli_real_escape_string($connect, $_GET['nik']);
    
    $query = mysqli_query($connect, "DELETE FROM nasabah WHERE nik='$nik'");
    
    if($query) {
        echo "<script>
            window.location.href='input-nasabah.php';
            alert('Data nasabah berhasil dihapus!');
        </script>";
    } else {
        echo "<script>
            window.location.href='input-nasabah.php';
            alert('Gagal menghapus data nasabah!');
        </script>";
    }
} else {
    header("Location: input-nasabah.php");
}
?>