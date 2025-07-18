<?php
include '../config/koneksi.php';
$queryKegiatan = mysqli_query($conn, "
  SELECT 
    a.nama_kegiatan,
    a.alokasi_dana,
    ROUND(
      (IFNULL(SUM(p.jumlah), 0) / a.alokasi_dana) * 100,
      2
    ) AS realisasi_persen
  FROM anggaran a
  LEFT JOIN pengeluaran p ON p.id_anggaran = a.id_anggaran
  GROUP BY a.id_anggaran
  HAVING realisasi_persen < 100
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Transparansi Keuangan Desa Sanggaria</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
        },
      }
    }
  </script>
</head>
<body class="bg-gray-50 font-sans text-gray-800 scroll-smooth">

<!-- Navbar -->
<header class="bg-white shadow sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-indigo-700">💼 Keuangan Desa Sanggaria</h1>
    <nav class="space-x-4 text-sm font-medium">
      <a href="#" class="text-gray-700 hover:text-indigo-700">Beranda</a>
      <a href="#tentang" class="text-gray-700 hover:text-indigo-700">Tentang</a>
      <a href="#kegiatan" class="text-gray-700 hover:text-indigo-700">Kegiatan</a>
      <a href="#feedback" class="text-gray-700 hover:text-indigo-700">Feedback</a>
    </nav>
  </div>
</header>

<!-- Hero Section -->
<div class="relative h-[60vh] bg-cover bg-center" style="background-image: url('../assets/img/sanggaria.jpeg');">
  <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/30 flex items-center justify-center">
    <div class="text-center text-white px-4">
      <h1 class="text-4xl md:text-5xl font-extrabold mb-3 drop-shadow">Sistem Informasi Keuangan Desa</h1>
      <p class="text-lg max-w-2xl mx-auto drop-shadow">Transparansi dan Akuntabilitas Dana Desa Kampung Sanggaria.</p>
    </div>
  </div>
</div>

<!-- Tentang -->
<section id="tentang" class="py-16 px-6 md:px-16 bg-white max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-indigo-700 mb-6">Tentang Sistem</h2>
  <p class="text-center max-w-4xl mx-auto text-gray-600 text-lg leading-relaxed">
    Ini adalah platform resmi pengelolaan keuangan desa Kampung Sanggaria. Di sini masyarakat dapat memantau pemasukan, pengeluaran, serta kegiatan yang didanai oleh Dana Desa secara langsung dan transparan.
  </p>
</section>
<!-- UNDUH LAPORAN KEUANGAN -->
<?php
$folder = '../laporan_publik/';
$files = array_diff(scandir($folder), ['.', '..']);
?>

<section class="py-16 px-6 md:px-16 bg-gray-100 max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-indigo-700 mb-10">📥 Unduh Dokumen Laporan</h2>
  <div class="grid md:grid-cols-2 gap-6 text-center">
    <?php
    $pdfFound = false;
    foreach ($files as $file):
      if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf'):
        $pdfFound = true;
        $filepath = $folder . $file;
        $timestamp = filemtime($filepath); // Ambil waktu terakhir diubah
        $filenameDisplay = ucwords(str_replace(['_', '.pdf'], [' ', ''], pathinfo($file, PATHINFO_FILENAME)));
        $url = $filepath . '?v=' . $timestamp;
    ?>
      <a href="<?= $url ?>" target="_blank" class="bg-white p-6 shadow rounded-xl hover:shadow-md transition">
        <h3 class="text-lg font-semibold text-gray-700 mb-2"><?= $filenameDisplay ?></h3>
        <p class="text-sm text-gray-500">Klik untuk unduh</p>
      </a>
    <?php
      endif;
    endforeach;
    ?>

    <?php if (!$pdfFound): ?>
      <p class="col-span-2 text-center text-gray-500 italic">Belum ada laporan dipublikasikan.</p>
    <?php endif; ?>
  </div>
</section>


<!-- Tabel Kegiatan -->
<section id="kegiatan" class="py-16 px-6 md:px-16 bg-white max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-indigo-700 mb-10">
  📌 KEGIATAN YANG SEDANG DIDANAI TAHUN <?= date('Y') ?>
</h2>

  <div class="overflow-auto">
    <table class="w-full border text-sm bg-white shadow">
      <thead class="bg-indigo-100 text-gray-700">
        <tr>
          <th class="border px-4 py-2">No</th>
          <th class="border px-4 py-2">Nama Kegiatan</th>
          <th class="border px-4 py-2">Jumlah Alokasi</th>
          <th class="border px-4 py-2">Realisasi (%)</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = mysqli_fetch_assoc($queryKegiatan)): ?>
        <tr class="hover:bg-gray-50">
          <td class="border px-4 py-2 text-center"><?= $no++ ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
          <td class="border px-4 py-2">Rp <?= number_format($row['alokasi_dana'], 0, ',', '.') ?></td>
          <td class="border px-4 py-2 text-center font-semibold text-indigo-700"><?= $row['realisasi_persen'] ?>%</td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>


<!-- Form Feedback -->
<section id="feedback" class="py-16 px-6 md:px-16 bg-gray-100 max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-indigo-700 mb-8">💬 Kirim Masukan & Saran</h2>
  <form action="simpan_feedback.php" method="POST" class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow space-y-5">
    <input type="text" name="nama" required placeholder="Nama Lengkap" class="w-full px-4 py-2 border rounded-lg">
    <input type="email" name="email" required placeholder="Alamat Email" class="w-full px-4 py-2 border rounded-lg">
    <input type="number" name="usia" required placeholder="Usia" class="w-full px-4 py-2 border rounded-lg">
    <select name="jenis_kelamin" required class="w-full px-4 py-2 border rounded-lg">
      <option value="">-- Pilih Jenis Kelamin --</option>
      <option value="Laki-laki">Laki-laki</option>
      <option value="Perempuan">Perempuan</option>
    </select>
    <textarea name="pesan" rows="4" required placeholder="Pesan atau Saran..." class="w-full px-4 py-2 border rounded-lg"></textarea>
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold">
      Kirim Feedback
    </button>
  </form>
</section>
<!-- Footer -->
 <?php include '../includes/footer.php'; ?>
</body>
</html>
