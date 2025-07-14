<?php
require_once __DIR__ . '/../vendor/autoload.php';
include '../config/koneksi.php';

$tahap = $_GET['tahap'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

function getLabelTahap($tahap) {
    return [
        'tahap_i' => 'Tahap I (Jan - Mar)',
        'tahap_ii' => 'Tahap II (Apr - Aug)',
        'tahap_iii' => 'Tahap III (Sep - Dec)',
        'tahunan' => 'LPJ Tahunan'
    ][$tahap] ?? $tahap;
}

function getAnggaran($conn, $tahun) {
    return mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
}

function getTotalPengeluaran($conn, $id_anggaran, $start = null, $end = null) {
    $sql = "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran'";
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

// Tentukan bulan berdasarkan tahap
$bulan = [
    'tahap_i' => [1, 3],
    'tahap_ii' => [4, 8],
    'tahap_iii' => [9, 12],
    'tahunan' => [null, null]
][$tahap];

$anggaran = getAnggaran($conn, $tahun);
$labelTahap = getLabelTahap($tahap);

$html = '<h2 style="text-align:center;">Laporan Keuangan Desa</h2>';
$html .= '<h3 style="text-align:center;">' . $labelTahap . ' - Tahun ' . $tahun . '</h3><br>';

$html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">
<thead style="background-color:#f0f0f0;">
<tr>
<th>No</th>
<th>Nama Kegiatan</th>
<th>Alokasi Dana</th>
<th>Total Pengeluaran</th>
<th>Realisasi (%)</th>
<th>Sisa Dana</th>
</tr>
</thead><tbody>';

$no = 1;
while ($a = mysqli_fetch_assoc($anggaran)) {
    $total = getTotalPengeluaran($conn, $a['id_anggaran'], $bulan[0], $bulan[1]);
    $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
    $sisa = $a['alokasi_dana'] - $total;

    $html .= "<tr>
        <td>$no</td>
        <td>{$a['nama_kegiatan']}</td>
        <td>Rp " . number_format($a['alokasi_dana'], 0, ',', '.') . "</td>
        <td>Rp " . number_format($total, 0, ',', '.') . "</td>
        <td>{$persen}%</td>
        <td>Rp " . number_format($sisa, 0, ',', '.') . "</td>
    </tr>";
    $no++;
}
$html .= '</tbody></table>';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);

// Simpan ke folder publik
$nama_file = "Data_Laporan_" . strtoupper($tahap) . "_$tahun.pdf";
$lokasi_simpan = __DIR__ . '/../laporan_publik/' . $nama_file;
$mpdf->Output($lokasi_simpan, 'F'); // Simpan ke folder, bukan download

// Redirect balik ke halaman laporan
header("Location: laporan.php?tahun=$tahun&status=sukses_share");
exit;
?>
