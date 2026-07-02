<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM log_harian WHERE id = '$id'";
    mysqli_query($koneksi, $query);
    
    header("Location: index.php?pesan=log_dihapus");
}
?>