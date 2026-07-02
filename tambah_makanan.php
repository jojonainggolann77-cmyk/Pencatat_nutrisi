<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kalori = $_POST['kalori'];
    $protein = $_POST['protein'];
    $karbo = $_POST['karbohidrat'];
    $lemak = $_POST['lemak'];
    $serat = $_POST['serat'];

    $query = "INSERT INTO makanan (nama, kalori, protein, karbohidrat, lemak, serat) 
              VALUES ('$nama', '$kalori', '$protein', '$karbo', '$lemak', '$serat')";
    
    mysqli_query($koneksi, $query);
    header("Location: index.php?pesan=makanan_ditambah");
}
?>