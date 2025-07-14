<?php
include '../config/koneksi.php';

$tahun = $_GET['tahun'];
$query = mysqli_query($conn, "SELECT 
    IFNULL(SUM(jumlah), 0) AS total_pemasukan,
    IFNULL((SELECT SUM(jumlah) FROM pengeluaran WHERE YEAR(tanggal) = '$tahun'), 0) AS total_pengeluaran,
    IFNULL((SELECT COUNT(*) FROM anggaran WHERE tahun = '$tahun'), 0) AS jumlah_kegiatan
  FROM pemasukan
  WHERE YEAR(tanggal) = '$tahun'");

$data = mysqli_fetch_assoc($query);
$data['sisa_dana'] = $data['total_pemasukan'] - $data['total_pengeluaran'];

echo json_encode($data);
?>
