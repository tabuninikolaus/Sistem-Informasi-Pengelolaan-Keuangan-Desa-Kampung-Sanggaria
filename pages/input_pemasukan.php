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
    <main class="flex-1 p-10">
      <h2 class="text-2xl font-bold mb-6 text-purple-700">Form Input Pemasukan Dana</h2>

      <?php if ($jumlahTerbilang): ?>
        <div class="bg-green-100 text-green-800 p-4 rounded mb-6">
          <strong>Jumlah dalam huruf:</strong><br>
          <em><?= $jumlahTerbilang; ?></em>
        </div>
      <?php endif; ?>

      <div class="bg-white p-8 rounded-xl shadow-md max-w-2xl">
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
