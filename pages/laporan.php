<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

function getTahap($tanggal) {
    $bulan = date('n', strtotime($tanggal));
    if ($bulan >= 1 && $bulan <= 3) return 'Tahap I (Jan - Mar)';
    elseif ($bulan >= 4 && $bulan <= 8) return 'Tahap II (Apr - Aug)';
    else return 'Tahap III (Sep - Dec)';
}

function getAnggaranByPengeluaranTahap($conn, $start, $end) {
    $query = "
        SELECT DISTINCT a.*
        FROM anggaran a
        JOIN pengeluaran p ON a.id_anggaran = p.id_anggaran
        WHERE MONTH(p.tanggal) BETWEEN $start AND $end
    ";
    return mysqli_query($conn, $query);
}

function getPengeluaranTahap($conn, $id_anggaran, $startMonth, $endMonth) {
    $query = "SELECT * FROM pengeluaran WHERE id_anggaran='$id_anggaran' AND MONTH(tanggal) BETWEEN $startMonth AND $endMonth";
    return mysqli_query($conn, $query);
}

function getTotalPengeluaranTahap($conn, $id_anggaran, $startMonth, $endMonth) {
    $result = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran' AND MONTH(tanggal) BETWEEN $startMonth AND $endMonth");
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

function getTahunList($conn) {
    $tahunQuery = mysqli_query($conn, "SELECT DISTINCT tahun FROM anggaran ORDER BY tahun DESC");
    $tahunList = [];
    while ($t = mysqli_fetch_assoc($tahunQuery)) {
        $tahunList[] = $t['tahun'];
    }
    return $tahunList;
}

function getAnggaranByTahun($conn, $tahun) {
    return mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
}

function getTotalPengeluaranByAnggaran($conn, $id_anggaran) {
    $query = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran'");
    $row = mysqli_fetch_assoc($query);
    return $row['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="flex">
  <!-- Sidebar -->
<aside class="w-64 bg-purple-700 text-white min-h-screen shadow-lg">
  <div class="p-6 text-xl font-bold border-b border-purple-500">⚙️ Admin Panel</div>
  <nav class="flex flex-col p-4 space-y-2">
    <a href="dashboard_bendahara.php" class="hover:bg-purple-800 p-2 rounded">🏠 Dashboard</a>
  </nav>
</aside>

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-green-700">📋 Laporan Keuangan Pertahap</h1>
                <div>
                    <a href="export_excel.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg mr-2">📊 Excel</a>
                    <a href="export_pdf.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">📄 PDF</a>
                </div>
            </div>

            <?php
            $tahapan = [
                'Tahap I (Jan - Mar)' => [1, 3],
                'Tahap II (Apr - Aug)' => [4, 8],
                'Tahap III (Sep - Dec)' => [9, 12],
            ];

            foreach ($tahapan as $judul => [$start, $end]):
                $anggaran = getAnggaranByPengeluaranTahap($conn, $start, $end);
            ?>
            <div class="mb-10">
                <h2 class="text-lg font-semibold text-green-600 mb-2"><?= $judul ?></h2>
                <div class="overflow-auto">
                    <table class="w-full border text-sm">
                        <thead class="bg-green-100">
                            <tr>
                                <th class="border px-3 py-2">Nama Kegiatan</th>
                                <th class="border px-3 py-2">Alokasi Dana</th>
                                <th class="border px-3 py-2">Realisasi (%)</th>
                                <th class="border px-3 py-2">Detail Transaksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($a = mysqli_fetch_assoc($anggaran)): ?>
                            <?php
                            $total = getTotalPengeluaranTahap($conn, $a['id_anggaran'], $start, $end);
                            $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
                            $detail = getPengeluaranTahap($conn, $a['id_anggaran'], $start, $end);
                            ?>
                            <tr>
                                <td class="border px-3 py-2 align-top"><?= $a['nama_kegiatan'] ?></td>
                                <td class="border px-3 py-2 align-top">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
                                <td class="border px-3 py-2 align-top"><?= $persen ?>%</td>
                                <td class="border px-3 py-2">
                                    <?php if (mysqli_num_rows($detail) > 0): ?>
                                    <div class="overflow-auto max-h-48">
                                        <table class="text-xs border w-full">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="border px-2">Tanggal</th>
                                                    <th class="border px-2">Jumlah</th>
                                                    <th class="border px-2">Bukti</th>
                                                    <th class="border px-2">Keterangan</th>
                                                    <th class="border px-2">Nama Kegiatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($p = mysqli_fetch_assoc($detail)): ?>
                                                <tr>
                                                    <td class="border px-2"><?= $p['tanggal'] ?></td>
                                                    <td class="border px-2">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                                    <td class="border px-2">
                                                        <?php
                                                        $bukti = json_decode($p['bukti_pengeluaran'], true);
                                                        if ($bukti) {
                                                            foreach ($bukti as $file) {
                                                                echo "<a href='../uploads/$file' class='text-blue-500 underline block' target='_blank'>Lihat</a>";
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="border px-2"><?= $p['keterangan'] ?></td>
                                                    <td class="border px-2"><?= $a['nama_kegiatan'] ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                        <span class="italic text-gray-400">Tidak ada transaksi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Tahunan -->
            <h2 class="text-xl font-bold text-indigo-700 mb-4 mt-12">📁 Laporan Pertanggungjawaban Tahunan</h2>
            <form method="GET" class="mb-4">
                <label class="mr-2">Pilih Tahun:</label>
                <select name="tahun" onchange="this.form.submit()" class="px-3 py-1 border rounded">
                    <option disabled selected>-- Tahun --</option>
                    <?php foreach (getTahunList($conn) as $t): ?>
                        <option value="<?= $t ?>" <?= isset($_GET['tahun']) && $_GET['tahun'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if (isset($_GET['tahun'])):
                $tahun = $_GET['tahun'];
                $data = getAnggaranByTahun($conn, $tahun);
            ?>
            <div class="overflow-auto">
                <table class="w-full border text-sm">
                    <thead class="bg-indigo-100">
                        <tr>
                            <th class="border px-3 py-2">Nama Kegiatan</th>
                            <th class="border px-3 py-2">Tahun</th>
                            <th class="border px-3 py-2">Alokasi Dana</th>
                            <th class="border px-3 py-2">Total Pengeluaran</th>
                            <th class="border px-3 py-2">Realisasi (%)</th>
                            <th class="border px-3 py-2">Sisa Dana</th>
                            <th class="border px-3 py-2">Jumlah Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($a = mysqli_fetch_assoc($data)):
                        $total = getTotalPengeluaranByAnggaran($conn, $a['id_anggaran']);
                        $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
                        $sisa = $a['alokasi_dana'] - $total;

                        $bukti = mysqli_query($conn, "SELECT bukti_pengeluaran FROM pengeluaran WHERE id_anggaran='{$a['id_anggaran']}'");
                        $jumlahBukti = 0;
                        while ($b = mysqli_fetch_assoc($bukti)) {
                            $files = json_decode($b['bukti_pengeluaran'], true);
                            if (is_array($files)) $jumlahBukti += count($files);
                        }
                    ?>
                        <tr>
                            <td class="border px-3 py-2"><?= $a['nama_kegiatan'] ?></td>
                            <td class="border px-3 py-2"><?= $a['tahun'] ?></td>
                            <td class="border px-3 py-2">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
                            <td class="border px-3 py-2">Rp <?= number_format($total, 0, ',', '.') ?></td>
                            <td class="border px-3 py-2"><?= $persen ?>%</td>
                            <td class="border px-3 py-2">Rp <?= number_format($sisa, 0, ',', '.') ?></td>
                            <td class="border px-3 py-2"><?= $jumlahBukti ?> File</td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
