<?php
// konfigurasi database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'uts_perpustakaan_60324001');
 
// tambah koneksi
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// cek kembali koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
 
// Set charset
$conn->set_charset("utf8mb4");
 
// function helper
function escape($conn, $data) {
    return htmlspecialchars($conn->real_escape_string($data), ENT_QUOTES, 'UTF-8');
}
?>