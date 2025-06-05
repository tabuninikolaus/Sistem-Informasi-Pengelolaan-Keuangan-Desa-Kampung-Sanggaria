<?php
include '../config/koneksi.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_keuangan.xls");

$tahun = $_GET['tahun'] ?? date('Y');
$data = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
?>

<table border="1">
    <thead>
        <tr>
            <th>Nama Kegiatan</th>
            <th>Tahun</th>
            <th>Alokasi Dana</th>
            <th>Total Pengeluaran</th>
            <th>Realisasi (%)</th>
            <th>Sisa Dana</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($a = mysqli_fetch_assoc($data)): 
            $id = $a['id_anggaran'];
            $alokasi = $a['alokasi_dana'];
            $totalPengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id'"))['total'] ?? 0;
            $persen = $alokasi > 0 ? round($totalPengeluaran / $alokasi * 100) : 0;
            $sisa = $alokasi - $totalPengeluaran;
        ?>
        <tr>
            <td><?= $a['nama_kegiatan'] ?></td>
            <td><?= $a['tahun'] ?></td>
            <td><?= $alokasi ?></td>
            <td><?= $totalPengeluaran ?></td>
            <td><?= $persen ?>%</td>
            <td><?= $sisa ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
