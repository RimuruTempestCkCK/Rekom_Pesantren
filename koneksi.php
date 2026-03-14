<?php
$host   = "localhost";
$dbname = "rekom_pesantren";
$user   = "root";
$pass   = "";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>