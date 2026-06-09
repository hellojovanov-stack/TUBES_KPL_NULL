<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$apiBaseUrl = "http://localhost/TUBES_KPL_NULL/backend/api";

// Handle Tambah
if (isset($_POST['tambah'])) {
    $nama = htmlspecialchars($_POST['nama_supplier']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $telepon = htmlspecialchars($_POST['telepon']);

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode(['nama_supplier' => $nama, 'alamat' => $alamat, 'telepon' => $telepon])
        ]
    ];
    $context  = stream_context_create($options);
    file_get_contents("$apiBaseUrl/supplier.php", false, $context);

    header("Location: supplier.php");
    exit;
}

// Handle Update
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $nama = htmlspecialchars($_POST['nama_supplier']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $telepon = htmlspecialchars($_POST['telepon']);

    $options = [
        'http' => [
            'method'  => 'PUT',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode(['id' => $id, 'nama_supplier' => $nama, 'alamat' => $alamat, 'telepon' => $telepon])
        ]
    ];
    $context  = stream_context_create($options);
    file_get_contents("$apiBaseUrl/supplier.php", false, $context);

    header("Location: supplier.php");
    exit;
}

// Handle Delete
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $options = [
        'http' => [
            'method'  => 'DELETE',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode(['id' => $id])
        ]
    ];
    $context  = stream_context_create($options);
    file_get_contents("$apiBaseUrl/supplier.php", false, $context);

    header("Location: supplier.php");
    exit;
}

// Fetch Supplier Data
$data = [];
$result = file_get_contents("$apiBaseUrl/supplier.php");
if ($result !== false) {
    $response = json_decode($result, true);
    if (isset($response['status']) && $response['status'] === true) {
        $data = $response['data'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Supplier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800">

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
            <a href="dashboard.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Dashboard</a>
            <a href="kategori.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Kategori</a>
            <a href="supplier.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">Supplier</a>
            <a href="transaksi.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Transaksi</a>
            <a href="riwayat.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Riwayat</a>
            <a href="logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">Logout</a>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-6 mt-10">
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 mb-10 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Manajemen Supplier</h2>
            <p class="text-slate-500">Kelola daftar supplier obat.</p>
        </div>
        <button onclick="document.getElementById('modal').classList.remove('hidden')" class="bg-indigo-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all">
            + Tambah
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($data as $supplier): ?>
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2"><?= $supplier['nama_supplier']; ?></h3>
                    <p class="text-slate-500 text-sm mb-1">📞 <?= htmlspecialchars($supplier['telepon'] ?? '-'); ?></p>
                    <p class="text-slate-500 text-sm mb-4">📍 <?= htmlspecialchars($supplier['alamat'] ?? '-'); ?></p>
                </div>
                <div class="flex gap-3">
                    <button onclick="openEditModal('<?= $supplier['id']; ?>', '<?= addslashes($supplier['nama_supplier']); ?>', '<?= addslashes($supplier['alamat'] ?? ''); ?>', '<?= addslashes($supplier['telepon'] ?? ''); ?>')" class="flex-1 text-center bg-indigo-50 text-indigo-600 py-2 rounded-xl font-bold hover:bg-indigo-100 transition-all">
                        Edit
                    </button>
                    <a href="supplier.php?hapus=<?= $supplier['id']; ?>" onclick="return confirm('Yakin hapus supplier?')" class="flex-1 text-center bg-red-50 text-red-600 py-2 rounded-xl font-bold hover:bg-red-100 transition-all">
                        Hapus
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- MODAL TAMBAH -->
<div id="modal" class="fixed inset-0 hidden z-50 flex items-center justify-center min-h-screen px-4 bg-black/40 backdrop-blur-sm animate-fade">
    <div class="bg-white w-full max-w-lg m-auto rounded-3xl shadow-2xl overflow-hidden animate-modal">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">
            <h2 class="text-2xl font-black">Tambah Supplier</h2>
            <button onclick="document.getElementById('modal').classList.add('hidden')" class="text-white text-3xl leading-none hover:scale-110 transition-all">×</button>
        </div>
        <form method="POST" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Supplier</label>
                <input type="text" name="nama_supplier" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Telepon</label>
                <input type="text" name="telepon" class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <button type="submit" name="tambah" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold transition-all shadow-lg shadow-emerald-100">Simpan</button>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="editModal" class="fixed inset-0 hidden z-50 flex items-center justify-center min-h-screen px-4 bg-black/40 backdrop-blur-sm animate-fade">
    <div class="bg-white w-full max-w-lg m-auto rounded-3xl shadow-2xl overflow-hidden animate-modal">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white flex justify-between items-center">
            <h2 class="text-2xl font-black">Edit Supplier</h2>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-white text-3xl leading-none hover:scale-110 transition-all">×</button>
        </div>
        <form method="POST" class="p-6 space-y-5">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Supplier</label>
                <input type="text" name="nama_supplier" id="edit_nama" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Telepon</label>
                <input type="text" name="telepon" id="edit_telepon" class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Alamat</label>
                <textarea name="alamat" id="edit_alamat" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <button type="submit" name="update" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold transition-all shadow-lg shadow-emerald-100">Update</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nama, alamat, telepon) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_alamat').value = alamat;
    document.getElementById('edit_telepon').value = telepon;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
</body>
</html>
