<?php
require_once 'config/database.php';
 
//cek id dari GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=" . urlencode("ID Kategori tidak valid."));
    exit();
}

$id_kategori = $_GET['id'];
 
//cek posisi data
$cek_query = "SELECT id_kategori FROM kategori WHERE id_kategori = ?";
$stmt_cek = $conn->prepare($cek_query);
$stmt_cek->bind_param("i", $id_kategori);
$stmt_cek->execute();
$result = $stmt_cek->get_result();

if ($result->num_rows == 0) {
    $stmt_cek->close();
    header("Location: index.php?error=" . urlencode("Data kategori tidak ditemukan di database."));
    exit();
}
$stmt_cek->close();
 
//delete data
//menggunakan prepared statement dan cek affectedrows
$delete_query = "DELETE FROM kategori WHERE id_kategori = ?";
$stmt_delete = $conn->prepare($delete_query);
$stmt_delete->bind_param("i", $id_kategori);
$stmt_delete->execute();
 
//direct dengan pesan
if ($stmt_delete->affected_rows > 0) {
    $stmt_delete->close();
    header("Location: index.php?success=" . urlencode("Data kategori berhasil dihapus."));
    exit();
} else {
    $stmt_delete->close();
    header("Location: index.php?error=" . urlencode("Gagal menghapus data kategori."));
    exit();
}
?>