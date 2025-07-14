<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;
include '../config/koneksi.php';

$tahap = $_GET['tahap'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');
$mpdf = new Mpdf();

$html = '<h2 style="text-align:center;">Laporan Keuangan Desa</h2>';
$html .= "<p style='text-align:center;'>Tahap: <strong>" . strtoupper(str_replace('_', ' ', $tahap)) . "</strong> | Tahun: <strong>$tahun</strong></p><hr>";

$html .= '
<table border="1" cellspacing="0" cellpadding="5" width="100%">
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Kegiatan</th>
      <th>Alokasi Dana</th>
      <th>Total Pengeluaran</th>
      <th>Realisasi (%)</th>
    </tr>
  </thead>
  <tbody>
';

// Fungsi untuk hitung total pengeluaran
function getTotalPengeluaran($conn, $id_anggaran) {
  $q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran = '$id_anggaran'");
  $d = mysqli_fetch_assoc($q);
  return $d['total'] ?? 0;
}

// Mapping bulan per tahap
$mapping = [
  'tahap_i' => [1, 3],
  'tahap_ii' => [4, 8],
  'tahap_iii' => [9, 12],
  'tahunan' => [1, 12]
];

[$startMonth, $endMonth] = $mapping[$tahap] ?? [1, 12];

// Ambil anggaran utama dari periode tahap
$data_anggaran = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN $startMonth AND $endMonth");

$anggaranList = [];
while ($row = mysqli_fetch_assoc($data_anggaran)) {
  $anggaranList[] = $row;
}

// Tambahan untuk Tahap II: kegiatan dari Tahap I (Jan-Mar) yang belum 100%
if ($tahap === 'tahap_ii') {
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 3");
  while ($row = mysqli_fetch_assoc($q)) {
    $total = getTotalPengeluaran($conn, $row['id_anggaran']);
    if ($total < $row['alokasi_dana']) {
      $anggaranList[] = $row;
    }
  }
}

// Tambahan untuk Tahap III: kegiatan dari Tahap I dan II (Jan–Aug) yang belum 100%
if ($tahap === 'tahap_iii') {
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 8");
  while ($row = mysqli_fetch_assoc($q)) {
    $total = getTotalPengeluaran($conn, $row['id_anggaran']);
    if ($total < $row['alokasi_dana']) {
      $anggaranList[] = $row;
    }
  }
}

// Tambahan untuk Tahunan: semua data tahun berjalan (Jan–Des)
if ($tahap === 'tahunan') {
  $anggaranList = [];
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
  while ($row = mysqli_fetch_assoc($q)) {
    $anggaranList[] = $row;
  }
}

$no = 1;
foreach ($anggaranList as $a) {
  $alokasi = $a['alokasi_dana'];
  $total = getTotalPengeluaran($conn, $a['id_anggaran']);
  $persen = ($alokasi > 0) ? round(($total / $alokasi) * 100) : 0;

  $html .= "<tr>
    <td>$no</td>
    <td>{$a['nama_kegiatan']}</td>
    <td>Rp " . number_format($alokasi, 0, ',', '.') . "</td>
    <td>Rp " . number_format($total, 0, ',', '.') . "</td>
    <td>$persen%</td>
  </tr>";
  $no++;
}

$html .= '</tbody></table>';
$mpdf->WriteHTML($html);
$mpdf->Output("laporan_keuangan_{$tahap}_{$tahun}.pdf", 'I');
