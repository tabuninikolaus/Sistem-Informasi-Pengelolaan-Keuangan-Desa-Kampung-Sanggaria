<?php 
include '../config/koneksi.php';
session_start();

// Cek hak akses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fungsi terbilang
function terbilang($angka) {
    $angka = (float)$angka;
    $bilangan = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
    if ($angka < 12) return $bilangan[$angka];
    elseif ($angka < 20) return terbilang($angka - 10) . " belas";
    elseif ($angka < 100) return terbilang(floor($angka / 10)) . " puluh " . terbilang($angka % 10);
    elseif ($angka < 200) return "seratus " . terbilang($angka - 100);
    elseif ($angka < 1000) return terbilang(floor($angka / 100)) . " ratus " . terbilang($angka % 100);
    elseif ($angka < 2000) return "seribu " . terbilang($angka - 1000);
    elseif ($angka < 1000000) return terbilang(floor($angka / 1000)) . " ribu " . terbilang($angka % 1000);
    elseif ($angka < 1000000000) return terbilang(floor($angka / 1000000)) . " juta " . terbilang($angka % 1000000);
    elseif ($angka < 1000000000000) return terbilang(floor($angka / 1000000000)) . " milyar " . terbilang($angka % 1000000000);
    else return "angka terlalu besar";
}

$jumlahTerbilang = null;

if (isset($_POST['simpan'])) {
    $tanggal     = $_POST['tanggal'];
    $sumberInput = $_POST['sumber'];
    $sumberLain  = $_POST['sumber_lain'];
    $jumlahInput = $_POST['jumlah'];
    $jumlah      = str_replace('.', '', $jumlahInput);
    $keterangan  = $_POST['keterangan'];

    $sumber = ($sumberInput == "Lain-lain") ? $sumberLain : $sumberInput;

    $query = "INSERT INTO pemasukan (tanggal, sumber, jumlah, keterangan)
              VALUES ('$tanggal', '$sumber', '$jumlah', '$keterangan')";

    if (mysqli_query($conn, $query)) {
        $jumlahTerbilang = ucfirst(terbilang($jumlah)) . " rupiah";
        echo "<script>alert('Data pemasukan berhasil disimpan');</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
// Ambil tahun sekarang
$tahun_ini = date('Y');

// Ambil data pemasukan berdasarkan tahun
$data_rekap = mysqli_query($conn, "
  SELECT * FROM pemasukan 
  WHERE YEAR(tanggal) = '$tahun_ini'
  ORDER BY id_pemasukan ASC
");

// Hitung total dana masuk
$total_rekap = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT SUM(jumlah) AS total FROM pemasukan 
  WHERE YEAR(tanggal) = '$tahun_ini'
"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Input Pemasukan Dana</title>
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
    <main class="flex-1 p-10 flex flex-col items-center">
      <h2 class="text-2xl font-bold mb-6 text-purple-700">Form Input Pemasukan Dana</h2>

     

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-4xl">
        <form method="POST" class="space-y-5">
          <div>
            <label class="block text-gray-700">Tanggal</label>
            <input type="date" name="tanggal" required
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
          </div>

          <div>
            <label class="block text-gray-700">Sumber Dana</label>
            <select name="sumber" id="sumber" onchange="toggleSumberLain()" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
              <option disabled selected>-- Pilih Sumber Dana --</option>
              <option value="DD">DD</option>
              <option value="ADD">ADD</option>
              <option value="Lain-lain">Lain-lain</option>
            </select>
          </div>

          <div id="inputSumberLain" style="display: none;">
            <label class="block text-gray-700">Tulis Sumber Dana</label>
            <input type="text" name="sumber_lain"
                   placeholder="Contoh: Bantuan Provinsi, Hibah, dsb"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
          </div>

          <div>
            <label class="block text-gray-700">Jumlah (Rp)</label>
            <input type="text" id="jumlah" name="jumlah" required
                   oninput="formatRupiah(this)"
                   placeholder="Contoh: 500.000.000"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
          </div>

          <div>
            <label class="block text-gray-700">Keterangan</label>
            <textarea name="keterangan" rows="3" required
                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"
                      placeholder="Misal: Dana Desa Tahap I 2025"></textarea>
          </div>

          <button type="submit" name="simpan"
                  class="w-full bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-semibold transition">
            Simpan
          </button>
        </form>
      </div>
      <!-- TABEL REKAP PEMASUKAN -->
<div class="bg-white mt-12 p-8 rounded-xl shadow-md w-full max-w-4xl mx-auto">
  <h3 class="text-xl font-bold text-purple-700 mb-4">📊 Rekap Pemasukan Tahun <?= $tahun_ini ?></h3>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left border">
      <thead class="bg-purple-100">
        <tr>
          <th class="px-4 py-2 border">No</th>
          <th class="px-4 py-2 border">Tanggal</th>
          <th class="px-4 py-2 border">Sumber Pemasukan</th>
          <th class="px-4 py-2 border">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = 1; 
        while ($row = mysqli_fetch_assoc($data_rekap)): ?>
        <tr class="border-b">
          <td class="px-4 py-2 border"><?= $no++ ?></td>
          <td class="px-4 py-2 border"><?= $row['tanggal'] ?></td>
          <td class="px-4 py-2 border"><?= $row['sumber'] ?></td>
          <td class="px-4 py-2 border text-green-700 font-semibold">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
        </tr>
        <?php endwhile; ?>
               <tr class="bg-purple-50 font-semibold">
  <td colspan="4" class="px-4 py-3 text-center border-t border-b">
    <div class="text-lg text-green-700">
      Total Dana Masuk: <strong>Rp <?= number_format($total_rekap['total'] ?? 0, 0, ',', '.') ?></strong>
    </div>
    <div class="text-sm text-gray-600 italic mt-1">
      (<?= ucfirst(terbilang($total_rekap['total'] ?? 0)) ?> rupiah)
    </div>
  </td>
</tr>

  </tbody>
    </table>
  </div>
</div>
    </main>
  </div>  
  

  <!-- FOOTER -->
  <footer class="text-center text-sm text-gray-500 py-4">
    &copy; <?= date('Y') ?> Sistem Keuangan Desa. All rights reserved.
  </footer>

  <script>
    function toggleSumberLain() {
      var sumber = document.getElementById("sumber").value;
      var inputLain = document.getElementById("inputSumberLain");
      inputLain.style.display = (sumber === "Lain-lain") ? "block" : "none";
    }

    function formatRupiah(input) {
      let angka = input.value.replace(/[^0-9]/g, '');
      input.value = new Intl.NumberFormat('id-ID').format(angka);
    }
  </script>

</body>
</html>
