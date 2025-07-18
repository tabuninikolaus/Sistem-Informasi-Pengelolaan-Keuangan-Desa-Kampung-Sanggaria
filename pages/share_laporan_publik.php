<?php
require_once __DIR__ . '/../vendor/autoload.php';
include '../config/koneksi.php';

$tahap = $_GET['tahap'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

$valid_tahapan = ['tahap_i', 'tahap_ii', 'tahap_iii', 'tahunan'];
if (!in_array($tahap, $valid_tahapan)) {
    die("Tahap tidak valid");
}

function getLabelTahap($tahap) {
    return [
        'tahap_i' => 'Tahap I (Jan - Mar)',
        'tahap_ii' => 'Tahap II (Apr - Aug)',
        'tahap_iii' => 'Tahap III (Sep - Dec)',
        'tahunan' => 'LPJ Tahunan'
    ][$tahap] ?? $tahap;
}

function getTotalPengeluaranValidTahap($conn, $id_anggaran, $startMonth, $endMonth) {
    $result = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran' AND status_detail_pengeluaran='Valid' AND MONTH(tanggal) BETWEEN $startMonth AND $endMonth");
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

function getTotalPengeluaranValidByAnggaran($conn, $id_anggaran) {
    $query = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran' AND status_detail_pengeluaran='Valid'");
    $row = mysqli_fetch_assoc($query);
    return $row['total'] ?? 0;
}

function getAnggaranByTahapCustom($conn, $startMonth, $endMonth, $tahun, $tahap_key) {
    $tahun_lalu = $tahun - 1;
    $anggaran = [];

    $q1 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN $startMonth AND $endMonth");
    while ($row = mysqli_fetch_assoc($q1)) {
        $anggaran[] = $row;
    }

    if ($tahap_key === 'tahap_i') {
        $q2 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun_lalu' AND MONTH(tanggal_input_kegiatan) BETWEEN 9 AND 12");
        while ($row = mysqli_fetch_assoc($q2)) {
            $alokasi = $row['alokasi_dana'];
            $total = getTotalPengeluaranValidTahap($conn, $row['id_anggaran'], 1, 12);
            if ($total < $alokasi) {
                $anggaran[] = $row;
            }
        }
    }

    if ($tahap_key === 'tahap_ii') {
        $q3 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 3");
        while ($row = mysqli_fetch_assoc($q3)) {
            $alokasi = $row['alokasi_dana'];
            $total = getTotalPengeluaranValidTahap($conn, $row['id_anggaran'], 1, 12);
            if ($total < $alokasi) {
                $anggaran[] = $row;
            }
        }
    } elseif ($tahap_key === 'tahap_iii') {
        $q3 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 8");
        while ($row = mysqli_fetch_assoc($q3)) {
            $alokasi = $row['alokasi_dana'];
            $total = getTotalPengeluaranValidTahap($conn, $row['id_anggaran'], 1, 12);
            if ($total < $alokasi) {
                $anggaran[] = $row;
            }
        }
    }

    return $anggaran;
}

function getAnggaranByTahun($conn, $tahun) {
    return mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
}

$labelTahap = getLabelTahap($tahap);
$html = '<h2 style="text-align:center;">Laporan Keuangan Desa</h2>';
$html .= '<h3 style="text-align:center;">' . $labelTahap . ' - Tahun ' . $tahun . '</h3><br>';
$html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">
<thead style="background-color:#f0f0f0;">
<tr>
<th>No</th>
<th>Nama Kegiatan</th>
<th>Alokasi Dana</th>
<th>Total Pengeluaran Valid</th>
<th>Progres Kegiatan</th>
<th>Sisa Dana</th>
</tr>
</thead><tbody>';

$no = 1;
if ($tahap === 'tahunan') {
    $anggaran = getAnggaranByTahun($conn, $tahun);
    while ($a = mysqli_fetch_assoc($anggaran)) {
        $total = getTotalPengeluaranValidByAnggaran($conn, $a['id_anggaran']);
        $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
        $sisa = $a['alokasi_dana'] - $total;
        $status = $persen === 100 ? '100% (Selesai)' : $persen . '%';
        $html .= "<tr>
            <td>$no</td>
            <td>{$a['nama_kegiatan']}</td>
            <td>Rp " . number_format($a['alokasi_dana'], 0, ',', '.') . "</td>
            <td>Rp " . number_format($total, 0, ',', '.') . "</td>
            <td>{$status}</td>
            <td>Rp " . number_format($sisa, 0, ',', '.') . "</td>
        </tr>";
        $no++;
    }
} else {
    $bulan_range = [
        'tahap_i' => [1, 3],
        'tahap_ii' => [4, 8],
        'tahap_iii' => [9, 12],
    ];
    [$start, $end] = $bulan_range[$tahap];
    $anggaran = getAnggaranByTahapCustom($conn, $start, $end, $tahun, $tahap);

    foreach ($anggaran as $a) {
        $total = getTotalPengeluaranValidTahap($conn, $a['id_anggaran'], 1, $end);
        $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
        $sisa = $a['alokasi_dana'] - $total;
        $status = $persen === 100 ? '100% (Selesai)' : $persen . '%';
        $html .= "<tr>
            <td>$no</td>
            <td>{$a['nama_kegiatan']}</td>
            <td>Rp " . number_format($a['alokasi_dana'], 0, ',', '.') . "</td>
            <td>Rp " . number_format($total, 0, ',', '.') . "</td>
            <td>{$status}</td>
            <td>Rp " . number_format($sisa, 0, ',', '.') . "</td>
        </tr>";
        $no++;
    }
}
$html .= '</tbody></table>';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);

$folder = __DIR__ . '/../laporan_publik/';
if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
}

$nama_file = "Laporan_" . strtoupper($tahap) . "_" . $tahun . ".pdf";
$lokasi_simpan = $folder . $nama_file;
$mpdf->Output($lokasi_simpan, 'F');

ob_clean();
header("Location: laporan.php?tahun=$tahun&status=sukses_share");
exit;
