<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Koneksi database untuk ambil data obat
$conn = mysqli_connect("localhost", "root", "", "apotek_db");

// Proses TAMBAH ke keranjang
if (isset($_POST['tambah'])) {
    $id_obat = $_POST['id_obat'];
    $jumlah = (int)$_POST['jumlah'];
    
    // Ambil data obat
    $result = mysqli_query($conn, "SELECT * FROM obat WHERE id = $id_obat");
    $obat = mysqli_fetch_assoc($result);
    
    if ($obat && $jumlah <= $obat['stok']) {
        $item = [
            'id' => $obat['id'],
            'nama' => $obat['nama_obat'],
            'harga' => $obat['harga'],
            'jumlah' => $jumlah,
            'subtotal' => $obat['harga'] * $jumlah
        ];
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][] = $item;
    }
}

// Proses BAYAR
if (isset($_POST['bayar']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        // Kurangi stok
        mysqli_query($conn, "UPDATE obat SET stok = stok - {$item['jumlah']} WHERE id = {$item['id']}");
    }
    unset($_SESSION['cart']);
    echo "<script>alert('Pembayaran berhasil!'); window.location.href='transaksi.php';</script>";
    exit;
}

// Proses BATAL
if (isset($_GET['batal'])) {
    unset($_SESSION['cart']);
    echo "<script>alert('Transaksi dibatalkan'); window.location.href='transaksi.php';</script>";
    exit;
}

// Ambil data obat untuk dropdown
$query = mysqli_query($conn, "SELECT * FROM obat ORDER BY nama_obat ASC");

// Hitung total
$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['subtotal'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <a href="dashboard.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Dashboard
            </a>
            <a href="transaksi.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">
                Transaksi
            </a>
            <a href="../../backend/routes/logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">
                Logout
            </a>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="max-w-5xl mx-auto px-6 mt-10">
    <div class="bg-white p-10 rounded-3xl shadow-xl border border-slate-200">
        
        <!-- HEADER -->
        <div class="flex justify-between items-center mb-10 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-3xl font-black text-slate-800">E-Kasir</h1>
                <p class="text-slate-400 mt-1">Kelola transaksi pelanggan dengan efisien.</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">
                    Status Transaksi
                </span>
                <div class="px-6 py-2 rounded-2xl text-sm font-black uppercase tracking-wider <?= empty($_SESSION['cart']) ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-700'; ?>">
                    <?= empty($_SESSION['cart']) ? 'DRAFT' : 'ACTIVE'; ?>
                </div>
            </div>
        </div>

        <!-- FORM TAMBAH -->
        <form method="POST" class="grid md:grid-cols-3 gap-4 mb-8">
            <select name="id_obat" required class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="">Pilih Obat</option>
                <?php while ($obat = mysqli_fetch_assoc($query)): ?>
                    <option value="<?= $obat['id']; ?>">
                        <?= $obat['nama_obat']; ?> - Stok <?= $obat['stok']; ?> - Rp <?= number_format($obat['harga']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="jumlah" min="1" placeholder="Jumlah" required class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">
            <button type="submit" name="tambah" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black transition-all">
                + Tambah
            </button>
        </form>

        <!-- LIST TRANSAKSI -->
        <div class="space-y-5 mb-10">
            <?php if (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                    <div class="flex justify-between items-center p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex gap-4 items-center">
                            <div class="bg-white p-3 rounded-xl shadow-sm">💊</div>
                            <div>
                                <h3 class="font-bold text-slate-800"><?= $item['nama']; ?></h3>
                                <p class="text-sm text-slate-500"><?= $item['jumlah']; ?> x Rp <?= number_format($item['harga']); ?></p>
                            </div>
                        </div>
                        <span class="font-bold text-slate-700">Rp <?= number_format($item['subtotal']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-slate-50 rounded-3xl">
                    <p class="text-slate-400 font-medium">Belum ada transaksi.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- TOTAL -->
        <div class="bg-slate-50 rounded-3xl p-6 mb-8 border border-slate-200">
            <div class="flex justify-between items-center">
                <span class="text-slate-500 font-medium">Total Pembayaran</span>
                <h2 class="text-4xl font-black text-emerald-600">Rp <?= number_format($total); ?></h2>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (empty($_SESSION['cart'])): ?>
                <button type="button" onclick="showWarning()" class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all bg-slate-200 text-slate-400 cursor-not-allowed">
                    Bayar Lunas
                </button>
            <?php else: ?>
                <form method="POST">
                    <button type="submit" name="bayar" class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-100">
                        Bayar Lunas
                    </button>
                </form>
            <?php endif; ?>
            
            <a href="<?= empty($_SESSION['cart']) ? '#' : 'transaksi.php?batal=1'; ?>" 
               <?= empty($_SESSION['cart']) ? 'onclick="showWarning()"' : ''; ?>
               class="w-full flex items-center justify-center py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all <?= empty($_SESSION['cart']) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-red-50 text-red-600 hover:bg-red-100'; ?>">
                Batalkan
            </a>
        </div>

        <!-- WARNING BOX -->
        <div id="warningBox" class="hidden mt-5 bg-amber-50 border border-amber-200 text-amber-700 px-5 py-4 rounded-2xl text-sm font-medium">
            ⚠️ Belum ada transaksi yang bisa diproses.
        </div>
    </div>
</main>

<script>
function showWarning() {
    const box = document.getElementById('warningBox');
    box.classList.remove('hidden');
    setTimeout(() => {
        box.classList.add('hidden');
    }, 3500);
}
</script>
</body>
</html>