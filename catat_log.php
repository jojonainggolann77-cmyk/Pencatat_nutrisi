<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $makanan_id = $_POST['makanan_id'];
    $porsi      = $_POST['porsi'];
    $tanggal    = date("Y-m-d"); 

    $query = "INSERT INTO log_harian (makanan_id, tanggal, porsi) VALUES ('$makanan_id', '$tanggal', '$porsi')";
    mysqli_query($koneksi, $query);
    
    header("Location: index.php?pesan=log_ditambah");
}
?>