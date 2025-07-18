<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;
include '../config/koneksi.php';

$tahap = $_GET['tahap'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');
$mpdf = new Mpdf();

$html = '<h2 style="text-align:center;">Laporan Keuangan Desa</h2>';
$html .= "<p style='text-align:center;'>Tahap: <strong>" . strtoupper(str_replace('_', ' ', $tahap)) . "</strong> | Tahun: <strong>$tahun</strong></p><hr>";

// Mapping bulan per tahap
$mapping = [
  'tahap_i' => [1, 3],
  'tahap_ii' => [4, 8],
  'tahap_iii' => [9, 12],
  'tahunan' => [1, 12]
];
[$startMonth, $endMonth] = $mapping[$tahap] ?? [1, 12];

// Fungsi: Total pengeluaran valid
function getTotalPengeluaranValid($conn, $id_anggaran) {
  $q = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran = '$id_anggaran' AND status_detail_pengeluaran = 'Valid'");
  $d = mysqli_fetch_assoc($q);
  return $d['total'] ?? 0;
}

// Fungsi: Detail pengeluaran per tahap
function getPengeluaranTahap($conn, $id_anggaran, $startMonth, $endMonth, $tahun) {
  return mysqli_query($conn, "SELECT * FROM pengeluaran 
    WHERE id_anggaran = '$id_anggaran' 
    AND YEAR(tanggal) = '$tahun' 
    AND MONTH(tanggal) BETWEEN $startMonth AND $endMonth
    ORDER BY tanggal ASC");
}

// Ambil data anggaran
$data_anggaran = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN $startMonth AND $endMonth");
$anggaranList = [];
while ($row = mysqli_fetch_assoc($data_anggaran)) {
  $anggaranList[] = $row;
}

// Tambahan logika kegiatan tertunda
if ($tahap === 'tahap_ii') {
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 3");
  while ($row = mysqli_fetch_assoc($q)) {
    $total = getTotalPengeluaranValid($conn, $row['id_anggaran']);
    if ($total < $row['alokasi_dana']) $anggaranList[] = $row;
  }
} elseif ($tahap === 'tahap_iii') {
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 8");
  while ($row = mysqli_fetch_assoc($q)) {
    $total = getTotalPengeluaranValid($conn, $row['id_anggaran']);
    if ($total < $row['alokasi_dana']) $anggaranList[] = $row;
  }
} elseif ($tahap === 'tahunan') {
  $anggaranList = [];
  $q = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
  while ($row = mysqli_fetch_assoc($q)) {
    $anggaranList[] = $row;
  }
}

// ======================
// BAGIAN 1: TABEL REKAP
// ======================
$html .= '
<h4>Ringkasan Alokasi dan Progres Kegiatan</h4>
<table border="1" cellspacing="0" cellpadding="5" width="100%">
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Kegiatan</th>
      <th>Alokasi Dana</th>
      <th>Total Pengeluaran (Valid)</th>
      <th>Progres Kegiatan (%)</th>
    </tr>
  </thead>
  <tbody>
';

$no = 1;
foreach ($anggaranList as $a) {
  $alokasi = $a['alokasi_dana'];
  $total = getTotalPengeluaranValid($conn, $a['id_anggaran']);
  $persen = ($alokasi > 0) ? round(($total / $alokasi) * 100) : 0;
  $progres_text = ($persen == 100) ? "100% (Selesai)" : "$persen%";

  $html .= "<tr>
    <td>$no</td>
    <td>{$a['nama_kegiatan']}</td>
    <td>Rp " . number_format($alokasi, 0, ',', '.') . "</td>
    <td>Rp " . number_format($total, 0, ',', '.') . "</td>
    <td>$progres_text</td>
  </tr>";
  $no++;
}

$html .= '</tbody></table><br><br>';

// ===========================
// BAGIAN 2: DETAIL PENGELUARAN
// ===========================
$html .= '<h4>Detail Pengeluaran</h4>';
$html .= '
<table border="1" cellspacing="0" cellpadding="5" width="100%">
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Kegiatan</th>
      <th>Tanggal Pengeluaran</th>
      <th>Jumlah Pengeluaran</th>
      <th>Keterangan</th>
    </tr>
  </thead>
  <tbody>
';

$noDetail = 1;
foreach ($anggaranList as $a) {
  $pengeluaran = getPengeluaranTahap($conn, $a['id_anggaran'], $startMonth, $endMonth, $tahun);
  while ($p = mysqli_fetch_assoc($pengeluaran)) {
    $html .= "<tr>
      <td>$noDetail</td>
      <td>{$a['nama_kegiatan']}</td>
      <td>{$p['tanggal']}</td>
      <td>Rp " . number_format($p['jumlah'], 0, ',', '.') . "</td>
      <td>{$p['keterangan']}</td>
    </tr>";
    $noDetail++;
  }
}

if ($noDetail === 1) {
  $html .= '<tr><td colspan="5" style="text-align:center;"><em>Tidak ada transaksi</em></td></tr>';
}

$html .= '</tbody></table>';

$mpdf->WriteHTML($html);
$mpdf->Output("laporan_keuangan_{$tahap}_{$tahun}.pdf", 'I');
