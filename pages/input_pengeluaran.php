<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil daftar kegiatan dari tabel anggaran
$kegiatanResult = mysqli_query($conn, "SELECT id_anggaran, nama_kegiatan, alokasi_dana FROM anggaran");

$warning = '';

if (isset($_POST['simpan'])) {
    $tanggal     = $_POST['tanggal'];
    $id_anggaran = $_POST['id_anggaran'];
    $jumlah      = str_replace('.', '', $_POST['jumlah']);
    $keterangan  = $_POST['keterangan'];

    $cekPengeluaran = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran WHERE id_anggaran='$id_anggaran'");
    $hasil = mysqli_fetch_assoc($cekPengeluaran);
    $totalSekarang = $hasil['total'] ?? 0;

    $cekAnggaran = mysqli_query($conn, "SELECT alokasi_dana, nama_kegiatan FROM anggaran WHERE id_anggaran='$id_anggaran'");
    $anggaran = mysqli_fetch_assoc($cekAnggaran);
    $alokasi = $anggaran['alokasi_dana'];
    $nama_kegiatan = $anggaran['nama_kegiatan'];

    $totalSetelah = $totalSekarang + $jumlah;

    $folder = "../uploads/";
    $buktiFiles = [];

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    foreach ($_FILES['bukti']['name'] as $key => $name) {
        $tmp = $_FILES['bukti']['tmp_name'][$key];
        $newName = time() . '_' . preg_replace('/\s+/', '_', basename($name));
        if (move_uploaded_file($tmp, $folder . $newName)) {
            $buktiFiles[] = $newName;
        }
    }

    $buktiJson = json_encode($buktiFiles);

    if ($totalSetelah > $alokasi) {
        $warning = "Maaf, dana yang Anda masukkan melebihi alokasi kegiatan '$nama_kegiatan'.";
    } else {
        $query = "INSERT INTO pengeluaran (tanggal, id_anggaran, jumlah, keterangan, bukti_pengeluaran)
                  VALUES ('$tanggal', '$id_anggaran', '$jumlah', '$keterangan', '$buktiJson')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data pengeluaran berhasil disimpan'); window.location.href='input_pengeluaran.php';</script>";
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Input Pengeluaran Dana</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

<header class="bg-purple-700 text-white px-6 py-6 shadow-lg flex justify-between items-center h-28">
  <div>
    <h1 class="text-2xl font-bold mb-1">💰 Sistem Informasi Keuangan Desa</h1>
    <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
  </div>
  <div class="text-right">
    <p class="text-sm">👋 Selamat datang, <strong>Bendahara</strong></p>
    <a href="../logout.php" class="text-red-300 hover:text-white text-xs underline">Keluar</a>
  </div>
</header>

<div class="flex">
  <!-- SIDEBAR -->
  <aside class="w-64 bg-purple-800 text-white min-h-screen px-6 py-8">
    <h2 class="text-xl font-bold mb-8 text-center">💰 Menu</h2>
    <nav class="space-y-3">
      <a href="dashboard_bendahara.php" class="block bg-purple-700 px-4 py-2 rounded-md">⬅️ Kembali ke Dashboard</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="flex-1 p-10">
    <h2 class="text-2xl font-bold mb-6 text-purple-700">Form Input Pengeluaran Dana</h2>

    <?php if ($warning): ?>
      <div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg border border-red-300">
        <?= $warning; ?>
      </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-xl shadow-md max-w-2xl">
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
          <label class="block text-gray-700">Tanggal</label>
          <input type="date" name="tanggal" required
                 class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700">Pilih Kegiatan</label>
          <select name="id_anggaran" required
                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            <option disabled selected>-- Pilih Kegiatan --</option>
            <?php while ($row = mysqli_fetch_assoc($kegiatanResult)): ?>
              <option value="<?= $row['id_anggaran']; ?>">
                <?= $row['nama_kegiatan']; ?> (Alokasi: Rp <?= number_format($row['alokasi_dana'], 0, ',', '.'); ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div>
          <label class="block text-gray-700">Jumlah Pengeluaran (Rp)</label>
          <input type="text" name="jumlah" id="jumlah" required
                 oninput="formatRupiah(this)"
                 placeholder="Contoh: 100.000.000"
                 class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700">Keterangan</label>
          <textarea name="keterangan" rows="3" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"
                    placeholder="Misal: Pembayaran bahan bangunan"></textarea>
        </div>

        <div>
          <label class="block text-gray-700">Upload Bukti Pengeluaran</label>
          <input type="file" name="bukti[]" multiple accept=".jpg,.jpeg,.png,.pdf" required
                 class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <button type="submit" name="simpan"
                class="w-full bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-semibold transition">
          Simpan Pengeluaran
        </button>
      </form>
    </div>
  </main>
</div>

<!-- FOOTER -->
<footer class="text-center text-sm text-gray-500 py-4">
  &copy; <?= date('Y') ?> Sistem Keuangan Desa. All rights reserved.
</footer>

<script>
function formatRupiah(el) {
  let number_string = el.value.replace(/[^,\d]/g, '').toString(),
      split = number_string.split(','),
      sisa  = split[0].length % 3,
      rupiah  = split[0].substr(0, sisa),
      ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

  if (ribuan) {
    let separator = sisa ? '.' : '';
    rupiah += separator + ribuan.join('.');
  }

  el.value = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
}
</script>

</body>
</html>
