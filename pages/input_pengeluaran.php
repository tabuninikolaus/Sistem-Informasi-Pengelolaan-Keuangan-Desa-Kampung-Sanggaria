<?php
include '../config/koneksi.php';
session_start();

$tahun_ini = date('Y');

// Ambil data anggaran untuk tahun ini
$data_anggaran = mysqli_query($conn, "
  SELECT * FROM anggaran 
  WHERE tahun = '$tahun_ini'
");

// Hitung total pemasukan dan total pengeluaran tahun ini
$total_pemasukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan WHERE YEAR(tanggal) = '$tahun_ini'"))['total'] ?? 0;
$total_pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran WHERE YEAR(tanggal) = '$tahun_ini'"))['total'] ?? 0;
$sisa_dana = $total_pemasukan - $total_pengeluaran;

$kegiatanResult = mysqli_query($conn, "SELECT id_anggaran, nama_kegiatan, alokasi_dana FROM anggaran");
$warning = '';
$modal_warning = false; // Untuk kontrol modal warning melebihi alokasi

// Handle penyimpanan setelah konfirmasi dari modal
if (isset($_POST['konfirmasi_pengajuan']) && isset($_SESSION['pengajuan_data'])) {
    $data = $_SESSION['pengajuan_data'];
    unset($_SESSION['pengajuan_data']);

    $tanggal = $data['tanggal'];
    $id_anggaran = $data['id_anggaran'];
    $jumlah = $data['jumlah'];
    $keterangan = $data['keterangan'];
    $buktiJson = $data['bukti'];

    $query = "INSERT INTO pengeluaran_ajuan (tanggal_pengajuan, id_anggaran, jumlah_ajuan, keterangan, dokumen_pengajuan, status_ajuan,catatan_admin)
              VALUES ('$tanggal', '$id_anggaran', '$jumlah', '$keterangan', '$buktiJson', 'menunggu', 'Pengeluaran di atas 50 juta. Menunggu verifikasi kades.')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Ajuan pengeluaran berhasil dikirim ke kades'); window.location.href='input_pengeluaran.php';</script>";
        exit;
    } else {
        $warning = "Gagal mengajukan pengeluaran besar: " . mysqli_error($conn);
    }
}

// Handle form simpan awal
if (isset($_POST['simpan'])) {
    $tanggal     = $_POST['tanggal'] ?? null;
    $id_anggaran = $_POST['id_anggaran'] ?? null;
    $jumlahInput = $_POST['jumlah'] ?? '0';
    $jumlah      = str_replace('.', '', $jumlahInput);
    $keterangan  = $_POST['keterangan'] ?? '';

    if (!$id_anggaran) {
        $warning = "Pilih kegiatan terlebih dahulu.";
    } else {
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

       
        }
        // LOGIKA KONDISI Lebih Alokasi
if ($totalSetelah > $alokasi) {
    // Modal peringatan melebihi alokasi muncul
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-alokasi').classList.remove('hidden');
        });
    </script>";
} elseif ($jumlah > 50000000) {
    // Modal konfirmasi pengeluaran besar
    $_SESSION['pengajuan_data'] = [
        'tanggal' => $tanggal,
        'id_anggaran' => $id_anggaran,
        'jumlah' => $jumlah,
        'keterangan' => $keterangan,
        'bukti' => $buktiJson
    ];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-konfirmasi').classList.remove('hidden');
            document.getElementById('form-pengeluaran').classList.add('hidden');
        });
    </script>";
} else {
    // Simpan langsung karena memenuhi syarat
    $query = "INSERT INTO pengeluaran (tanggal, id_anggaran, jumlah, keterangan, bukti_pengeluaran, status_verifikasi)
              VALUES ('$tanggal', '$id_anggaran', '$jumlah', '$keterangan', '$buktiJson', 'disetujui')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data pengeluaran berhasil disimpan'); window.location.href='input_pengeluaran.php';</script>";
        exit;
    } else {
        $warning = 'Terjadi kesalahan saat menyimpan pengeluaran: ' . mysqli_error($conn);
    }
}   
    }
    // Untuk validasi kegiatan selesai (realisasi 100%)
$realisasiKegiatan = [];
$allKegiatan = mysqli_query($conn, "SELECT id_anggaran, alokasi_dana FROM anggaran WHERE tahun = '$tahun_ini'");
while ($kg = mysqli_fetch_assoc($allKegiatan)) {
    $id = $kg['id_anggaran'];
    $alokasi = $kg['alokasi_dana'];
    $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran = '$id'"))['total'] ?? 0;
    $persen = $alokasi > 0 ? round(($total / $alokasi) * 100) : 0;
    $realisasiKegiatan[$id] = $persen;
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

<!-- HEADER -->
<?php include '../includes/header.php'; ?>

<div class="flex">
 <!-- SIDEBAR -->
<?php include '../includes/sidebar.php'; ?>


  <main class="flex-1 p-10">
    <h2 class="text-2xl font-bold mb-6 text-purple-700">Form Input Pengeluaran Dana</h2>

    <?php if ($warning): ?>
      <div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg border border-red-300">
        <?= $warning; ?>
      </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-xl shadow-md max-w-4xl mx-auto">
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
          <label class="block text-gray-700">Tanggal</label>
          <input type="date" name="tanggal" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700">Pilih Kegiatan</label>
          <select name="id_anggaran" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
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
          <input type="text" name="jumlah" id="jumlah" required oninput="formatRupiah(this)"
                 placeholder="Contoh: 100.000.000" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
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
    <!-- TABEL REKAP -->
<!-- TABEL REKAP -->
<div class="bg-white mt-12 p-8 rounded-xl shadow-md max-w-4xl mx-auto">
  <h3 class="text-xl font-bold text-purple-700 mb-4">📊 Rekapan Kegiatan, Jumlah Alokasi & Pengeluaran <?= $tahun_ini ?></h3>
  <div class="overflow-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-purple-100">
        <tr>
          <th class="px-4 py-2">No</th>
          <th class="px-4 py-2">Nama Kegiatan</th>
          <th class="px-4 py-2">Jumlah Alokasi</th>
          <th class="px-4 py-2">Total Pengeluaran</th>
          <th class="px-4 py-2">Sisa Dana</th>
          <th class="px-4 py-2">Realisasi (%)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = 1;
        while ($row = mysqli_fetch_assoc($data_anggaran)): 
          $id = $row['id_anggaran'];
          $alokasi = $row['alokasi_dana'];
          $pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran WHERE id_anggaran='$id'"))['total'] ?? 0;
          $sisa_kegiatan = $alokasi - $pengeluaran;
          $persen = ($alokasi > 0) ? round(($pengeluaran / $alokasi) * 100) : 0;
        ?>
        <tr class="border-b">
          <td class="px-4 py-2"><?= $no++ ?></td>
          <td class="px-4 py-2"><?= $row['nama_kegiatan'] ?></td>
          <td class="px-4 py-2">Rp <?= number_format($alokasi, 0, ',', '.') ?></td>
          <td class="px-4 py-2">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></td>
          <td class="px-4 py-2 text-blue-700">Rp <?= number_format($sisa_kegiatan, 0, ',', '.') ?></td>
          <td class="px-4 py-2 text-center"><?= $persen ?>%</td>
        </tr>
        <?php endwhile; ?>
  </div>
</div>

      </tbody>
    </table>
     <!-- CARD INFO RINGKASAN DANA MINI -->
<div class="bg-purple-100 px-6 py-3 rounded-b-xl max-w-4xl mx-auto mt-4">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-center">

    <!-- Total Masuk -->
    <div class="bg-white rounded-lg p-2 shadow border-t-4 border-green-500">
      <div class="text-sm text-gray-600 font-semibold">Total Dana Masuk</div>
      <div class="text-base font-bold text-green-700 mt-1">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></div>
    </div>

    <!-- Total Pengeluaran -->
    <div class="bg-white rounded-lg p-2 shadow border-t-4 border-red-500">
      <div class="text-sm text-gray-600 font-semibold">Total Pengeluaran</div>
      <div class="text-base font-bold text-red-600 mt-1">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></div>
    </div>

    <!-- Saldo -->
    <div class="bg-white rounded-lg p-2 shadow border-t-4 border-blue-500">
      <div class="text-sm text-gray-600 font-semibold">Saldo</div>
      <div class="text-base font-bold text-blue-700 mt-1">Rp <?= number_format($sisa_dana, 0, ',', '.') ?></div>
    </div>

  </div>
</div>

  </div>
</div>
  </main>
</div>
<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>


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
<!--  Modal untuk Melebihi Alokasi -->
<div id="modal-alokasi" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
  <div class="bg-white text-gray-800 rounded-lg shadow-lg p-6 w-96">
    <h2 class="text-xl font-bold mb-4 text-red-600">Peringatan!</h2>
    <p class="mb-4">Dana yang Anda inputkan melebihi alokasi dana yang tersedia untuk kegiatan ini.</p>
    <div class="flex justify-end">
      <button type="button" onclick="closeModal('modal-alokasi')" class="px-4 py-2 bg-purple-700 text-white rounded hover:bg-purple-800">Tutup</button>
    </div>
  </div>
</div>

<script>
function closeModal(id) {
  document.getElementById(id).classList.add('hidden');
}
</script>
<!-- Modal Konfirmasi Pengeluaran Besar -->
<div id="modal-konfirmasi" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
  <div class="bg-white text-gray-800 rounded-lg shadow-lg p-6 w-96">
    <h2 class="text-lg font-bold text-red-600 mb-2">Konfirmasi Pengeluaran Besar</h2>
    <p class="text-gray-700 mb-4">Anda akan mengajukan pengeluaran di atas 50 juta. Data akan dikirim ke Kepala Desa untuk diverifikasi terlebih dahulu.</p>
    <form method="POST">
      <input type="hidden" name="konfirmasi_pengajuan" value="1">
      <input type="hidden" name="data_pengajuan" id="data-pengajuan">
      <div class="flex justify-end space-x-3">
        <button type="button" onclick="closeModal('modal-konfirmasi')" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Batal</button>
        <button type="submit" class="bg-yellow-500 px-4 py-2 rounded hover:bg-yellow-600">Ajukan</button>
      </div>
    </form>
  </div>
</div>
<script>
function closeModal(id) {
  document.getElementById(id).classList.add('hidden');
}
</script>
<!-- Modal Kegiatan Selesai -->
<div id="modal-selesai" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
  <div class="bg-white rounded-lg shadow-lg p-6 w-96">
    <h2 class="text-lg font-bold text-red-600 mb-2">Kegiatan Selesai</h2>
    <p class="text-gray-700 mb-4">Kegiatan yang Anda pilih telah 100% realisasi. Pengeluaran tidak dapat ditambahkan lagi.</p>
    <div class="flex justify-end">
      <button onclick="closeModal('modal-selesai')" class="bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800">Tutup</button>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const kegiatanSelect = document.querySelector('select[name="id_anggaran"]');
  const jumlahInput = document.querySelector('input[name="jumlah"]');
  const realisasiData = <?= json_encode($realisasiKegiatan ?? []) ?>;

  kegiatanSelect.addEventListener('change', function () {
    const selected = this.value;
    if (realisasiData[selected] && realisasiData[selected] >= 100) {
      document.getElementById("modal-selesai").classList.remove("hidden");
      this.value = ''; // Reset pilihan
    }
  });
});
</script>

</body>
</html>
