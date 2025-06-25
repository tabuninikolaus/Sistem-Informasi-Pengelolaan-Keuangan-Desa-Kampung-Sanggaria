<?php
include '../config/koneksi.php';

// Ambil parameter
$tahap = $_GET['tahap'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

// Nama file yang akan diunduh
$labelFile = [
    'tahap_i' => 'Data_Laporan_Tahap_I',
    'tahap_ii' => 'Data_Laporan_Tahap_II',
    'tahap_iii' => 'Data_Laporan_Tahap_III',
    'tahunan' => 'Data_Laporan_Tahunan'
][$tahap] ?? 'Laporan';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename={$labelFile}_{$tahun}.xls");

// Fungsi ambil data anggaran
function getAnggaran($conn, $tahun) {
    return mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
}

// Fungsi ambil total pengeluaran
function getTotalPengeluaran($conn, $id_anggaran, $start = null, $end = null) {
    $sql = "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran'";
    if ($start && $end) {
        $sql .= " AND MONTH(tanggal) BETWEEN $start AND $end";
    }
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

// Ambil detail transaksi
function getDetailTransaksi($conn, $id_anggaran, $start = null, $end = null) {
    $sql = "SELECT * FROM pengeluaran WHERE id_anggaran='$id_anggaran'";
    if ($start && $end) {
        $sql .= " AND MONTH(tanggal) BETWEEN $start AND $end";
    }
    return mysqli_query($conn, $sql);
}

// Set rentang bulan berdasarkan tahap
$bulan = [
    'tahap_i' => [1, 3],
    'tahap_ii' => [4, 8],
    'tahap_iii' => [9, 12],
    'tahunan' => [null, null]
][$tahap];

$anggaran = getAnggaran($conn, $tahun);
?>

<table border="1">
  <thead style="background-color:#d9edf7;">
    <tr>
      <th colspan="6">Laporan Keuangan - <?= strtoupper(str_replace('_', ' ', $tahap)) ?> - Tahun <?= $tahun ?></th>
    </tr>
    <tr>
      <th>No</th>
      <th>Nama Kegiatan</th>
      <th>Alokasi Dana</th>
      <th>Total Pengeluaran</th>
      <th>Realisasi (%)</th>
      <th>Sisa Dana</th>
    </tr>
  </thead>
  <tbody>
<?php
$no = 1;
while ($a = mysqli_fetch_assoc($anggaran)):
  $total = getTotalPengeluaran($conn, $a['id_anggaran'], $bulan[0], $bulan[1]);
  $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
  $sisa = $a['alokasi_dana'] - $total;
?>
  <tr>
    <td><?= $no++ ?></td>
    <td><?= $a['nama_kegiatan'] ?></td>
    <td><?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
    <td><?= number_format($total, 0, ',', '.') ?></td>
    <td><?= $persen ?>%</td>
    <td><?= number_format($sisa, 0, ',', '.') ?></td>
  </tr>
  <!-- Detail Transaksi -->
  <tr>
    <td colspan="6">
      <b>Detail Transaksi:</b>
      <table border="1" width="100%">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Jumlah</th>
            <th>Keterangan</th>
            <th>Bukti</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $detail = getDetailTransaksi($conn, $a['id_anggaran'], $bulan[0], $bulan[1]);
        if (mysqli_num_rows($detail) > 0):
          while ($p = mysqli_fetch_assoc($detail)):
            $buktiList = '';
            $bukti = json_decode($p['bukti_pengeluaran'], true);
            if ($bukti && is_array($bukti)) {
              foreach ($bukti as $file) {
                $buktiList .= $file . '<br>';
              }
            }
        ?>
          <tr>
            <td><?= $p['tanggal'] ?></td>
            <td><?= number_format($p['jumlah'], 0, ',', '.') ?></td>
            <td><?= $p['keterangan'] ?></td>
            <td><?= $buktiList ?></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="4"><i>Tidak ada transaksi</i></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </td>
  </tr>
<?php endwhile; ?>
</tbody>
</table>
