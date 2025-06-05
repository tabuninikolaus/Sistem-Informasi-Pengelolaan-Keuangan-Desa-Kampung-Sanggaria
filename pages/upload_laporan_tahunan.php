<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['upload'])) {
    $nama_laporan   = $_POST['nama_laporan'];
    $tahun_anggaran = $_POST['tahun_anggaran'];
    $tanggal_upload = date('Y-m-d');
    $dibuat_oleh    = $_SESSION['id_user'];

    // Upload file
    $file = $_FILES['file_laporan']['name'];
    $tmp  = $_FILES['file_laporan']['tmp_name'];
    $folder = "../uploads/";

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $filePath = $folder . basename($file);

    if (move_uploaded_file($tmp, $filePath)) {
        $query = "INSERT INTO laporan_tahunan (nama_laporan, tahun_anggaran, tanggal_upload, file_laporan, status_verifikasi, dibuat_oleh)
                  VALUES ('$nama_laporan', '$tahun_anggaran', '$tanggal_upload', '$file', 'pending', '$dibuat_oleh')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Laporan berhasil diupload'); window.location.href='dashboard_bendahara.php';</script>";
        } else {
            echo "Gagal menyimpan data: " . mysqli_error($conn);
        }
    } else {
        echo "<script>alert('Upload file gagal');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Upload Laporan Tahunan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

  <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-xl">
    <h2 class="text-2xl font-semibold text-green-700 mb-6 text-center">Upload Laporan Tahunan</h2>

    <form method="POST" enctype="multipart/form-data" class="space-y-5">
      <div>
        <label class="block text-gray-700">Nama Laporan</label>
        <input type="text" name="nama_laporan" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>

      <div>
        <label class="block text-gray-700">Tahun Anggaran</label>
        <input type="number" name="tahun_anggaran" min="2020" max="2100" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>

      <div>
        <label class="block text-gray-700">Upload File (PDF)</label>
        <input type="file" name="file_laporan" accept=".pdf" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>

      <button type="submit" name="upload"
              class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold transition">
        Upload Laporan
      </button>
    </form>
  </div>

</body>
</html>
