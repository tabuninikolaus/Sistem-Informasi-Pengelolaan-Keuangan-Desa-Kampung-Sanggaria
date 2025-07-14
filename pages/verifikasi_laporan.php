<?php
include '../config/koneksi.php';
session_start();

// Akses terbatas
// if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'kades') {
//     header("Location: login.php");
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_ajuan'])) {
    $id = $_POST['id_ajuan'];
    $status = $_POST['action'];
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

    if (!in_array($status, ['disetujui', 'ditolak'])) {
        die("Aksi tidak valid.");
    }

    $query = "UPDATE laporan_ajuan SET 
        status_ajuan = '$status',
        tanggal_verifikasi = NOW(),
        alasan_penerimaan_laporan = " . ($status === 'disetujui' ? "'$catatan'" : "NULL") . ",
        alasan_penolakan_laporan = " . ($status === 'ditolak' ? "'$catatan'" : "NULL") . "
        WHERE id_ajuan = '$id'";

    if (mysqli_query($conn, $query)) {
        mysqli_query($conn, "INSERT INTO log_verifikasi 
        (jenis, id_referensi, status, alasan, waktu, untuk, status_dibaca) 
        VALUES 
        ('laporan', '$id', '$status', '$catatan', NOW(), 'bendahara', 'belum')");

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

function getAnggaranByTahap($conn, $tahun) {
    return mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
}

function getTotalPengeluaran($conn, $id_anggaran) {
    $sql = "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

$tahun = date('Y');
$tahapan = [
    'tahap_i' => ['Tahap I (Jan - Mar)'],
    'tahap_ii' => ['Tahap II (Apr - Aug)'],
    'tahap_iii' => ['Tahap III (Sep - Dec)'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Verifikasi Laporan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
<!-- Header -->
<?php include '../includes/header_kades.php'; ?>

<!-- Layout -->
<div class="flex flex-1">
   <!-- Sidebar -->
  <aside class="w-72 bg-teal-700 text-white px-6 py-8">
    <h2 class="text-xl font-bold mb-6 text-center">Menu</h2>
    <nav class="space-y-3">
      <a href="dashboard_kades.php" class="block hover:bg-teal-900 px-4 py-2 rounded">📊 Dashboard</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 overflow-auto">
    <h2 class="text-2xl font-bold text-green-700 mb-6">📋 Daftar Laporan yang Menunggu Verifikasi</h2>

    <!-- Pertahap -->
    <?php foreach ($tahapan as $kode => [$label]): ?>
      <?php $laporan = getLaporanMenunggu($conn, 'pertahap', $kode, $tahun); ?>
      <?php if ($laporan): ?>
      <section class="bg-white rounded shadow p-4 mb-6">
        <h3 class="text-lg font-semibold text-green-600 mb-3"><?= $label ?> - Tahun <?= $tahun ?></h3>
        <table class="w-full text-sm border">
          <thead class="bg-green-100">
            <tr>
              <th class="border px-2 py-1">No</th>
              <th class="border px-2 py-1">Nama Kegiatan</th>
              <th class="border px-2 py-1">Alokasi Dana</th>
              <th class="border px-2 py-1">Realisasi</th>
              <th class="border px-2 py-1">Sisa Dana</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $anggaran = getAnggaranByTahap($conn, $tahun);
            $no = 1;
            while ($a = mysqli_fetch_assoc($anggaran)):
              $total = getTotalPengeluaran($conn, $a['id_anggaran']);
              $sisa = $a['alokasi_dana'] - $total;
              $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
            ?>
            <tr>
              <td class="border px-2 py-1 text-center"><?= $no++ ?></td>
              <td class="border px-2 py-1"><?= $a['nama_kegiatan'] ?></td>
              <td class="border px-2 py-1">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
              <td class="border px-2 py-1 text-center"><?= $persen ?>% (Rp <?= number_format($total, 0, ',', '.') ?>)</td>
              <td class="border px-2 py-1">Rp <?= number_format($sisa, 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <div class="mt-4 flex gap-2">
          <button onclick="openModal('<?= $laporan['id_ajuan'] ?>', 'disetujui')" class="bg-green-600 text-white px-4 py-2 rounded">Setujui</button>
          <button onclick="openModal('<?= $laporan['id_ajuan'] ?>', 'ditolak')" class="bg-red-600 text-white px-4 py-2 rounded">Tolak</button>
        </div>
      </section>
      <?php endif; ?>
    <?php endforeach; ?>

    <!-- LPJ Tahunan -->
    <?php $lpj = getLaporanMenunggu($conn, 'tahunan', null, $tahun); ?>
    <?php if ($lpj): ?>
      <section class="bg-white rounded shadow p-4 mb-6">
        <h3 class="text-lg font-semibold text-indigo-600 mb-3">LPJ Tahunan - Tahun <?= $tahun ?></h3>
        <table class="w-full text-sm border">
          <thead class="bg-indigo-100">
            <tr>
              <th class="border px-2 py-1">No</th>
              <th class="border px-2 py-1">Nama Kegiatan</th>
              <th class="border px-2 py-1">Alokasi Dana</th>
              <th class="border px-2 py-1">Realisasi</th>
              <th class="border px-2 py-1">Sisa Dana</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $data = getAnggaranByTahap($conn, $tahun);
            $no = 1;
            while ($a = mysqli_fetch_assoc($data)):
              $total = getTotalPengeluaran($conn, $a['id_anggaran']);
              $sisa = $a['alokasi_dana'] - $total;
              $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
            ?>
            <tr>
              <td class="border px-2 py-1 text-center"><?= $no++ ?></td>
              <td class="border px-2 py-1"><?= $a['nama_kegiatan'] ?></td>
              <td class="border px-2 py-1">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
              <td class="border px-2 py-1 text-center"><?= $persen ?>% (Rp <?= number_format($total, 0, ',', '.') ?>)</td>
              <td class="border px-2 py-1">Rp <?= number_format($sisa, 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <div class="mt-4 flex gap-2">
          <button onclick="openModal('<?= $lpj['id_ajuan'] ?>', 'disetujui')" class="bg-green-600 text-white px-4 py-2 rounded">Setujui</button>
          <button onclick="openModal('<?= $lpj['id_ajuan'] ?>', 'ditolak')" class="bg-red-600 text-white px-4 py-2 rounded">Tolak</button>
        </div>
      </section>
    <?php endif; ?>
  </main>
</div>

<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white w-full max-w-md mx-4 md:mx-0 p-6 rounded-xl shadow-xl animate-fade-in">

    <h2 id="modal-title" class="text-lg font-bold mb-2 text-green-700">Verifikasi Laporan</h2>
    <form method="post">
      <input type="hidden" name="id_ajuan" id="modal-id">
      <input type="hidden" name="action" id="modal-action">
      <label class="block mb-1 font-medium">Catatan / Alasan:</label>
      <textarea name="catatan" class="w-full border p-2 rounded mb-4" placeholder="Tulis catatan..."></textarea>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeModal()" class="bg-gray-400 px-4 py-2 rounded text-white">Batal</button>
        <button type="submit" class="bg-green-600 px-4 py-2 rounded text-white">Kirim</button>
      </div>
    </form>
  </div>
</div>

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

</body>
</html>
