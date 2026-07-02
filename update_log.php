<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id    = $_POST['id'];
    $porsi = $_POST['porsi'];

    $query = "UPDATE log_harian SET porsi = '$porsi' WHERE id = '$id'";
    mysqli_query($koneksi, $query);
    
    header("Location: index.php?pesan=log_diupdate");
}
?>