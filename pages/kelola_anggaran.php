<?php 
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$tahun_ini = date('Y');

$getPemasukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan WHERE YEAR(tanggal) = '$tahun_ini'"));
$getPengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran WHERE YEAR(tanggal) = '$tahun_ini'"));

$total_pemasukan = $getPemasukan['total'] ?? 0;
$total_pengeluaran = $getPengeluaran['total'] ?? 0;
$sisa_dana = $total_pemasukan - $total_pengeluaran;

if (isset($_POST['simpan'])) {
    $tanggal_input = $_POST['tanggal_input'];
    $tahun = date('Y', strtotime($tanggal_input));
    $kegiatan = $_POST['kegiatan'];
    $alokasi = $_POST['alokasi'];
    $keterangan = $_POST['keterangan'];

    if ($alokasi > $sisa_dana) {
        echo "<script>alert('Alokasi dana melebihi sisa dana yang tersedia!');</script>";
    } else {
        $query = "INSERT INTO anggaran (tahun, nama_kegiatan, alokasi_dana, keterangan, tanggal_input_kegiatan)
                  VALUES ('$tahun', '$kegiatan', '$alokasi', '$keterangan', '$tanggal_input')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data anggaran berhasil disimpan'); window.location.href='kelola_anggaran.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

$data_anggaran = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun = '$tahun_ini'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Anggaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

<header class="bg-purple-700 text-white px-6 py-6 shadow-lg flex justify-between items-center h-28">
  <div>
    <h1 class="text-2xl font-bold mb-1">Sistem Informasi Keuangan Desa</h1>
    <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
  </div>
  <div class="text-right">
    <p class="text-sm">Selamat datang, <strong>Bendahara</strong></p>
    <a href="../logout.php" class="text-red-300 hover:text-white text-xs underline">Keluar</a>
  </div>
</header>

<div class="flex">
  <aside class="w-64 bg-purple-800 text-white min-h-screen px-6 py-8">
    <h2 class="text-xl font-bold mb-8 text-center">Menu</h2>
    <nav class="space-y-3">
       <a href="dashboard_bendahara.php" class="block bg-purple-700 px-4 py-2 rounded-md">⬅️ Kembali ke Dashboard</a>
    </nav>
  </aside>

  <main class="flex-1 p-10">
    <h2 class="text-2xl font-bold mb-6 text-purple-700">Form Kelola Anggaran (APBDes)</h2>
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-4xl mx-auto">
      <form method="POST" class="space-y-5">
        <div>
          <label class="block text-gray-700">Tanggal Input Kegiatan</label>
          <input type="date" name="tanggal_input" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>
        <div>
          <label class="block text-gray-700">Nama Kegiatan</label>
          <input type="text" name="kegiatan" placeholder="Contoh: Pembangunan Jalan" required class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div>
          <label class="block text-gray-700">Alokasi Dana (Rp)</label>
          <input type="text" id="alokasiFormatted" placeholder="Contoh: 500.000.000" class="w-full px-4 py-2 border rounded-lg" required>
          <input type="hidden" name="alokasi" id="alokasi">
        </div>
        <div>
          <label class="block text-gray-700">Keterangan</label>
          <textarea name="keterangan" rows="3" class="w-full px-4 py-2 border rounded-lg" required></textarea>
        </div>
        <button type="submit" name="simpan" class="w-full bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-semibold">
          Simpan Anggaran
        </button>
      </form>
    </div>

    <div class="bg-white mt-12 p-8 rounded-xl shadow-md w-full max-w-4xl mx-auto">
      <h3 class="text-xl font-bold text-purple-700 mb-4">Rekap Kegiatan & Alokasi Dana <?= $tahun_ini ?></h3>
      <div class="overflow-auto">
        <table class="min-w-full text-sm text-left">
          <thead class="bg-purple-100">
            <tr>
              <th class="px-4 py-2">No</th>
              <th class="px-4 py-2">Nama Kegiatan</th>
              <th class="px-4 py-2">Jumlah Alokasi</th>
              <th class="px-4 py-2">Total Pengeluaran</th>
              <th class="px-4 py-2">Realisasi (%)</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($data_anggaran)): 
              $id = $row['id_anggaran'];
              $alokasi = $row['alokasi_dana'];
              $pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran WHERE id_anggaran='$id'"));
              $total_pengeluaran_kegiatan = $pengeluaran['total'] ?? 0;
              $persen = ($alokasi > 0) ? round(($total_pengeluaran_kegiatan / $alokasi) * 100) : 0;
            ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= $no++ ?></td>
              <td class="px-4 py-2"><?= $row['nama_kegiatan'] ?></td>
              <td class="px-4 py-2">Rp <?= number_format($alokasi, 0, ',', '.') ?></td>
              <td class="px-4 py-2">Rp <?= number_format($total_pengeluaran_kegiatan, 0, ',', '.') ?></td>
              <td class="px-4 py-2"><?= $persen ?>%</td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <div class="bg-purple-100 px-6 py-3 rounded-b-xl max-w-4xl mx-auto mt-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-center">
          <div class="bg-white rounded-lg p-2 shadow border-t-4 border-green-500">
            <div class="text-sm text-gray-600 font-semibold">Total Dana Masuk</div>
            <div class="text-base font-bold text-green-700 mt-1">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
          </div>
          <div class="bg-white rounded-lg p-2 shadow border-t-4 border-red-500">
            <div class="text-sm text-gray-600 font-semibold">Total Pengeluaran</div>
            <div class="text-base font-bold text-red-600 mt-1">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></div>
          </div>
          <div class="bg-white rounded-lg p-2 shadow border-t-4 border-blue-500">
            <div class="text-sm text-gray-600 font-semibold">Saldo</div>
            <div class="text-base font-bold text-blue-700 mt-1">Rp <?= number_format($sisa_dana, 0, ',', '.') ?></div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<footer class="text-center text-sm text-gray-500 py-4">
  &copy; <?= date('Y') ?> Sistem Keuangan Desa. All rights reserved.
</footer>

<!-- Modal Saldo -->
<div id="modalSaldo" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 w-96 text-center">
    <h2 class="text-lg font-bold text-red-600 mb-4">Peringatan!</h2>
    <p class="text-gray-700 mb-4">Jumlah alokasi dana yang Anda inputkan melebihi saldo yang tersedia.</p>
    <button onclick="document.getElementById('modalSaldo').classList.add('hidden')" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded">Tutup</button>
  </div>
</div>

<script>
  const formatInput = document.getElementById('alokasiFormatted');
  const hiddenInput = document.getElementById('alokasi');
  const form = document.querySelector('form');

  formatInput.addEventListener('input', function(e) {
    let numericValue = e.target.value.replace(/\D/g, '');
    hiddenInput.value = numericValue;
    let formatted = new Intl.NumberFormat('id-ID').format(numericValue);
    formatInput.value = formatted;
  });

  form.addEventListener('submit', function(e) {
    const saldo = <?= $sisa_dana ?>;
    const nilaiInput = parseInt(hiddenInput.value);
    if (nilaiInput > saldo) {
      e.preventDefault();
      document.getElementById('modalSaldo').classList.remove('hidden');
    }
  });
</script>

</body>
</html>
