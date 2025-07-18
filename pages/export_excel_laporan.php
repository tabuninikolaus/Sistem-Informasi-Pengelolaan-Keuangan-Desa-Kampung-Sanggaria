<?php
include '../config/koneksi.php';

$tahap = $_GET['tahap'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

$labelFile = [
  'tahap_i' => 'Data_Laporan_Tahap_I',
  'tahap_ii' => 'Data_Laporan_Tahap_II',
  'tahap_iii' => 'Data_Laporan_Tahap_III',
  'tahunan' => 'Data_Laporan_Tahunan'
][$tahap] ?? 'Laporan';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename={$labelFile}_{$tahun}.xls");
header("Pragma: no-cache");
header("Expires: 0");

function getTotalPengeluaranValid($conn, $id_anggaran, $start = null, $end = null) {
  $sql = "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran' AND status_detail_pengeluaran='Valid'";
  if ($start && $end) {
    $sql .= " AND MONTH(tanggal) BETWEEN $start AND $end";
  }
  $result = mysqli_query($conn, $sql);
  $row = mysqli_fetch_assoc($result);
  return $row['total'] ?? 0;
}

function getDetailTransaksi($conn, $id_anggaran, $start = null, $end = null) {
  $sql = "SELECT * FROM pengeluaran WHERE id_anggaran='$id_anggaran'";
  if ($start && $end) {
    $sql .= " AND MONTH(tanggal) BETWEEN $start AND $end";
  }
  return mysqli_query($conn, $sql);
}

$bulan = [
  'tahap_i' => [1, 3],
  'tahap_ii' => [4, 8],
  'tahap_iii' => [9, 12],
  'tahunan' => [null, null]
][$tahap] ?? [null, null];

$anggaranList = [];
$q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND (" .
    ($bulan[0] ? "MONTH(tanggal_input_kegiatan) BETWEEN {$bulan[0]} AND {$bulan[1]}" : "1=1") .
")");
while ($row = mysqli_fetch_assoc($q)) {
  $anggaranList[] = $row;
}

if ($tahap === 'tahap_ii') {
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 3");
  while ($row = mysqli_fetch_assoc($q)) {
    $total = getTotalPengeluaranValid($conn, $row['id_anggaran']);
    if ($total < $row['alokasi_dana']) {
      $anggaranList[] = $row;
    }
  }
}

if ($tahap === 'tahap_iii') {
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 8");
  while ($row = mysqli_fetch_assoc($q)) {
    $total = getTotalPengeluaranValid($conn, $row['id_anggaran']);
    if ($total < $row['alokasi_dana']) {
      $anggaranList[] = $row;
    }
  }
}
?>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
  <thead>
    <tr style="background-color: #d9edf7;">
      <th colspan="6">Laporan Keuangan - <?= strtoupper(str_replace('_', ' ', $tahap)) ?> - Tahun <?= $tahun ?></th>
    </tr>
    <tr style="background-color: #f2f2f2;">
      <th>No</th>
      <th>Nama Kegiatan</th>
      <th>Alokasi Dana</th>
      <th>Total Pengeluaran</th>
      <th>Progres Kegiatan</th>
      <th>Sisa Dana</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($anggaranList as $a):
      $alokasi = $a['alokasi_dana'];
      $total = getTotalPengeluaranValid($conn, $a['id_anggaran'], $bulan[0], $bulan[1]);
      $persen = $alokasi > 0 ? round(($total / $alokasi) * 100) : 0;
      $sisa = $alokasi - $total;
      $label = ($persen === 100) ? '100% (Selesai)' : "$persen%";
    ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= $a['nama_kegiatan'] ?></td>
      <td>Rp <?= number_format($alokasi, 0, ',', '.') ?></td>
      <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
      <td><?= $label ?></td>
      <td>Rp <?= number_format($sisa, 0, ',', '.') ?></td>
    </tr>
    <tr>
      <td colspan="6">
        <strong>Detail Pengeluaran:</strong>
        <table border="1" cellpadding="4" cellspacing="0" width="100%">
          <thead style="background-color: #e8f5e9;">
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
              <td>Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
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
    <?php endforeach; ?>
  </tbody>
</table>
