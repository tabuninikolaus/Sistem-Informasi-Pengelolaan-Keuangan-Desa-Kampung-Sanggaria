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

$bulan = [
    'tahap_i' => [1, 3],
    'tahap_ii' => [4, 8],
    'tahap_iii' => [9, 12],
    'tahunan' => [null, null]
][$tahap];

$anggaran = getAnggaran($conn, $tahun);
$labelTahap = getLabelTahap($tahap);

$html = '<h2 style="text-align:center;">Laporan Keuangan Desa</h2>';
$html .= '<h3 style="text-align:center;">' . $labelTahap . ' - Tahun ' . $tahun . '</h3>';
$html .= '<br>';

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

    // Detail Transaksi
    $detail = getDetailTransaksi($conn, $a['id_anggaran'], $bulan[0], $bulan[1]);
    if (mysqli_num_rows($detail) > 0) {
        $html .= '<tr><td colspan="6"><b>Detail Transaksi:</b><br>';
        $html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%">
        <thead style="background-color:#e8e8e8;">
        <tr>
            <th>Tanggal</th>
            <th>Jumlah</th>
            <th>Keterangan</th>
            <th>Bukti</th>
        </tr>
        </thead><tbody>';
        while ($p = mysqli_fetch_assoc($detail)) {
            $buktiList = '';
            $bukti = json_decode($p['bukti_pengeluaran'], true);
            if ($bukti && is_array($bukti)) {
                foreach ($bukti as $file) {
                    $buktiList .= $file . "<br>";
                }
            }
            $html .= "<tr>
                <td>{$p['tanggal']}</td>
                <td>Rp " . number_format($p['jumlah'], 0, ',', '.') . "</td>
                <td>{$p['keterangan']}</td>
                <td>$buktiList</td>
            </tr>";
        }
        $html .= '</tbody></table></td></tr>';
    }

    $no++;
}

$html .= '</tbody></table>';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$nama_file = "Data_Laporan_" . str_replace('_', '_', strtoupper($tahap)) . "_$tahun.pdf";
$mpdf->Output($nama_file, 'D'); // 'D' = force download
