<?php
include '../config/koneksi.php';
session_start();

// if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'kades') {
//     header("Location: login.php");
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ajuan'])) {
    $id = $_POST['id_ajuan'];
    $status = $_POST['action'];
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

    $allowed = ['disetujui', 'ditolak'];
    if (!in_array($status, $allowed)) {
        die("Aksi tidak valid.");
    }

    $query = "UPDATE laporan_ajuan SET 
        status_ajuan = '$status',
        tanggal_verifikasi = NOW(),
        alasan_penerimaan_laporan = " . ($status === 'disetujui' ? "'$catatan'" : "NULL") . ",
        alasan_penolakan_laporan = " . ($status === 'ditolak' ? "'$catatan'" : "NULL") . "
        WHERE id_ajuan = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: verifikasi_laporan.php");
        exit;
    } else {
        die("Gagal memperbarui status: " . mysqli_error($conn));
    }
}

function getLaporanMenunggu($conn, $jenis, $tahap = null, $tahun = null) {
    $where = "jenis_laporan='$jenis'";
    if ($tahap !== null) $where .= " AND tahap='$tahap'";
    if ($tahun !== null) $where .= " AND tahun='$tahun'";
    $query = "SELECT * FROM laporan_ajuan WHERE $where AND status_ajuan = 'menunggu' ORDER BY tanggal_pengajuan DESC LIMIT 1";
    return mysqli_fetch_assoc(mysqli_query($conn, $query));
}

function getAnggaranByTahap($conn, $tahun, $start, $end) {
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

$tahun = date('Y');
$tahapan = [
    'tahap_i' => ['Tahap I (Jan - Mar)', 1, 3],
    'tahap_ii' => ['Tahap II (Apr - Aug)', 4, 8],
    'tahap_iii' => ['Tahap III (Sep - Dec)', 9, 12],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Verifikasi Laporan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function openModal(id, action) {
      document.getElementById('modal-id').value = id;
      document.getElementById('modal-action').value = action;
      document.getElementById('modal-title').innerText = action === 'disetujui' ? 'Setujui Laporan' : 'Tolak Laporan';
      document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
    }
  </script>
</head>
<body class="bg-gray-100 p-6">
  <h1 class="text-3xl font-bold text-green-700 mb-6">📋 Verifikasi Laporan Masuk</h1>

  <?php foreach ($tahapan as $kode => [$label, $start, $end]): ?>
    <?php $laporan = getLaporanMenunggu($conn, 'pertahap', $kode, $tahun); ?>
    <?php if ($laporan): ?>
      <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="text-xl font-semibold text-green-600 mb-2">Laporan <?= $label ?> (<?= $tahun ?>)</h2>
        <div class="overflow-auto">
          <table class="w-full text-sm border">
            <thead class="bg-green-100">
              <tr>
                <th class="border px-2 py-1">No</th>
                <th class="border px-2 py-1">Nama Kegiatan</th>
                <th class="border px-2 py-1">Tahun Anggaran</th>
                <th class="border px-2 py-1">Alokasi Dana</th>
                <th class="border px-2 py-1">Total Pengeluaran</th>
                <th class="border px-2 py-1">Realisasi (%)</th>
                <th class="border px-2 py-1">Sisa Dana</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $anggaran = getAnggaranByTahap($conn, $tahun, $start, $end);
              $no = 1;
              while ($a = mysqli_fetch_assoc($anggaran)):
                $total = getTotalPengeluaran($conn, $a['id_anggaran'], $start, $end);
                $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
                $sisa = $a['alokasi_dana'] - $total;
              ?>
              <tr>
                <td class="border px-2 py-1 text-center"><?= $no++ ?></td>
                <td class="border px-2 py-1"><?= $a['nama_kegiatan'] ?></td>
                <td class="border px-2 py-1 text-center"><?= $a['tahun'] ?></td>
                <td class="border px-2 py-1">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
                <td class="border px-2 py-1">Rp <?= number_format($total, 0, ',', '.') ?></td>
                <td class="border px-2 py-1 text-center"><?= $persen ?>%</td>
                <td class="border px-2 py-1">Rp <?= number_format($sisa, 0, ',', '.') ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-4 flex gap-2">
          <button onclick="openModal('<?= $laporan['id_ajuan'] ?>', 'disetujui')" class="bg-green-600 text-white px-4 py-2 rounded">Setujui</button>
          <button onclick="openModal('<?= $laporan['id_ajuan'] ?>', 'ditolak')" class="bg-red-600 text-white px-4 py-2 rounded">Tolak</button>
        </div>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php $lpj = getLaporanMenunggu($conn, 'tahunan', null, $tahun); ?>
  <?php if ($lpj): ?>
    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="text-xl font-semibold text-indigo-600 mb-2">LPJ Tahunan (<?= $tahun ?>)</h2>
      <div class="overflow-auto">
        <table class="w-full text-sm border">
          <thead class="bg-indigo-100">
            <tr>
              <th class="border px-2 py-1">No</th>
              <th class="border px-2 py-1">Nama Kegiatan</th>
              <th class="border px-2 py-1">Alokasi Dana</th>
              <th class="border px-2 py-1">Total Pengeluaran</th>
              <th class="border px-2 py-1">Realisasi (%)</th>
              <th class="border px-2 py-1">Sisa Dana</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $data = getAnggaranByTahap($conn, $tahun, null, null);
            $no = 1;
            while ($a = mysqli_fetch_assoc($data)):
              $total = getTotalPengeluaran($conn, $a['id_anggaran']);
              $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
              $sisa = $a['alokasi_dana'] - $total;
            ?>
            <tr>
              <td class="border px-2 py-1 text-center"><?= $no++ ?></td>
              <td class="border px-2 py-1"><?= $a['nama_kegiatan'] ?></td>
              <td class="border px-2 py-1">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
              <td class="border px-2 py-1">Rp <?= number_format($total, 0, ',', '.') ?></td>
              <td class="border px-2 py-1 text-center"><?= $persen ?>%</td>
              <td class="border px-2 py-1">Rp <?= number_format($sisa, 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div class="mt-4 flex gap-2">
        <button onclick="openModal('<?= $lpj['id_ajuan'] ?>', 'disetujui')" class="bg-green-600 text-white px-4 py-2 rounded">Setujui</button>
        <button onclick="openModal('<?= $lpj['id_ajuan'] ?>', 'ditolak')" class="bg-red-600 text-white px-4 py-2 rounded">Tolak</button>
      </div>
    </div>
  <?php endif; ?>

  <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white w-96 p-6 rounded shadow-lg">
      <h2 id="modal-title" class="text-lg font-bold mb-2 text-green-700">Verifikasi Laporan</h2>
      <form method="post" action="">
        <input type="hidden" name="id_ajuan" id="modal-id">
        <input type="hidden" name="action" id="modal-action">
        <label class="block mb-2 font-semibold">Catatan:</label>
        <textarea name="catatan" class="w-full border rounded p-2 mb-4" placeholder="Tulis catatan atau alasan..."></textarea>
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeModal()" class="px-4 py-1 bg-gray-400 rounded text-white">Batal</button>
          <button type="submit" class="px-4 py-1 bg-green-600 rounded text-white hover:bg-green-700">Kirim</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
