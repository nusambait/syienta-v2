<?php
include '../../config.php';
include '../config/config.php';

if(isset($_POST['nik'])) {
    $nik = mysqli_real_escape_string($connect, $_POST['nik']);
    
    $query = mysqli_query($connect, "SELECT nik FROM nasabah WHERE nik='$nik'");
    
    $response = array(
        'exists' => mysqli_num_rows($query) > 0
    );
    
    echo json_encode($response);
}
?>