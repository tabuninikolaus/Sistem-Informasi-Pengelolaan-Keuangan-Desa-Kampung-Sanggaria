<?php
include '../config/koneksi.php';
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Input Detail Pengeluaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

<?php include '../includes/header.php'; ?>

<div class="flex">
  <?php include '../includes/sidebar.php'; ?>

  <main class="ml-64 p-6 w-full">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
      <h1 class="text-2xl font-bold text-green-700 mb-6">📥 Input Dokumen Pertanggungjawaban</h1>

      <?php
      $kegiatan = mysqli_query($conn, "SELECT * FROM anggaran ORDER BY tahun DESC, nama_kegiatan ASC");
      while ($row = mysqli_fetch_assoc($kegiatan)):
        $id_anggaran = $row['id_anggaran'];
        $pengeluaran = mysqli_query($conn, "SELECT * FROM pengeluaran WHERE id_anggaran = '$id_anggaran'");
      ?>
      <div class="mb-8">
        <h2 class="font-semibold text-lg text-blue-600 mb-2">
          <?= $row['nama_kegiatan'] ?> (Rp <?= number_format($row['alokasi_dana'], 0, ',', '.') ?>)
        </h2>

        <div class="overflow-x-auto rounded border border-gray-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-green-100">
              <tr>
                <th class="border px-3 py-2">Tanggal</th>
                <th class="border px-3 py-2">Jumlah</th>
                <th class="border px-3 py-2">Keterangan</th>
                <th class="border px-3 py-2">Status</th>
                <th class="border px-3 py-2">Upload Dokumen</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($p = mysqli_fetch_assoc($pengeluaran)): ?>
              <tr class="bg-white hover:bg-gray-50">
                <td class="border px-3 py-2"><?= $p['tanggal'] ?></td>
                <td class="border px-3 py-2">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                <td class="border px-3 py-2"><?= $p['keterangan'] ?></td>
                <td class="border px-3 py-2">
                  <?= $p['status_detail_pengeluaran'] ? $p['status_detail_pengeluaran'] : 'Belum Valid' ?>
                </td>
                <td class="border px-3 py-2">
                  <form action="upload_bukti.php" method="post" enctype="multipart/form-data" onsubmit="return confirmUpload()">
                    <input type="hidden" name="id_pengeluaran" value="<?= $p['id_pengeluaran'] ?>">
                    <div class="flex items-center gap-2">
                      <input type="file" name="dokumen[]" multiple required class="text-sm">
                      <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                        Upload
                      </button>
                    </div>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
  function confirmUpload() {
    return confirm("Anda yakin ingin mengupload dokumen ini?");
  }
</script>

</body>
</html>
