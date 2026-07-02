<?php
$host     = "sql201.infinityfree.com";      
$user     = "if0_42323041";                 
$password = "arjKel10";       
$database = "if0_42323041_XXX";        
$koneksi = mysqli_connect($host, $user, $password, $database);


if (!$koneksi) {
    die("Koneksi database online gagal: " . mysqli_connect_error());
}
?>