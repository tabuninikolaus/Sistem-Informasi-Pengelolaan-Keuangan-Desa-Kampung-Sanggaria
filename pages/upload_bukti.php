<?php
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_pengeluaran = $_POST['id_pengeluaran'];
  $upload_dir = '../uploads/';
  $uploaded_files = [];

  // Pastikan ada file yang diunggah
  if (isset($_FILES['dokumen'])) {
    $file_count = count($_FILES['dokumen']['name']);

    for ($i = 0; $i < $file_count; $i++) {
      $tmp_name = $_FILES['dokumen']['tmp_name'][$i];
      $original_name = basename($_FILES['dokumen']['name'][$i]);

      if ($tmp_name && is_uploaded_file($tmp_name)) {
        $unique_name = time() . '_' . $i . '_' . $original_name;
        $target_path = $upload_dir . $unique_name;

        if (move_uploaded_file($tmp_name, $target_path)) {
          $uploaded_files[] = $unique_name;
        }
      }
    }

    // Jika ada file berhasil diupload
    if (count($uploaded_files) > 0) {
      // Gabungkan nama file jadi satu string
      $file_string = implode(',', $uploaded_files);

      // Simpan ke database
      $query = "UPDATE pengeluaran 
                SET dokumen_detail_pengeluaran = '$file_string', 
                    status_detail_pengeluaran = 'Valid' 
                WHERE id_pengeluaran = '$id_pengeluaran'";
      mysqli_query($conn, $query);

      header("Location: input_detail_pengeluaran.php?success=1");
      exit;
    } else {
      echo "Gagal mengunggah file.";
    }
  }
}
?>
