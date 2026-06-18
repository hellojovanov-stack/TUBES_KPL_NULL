<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
// testing
require_once __DIR__ . '/../../backend/models/Obat.php';

$obatModel = new Obat();

// Handle search
$keyword = $_GET['keyword'] ?? '';
if (!empty($keyword)) {
    $data = $obatModel->search($keyword);
} else {
    $data = $obatModel->getAll();
}

// ⬇️ TAMBAHKAN INI UNTUK PASTIKAN $data SELALU ADA ⬇️
if (!isset($data) || $data === false) {
    $data = [];
}
// ⬆️ TAMBAHKAN INI ⬆️

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800">

<!-- NAVBAR -->
<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="bg-emerald-600 p-1.5 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />
                </svg>
            </div>
            <span class="text-emerald-600 font-black text-xl uppercase tracking-tighter">
                Apotek<span class="text-slate-800">Sehat</span>
            </span>
        </div>
        <div class="flex items-center gap-6">
            <a href="dashboard.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">
                Dashboard
            </a>
            <a href="transaksi.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Transaksi
            </a>
            <a href="../../backend/routes/logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">
                Logout
            </a>
        </div>
    </div>
</nav>

<!-- MAIN -->
<main class="max-w-6xl mx-auto px-6 mt-10">
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Inventaris Obat</h2>
                <p class="text-slate-500">Cari dan kelola ketersediaan stok obat.</p>
            </div>
            <div class="mt-4 md:mt-0 px-4 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-[0.2em]">
                <?= isset($data) ? count($data) : 0; ?> DATA
            </div>
        </div>

        <!-- SEARCH FORM -->
        <form method="GET" action="">
            <div class="flex flex-col md:flex-row gap-4">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword ?? '') ?>" 
                    class="flex-1 px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 outline-none"
                    placeholder="Masukkan nama obat...">
                <button type="submit" class="bg-emerald-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-emerald-700 transition-all">
                    Cari Obat
                </button>
                <button type="button" onclick="openModal()" class="bg-indigo-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all">
                    + Tambah
                </button>
            </div>
        </form>
    </div>

    <!-- CARD GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (isset($data) && count($data) > 0): ?>
            <?php foreach ($data as $obat): ?>
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all">
                    <?php if (!empty($obat['gambar'])): ?>
                        <img src="../uploads/<?= $obat['gambar']; ?>" class="w-full h-48 object-cover rounded-2xl mb-5">
                    <?php endif; ?>
                    <div class="flex justify-between items-start mb-5">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($obat['nama_obat']); ?></h3>
                            <p class="text-slate-400 text-sm mt-1"><?= htmlspecialchars($obat['kategori']); ?></p>
                        </div>
                        <div class="bg-emerald-100 text-emerald-700 px-4 py-1 rounded-full text-xs font-black uppercase">
                            Stok <?= $obat['stok']; ?>
                        </div>
                    </div>
                    <div class="mb-6">
                        <p class="text-slate-400 text-sm mb-1">Harga</p>
                        <h2 class="text-3xl font-black text-emerald-600">Rp <?= number_format($obat['harga']); ?></h2>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="openEditModal('<?= $obat['id']; ?>', '<?= addslashes($obat['nama_obat']); ?>', '<?= addslashes($obat['kategori']); ?>', '<?= $obat['stok']; ?>', '<?= $obat['harga']; ?>')"
                            class="flex-1 text-center bg-indigo-50 text-indigo-600 py-3 rounded-2xl font-bold hover:bg-indigo-100 transition-all">
                            Edit
                        </button>
                        <a href="../../backend/routes/obat.php?action=delete&id=<?= $obat['id']; ?>" onclick="return confirm('Yakin hapus obat?')"
                            class="flex-1 text-center bg-red-50 text-red-600 py-3 rounded-2xl font-bold hover:bg-red-100 transition-all">
                            Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-200">
                <p class="text-slate-400 text-lg">Tidak ada data obat</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- MODAL TAMBAH -->
<div id="modal" class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black">Tambah Obat</h2>
                <p class="text-emerald-100 text-sm">Tambahkan data obat baru</p>
            </div>
            <button onclick="closeModal()" class="text-3xl">×</button>
        </div>
        <form method="POST" action="../../backend/routes/obat.php?action=create" enctype="multipart/form-data" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-bold mb-2">Gambar Obat</label>
                <input type="file" name="gambar" accept="image/*" class="w-full">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Nama Obat</label>
                <input type="text" name="nama_obat" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Kategori</label>
                <input type="text" name="kategori" class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-2">Stok</label>
                    <input type="number" name="stok" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Harga</label>
                    <input type="number" name="harga" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold">Simpan Obat</button>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="editModal" class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black">Edit Obat</h2>
                <p class="text-emerald-100 text-sm">Ubah data obat</p>
            </div>
            <button onclick="closeEditModal()" class="text-3xl">×</button>
        </div>
        <form method="POST" action="../../backend/routes/obat.php?action=edit" enctype="multipart/form-data" class="p-6 space-y-5">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label class="block text-sm font-bold mb-2">Gambar Baru</label>
                <input type="file" name="gambar" accept="image/*" class="w-full">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Nama Obat</label>
                <input type="text" name="nama_obat" id="edit_nama" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Kategori</label>
                <input type="text" name="kategori" id="edit_kategori" class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-2">Stok</label>
                    <input type="number" name="stok" id="edit_stok" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Harga</label>
                    <input type="number" name="harga" id="edit_harga" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl">
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold">Update Obat</button>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}
function openEditModal(id, nama, kategori, stok, harga) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_kategori').value = kategori;
    document.getElementById('edit_stok').value = stok;
    document.getElementById('edit_harga').value = harga;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
</body>
</html>
