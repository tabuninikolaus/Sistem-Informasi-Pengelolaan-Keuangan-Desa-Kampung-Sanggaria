<?php
  // Pastikan variabel $currentPage diset di setiap halaman yang pakai sidebar
  if (!isset($currentPage)) $currentPage = '';
?>

    <!-- Sidebar -->
    <aside class="tosca bg-teal-700 text-white px-6 py-8 shadow-md overflow-hidden">
      <h2 class="text-xl font-bold mb-6 text-center">Menu</h2>
      <nav class="space-y-3">
        <a href="dashboard_bendahara.php" class="block bg-slate-700 px-4 py-2 rounded">📊 Dashboard</a>
      </nav>
    </aside>
