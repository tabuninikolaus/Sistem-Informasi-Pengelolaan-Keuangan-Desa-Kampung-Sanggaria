<?php
include '../config/koneksi.php';
session_start();

// Dummy user (ganti sesuai sistem loginmu)
$user = ['nama_lengkap' => 'Kepala Desa', 'foto_profil' => 'assets/img/default-user.png'];

$query = "
    SELECT a.id_ajuan, a.tanggal_pengajuan, a.id_anggaran, ang.nama_kegiatan, ang.alokasi_dana,
           a.jumlah_ajuan, a.keterangan, a.dokumen_pengajuan
    FROM pengeluaran_ajuan a
    JOIN anggaran ang ON a.id_anggaran = ang.id_anggaran
    WHERE a.status_ajuan = 'menunggu'
    ORDER BY a.id_ajuan DESC
";

$result = mysqli_query($conn, $query);

// Proses tombol TERIMA
if (isset($_POST['terima'])) {
    $id_ajuan = $_POST['id_ajuan'];
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan_terima']);
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengeluaran_ajuan WHERE id_ajuan='$id_ajuan'"));
    $tanggal = $data['tanggal_pengajuan'];
    $id_anggaran = $data['id_anggaran'];
    $jumlah = $data['jumlah_ajuan'];
    $keterangan = $data['keterangan'];
    $bukti = $data['dokumen_pengajuan'];

    mysqli_query($conn, "UPDATE pengeluaran_ajuan 
        SET status_ajuan='disetujui', tanggal_verifikasi='$tanggal', alasan_penerimaan_pengeluaran='$alasan' 
        WHERE id_ajuan='$id_ajuan'");

    mysqli_query($conn, "INSERT INTO pengeluaran (tanggal, id_anggaran, jumlah, keterangan, bukti_pengeluaran, status_verifikasi)
        VALUES ('$tanggal', '$id_anggaran', '$jumlah', '$keterangan', '$bukti', 'disetujui')");

    mysqli_query($conn, "INSERT INTO log_verifikasi 
        (jenis, id_referensi, status, alasan, waktu, untuk, status_dibaca) 
        VALUES ('pengeluaran', '$id_ajuan', 'diterima', '$alasan', NOW(), 'bendahara', 'belum')");

    header("Location: verifikasi_pengeluaran.php");
    exit;
}

// Proses tombol TOLAK
if (isset($_POST['tolak'])) {
    $id_ajuan = $_POST['id_ajuan'];
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);
    $tanggal = date('Y-m-d');

    mysqli_query($conn, "UPDATE pengeluaran_ajuan 
        SET status_ajuan='ditolak', tanggal_verifikasi='$tanggal', alasan_penolakan_pengeluaran='$alasan' 
        WHERE id_ajuan='$id_ajuan'");

    mysqli_query($conn, "INSERT INTO log_verifikasi 
        (jenis, id_referensi, status, alasan, waktu, untuk, status_dibaca) 
        VALUES ('pengeluaran', '$id_ajuan', 'disetujui', '$alasan', NOW(), 'bendahara', 'belum')");

    header("Location: verifikasi_pengeluaran.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Verifikasi Pengeluaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
  <!-- HEADER  -->
<?php include '../includes/header_kades.php'; ?>

<!-- Main Layout -->
<div class="flex flex-1">
  <!-- Sidebar -->
  <aside class="w-72 bg-teal-700 text-white px-6 py-8">
    <h2 class="text-xl font-bold mb-6 text-center">Menu</h2>
    <nav class="space-y-3">
      <a href="dashboard_kades.php" class="block hover:bg-teal-900 px-4 py-2 rounded">📊 Dashboard</a>
    </nav>
  </aside>

  <!-- Konten Utama -->
  <main class="flex-1 p-6 overflow-auto">
    <h1 class="text-3xl font-bold text-purple-700 mb-6">📋 Verifikasi Pengeluaran Besar</h1>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full text-sm text-left border">
        <thead class="bg-purple-600 text-white">
          <tr>
            <th class="px-4 py-2">Tanggal Ajuan</th>
            <th class="px-4 py-2">Kegiatan</th>
            <th class="px-4 py-2">Alokasi</th>
            <th class="px-4 py-2">Jumlah Ajuan</th>
            <th class="px-4 py-2">Keterangan</th>
            <th class="px-4 py-2">Dokumen</th>
            <th class="px-4 py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2"><?= $row['tanggal_pengajuan']; ?></td>
            <td class="px-4 py-2"><?= $row['nama_kegiatan']; ?></td>
            <td class="px-4 py-2">Rp <?= number_format($row['alokasi_dana'], 0, ',', '.'); ?></td>
            <td class="px-4 py-2 font-bold">Rp <?= number_format($row['jumlah_ajuan'], 0, ',', '.'); ?></td>
            <td class="px-4 py-2"><?= $row['keterangan']; ?></td>
            <td class="px-4 py-2">
              <?php
              $files = json_decode($row['dokumen_pengajuan'], true);
              if (is_array($files)) {
                foreach ($files as $file) {
                  echo "<a href='../uploads/$file' target='_blank' class='text-blue-600 underline block'>Lihat</a>";
                }
              } else {
                echo "-";
              }
              ?>
            </td>
            <td class="px-4 py-2 space-y-1">
              <button onclick="showModalTerima('<?= $row['id_ajuan']; ?>')" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 w-full">Terima</button>
              <button onclick="showModalTolak('<?= $row['id_ajuan']; ?>')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 w-full">Tolak</button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Modal Tolak -->
<div id="modalTolak" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded-xl w-96 relative">
    <h2 class="text-xl font-semibold mb-4">Tolak Pengajuan</h2>
    <form method="POST">
      <input type="hidden" name="id_ajuan" id="modalAjuanIdTolak">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Alasan Penolakan</label>
        <textarea name="alasan" rows="3" required class="w-full px-3 py-2 border rounded-lg"></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalTolak')" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
        <button type="submit" name="tolak" class="px-4 py-2 bg-red-600 text-white rounded">Kirim</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Terima -->
<div id="modalTerima" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded-xl w-96 relative">
    <h2 class="text-xl font-semibold mb-4">Terima Pengajuan</h2>
    <form method="POST">
      <input type="hidden" name="id_ajuan" id="modalAjuanIdTerima">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Alasan Penerimaan</label>
        <textarea name="alasan_terima" rows="3" required class="w-full px-3 py-2 border rounded-lg"></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeModal('modalTerima')" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
        <button type="submit" name="terima" class="px-4 py-2 bg-green-600 text-white rounded">Kirim</button>
      </div>
    </form>
  </div>
</div>

<!-- Footer -->
<?php include '../includes/footer.php'; ?>

<!-- JS Modal -->
<script>
  function showModalTolak(id) {
    document.getElementById('modalAjuanIdTolak').value = id;
    document.getElementById('modalTolak').style.display = 'flex';
  }

  function showModalTerima(id) {
    document.getElementById('modalAjuanIdTerima').value = id;
    document.getElementById('modalTerima').style.display = 'flex';
  }

  function closeModal(id) {
    document.getElementById(id).style.display = 'none';
  }
</script>

</body>
</html>
