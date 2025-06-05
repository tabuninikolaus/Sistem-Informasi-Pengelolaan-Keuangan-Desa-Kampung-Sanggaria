<?php
include '../config/koneksi.php';
$feedbacks = mysqli_query($conn, "SELECT * FROM feedback ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Feedback</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
  <div class="max-w-5xl mx-auto bg-white shadow p-6 rounded-xl">
    <h1 class="text-2xl font-bold text-indigo-700 mb-6">💬 Data Masukan Masyarakat</h1>
    <div class="overflow-x-auto">
      <table class="w-full border text-sm">
        <thead class="bg-indigo-100">
          <tr>
            <th class="border px-3 py-2">No</th>
            <th class="border px-3 py-2">Nama</th>
            <th class="border px-3 py-2">Email</th>
            <th class="border px-3 py-2">JK</th>
            <th class="border px-3 py-2">Usia</th>
            <th class="border px-3 py-2">Pesan</th>
            <th class="border px-3 py-2">Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; while($f = mysqli_fetch_assoc($feedbacks)): ?>
          <tr class="hover:bg-gray-50">
            <td class="border px-3 py-2"><?= $no++ ?></td>
            <td class="border px-3 py-2"><?= htmlspecialchars($f['nama']) ?></td>
            <td class="border px-3 py-2"><?= htmlspecialchars($f['email']) ?></td>
            <td class="border px-3 py-2"><?= $f['jenis_kelamin'] ?></td>
            <td class="border px-3 py-2"><?= $f['usia'] ?></td>
            <td class="border px-3 py-2"><?= nl2br(htmlspecialchars($f['pesan'])) ?></td>
            <td class="border px-3 py-2"><?= $f['tanggal'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
