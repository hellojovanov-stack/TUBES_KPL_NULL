<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
$apiBaseUrl = "http://localhost/TUBES_KPL_NULL/backend/api";

if (isset($_POST['tambah'])) {
    $opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['nama_kategori' => htmlspecialchars($_POST['nama_kategori']), 'deskripsi' => htmlspecialchars($_POST['deskripsi'])])]];
    file_get_contents("$apiBaseUrl/kategori.php", false, stream_context_create($opts));
    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Kategori berhasil ditambahkan!'];
    header("Location: kategori.php"); exit;
}
if (isset($_POST['update'])) {
    $opts = ['http' => ['method' => 'PUT', 'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['id' => (int)$_POST['id'], 'nama_kategori' => htmlspecialchars($_POST['nama_kategori']), 'deskripsi' => htmlspecialchars($_POST['deskripsi'])])]];
    file_get_contents("$apiBaseUrl/kategori.php", false, stream_context_create($opts));
    $_SESSION['toast'] = ['type' => 'info', 'msg' => 'Kategori berhasil diperbarui!'];
    header("Location: kategori.php"); exit;
}
if (isset($_GET['hapus'])) {
    $opts = ['http' => ['method' => 'DELETE', 'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['id' => (int)$_GET['hapus']])]];
    file_get_contents("$apiBaseUrl/kategori.php", false, stream_context_create($opts));
    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'Kategori berhasil dihapus.'];
    header("Location: kategori.php"); exit;
}

$data = [];
$res = file_get_contents("$apiBaseUrl/kategori.php");
if ($res !== false) { $r = json_decode($res, true); if (!empty($r['status'])) $data = $r['data']; }

$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

$icons = ['<i class="fas fa-tag"></i>'];
$colors = [
    ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-800'],
    ['bg' => 'bg-indigo-50',  'border' => 'border-indigo-200',  'text' => 'text-indigo-700',  'badge' => 'bg-indigo-100 text-indigo-800'],
    ['bg' => 'bg-violet-50',  'border' => 'border-violet-200',  'text' => 'text-violet-700',  'badge' => 'bg-violet-100 text-violet-800'],
    ['bg' => 'bg-sky-50',     'border' => 'border-sky-200',     'text' => 'text-sky-700',     'badge' => 'bg-sky-100 text-sky-800'],
    ['bg' => 'bg-rose-50',    'border' => 'border-rose-200',    'text' => 'text-rose-700',    'badge' => 'bg-rose-100 text-rose-800'],
    ['bg' => 'bg-amber-50',   'border' => 'border-amber-200',   'text' => 'text-amber-700',   'badge' => 'bg-amber-100 text-amber-800'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - SIPOLA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .card-hover { transition: all .25s cubic-bezier(.4,0,.2,1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,.10); }
        @keyframes slideDown { from { opacity:0; transform:translateY(-16px);} to { opacity:1; transform:translateY(0);} }
        .toast-anim { animation: slideDown .35s ease forwards; }
        @keyframes fadeIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
        .modal-anim { animation: fadeIn .2s ease forwards; }
        .search-highlight { background: #fef3c7; border-radius:3px; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800">

<!-- TOAST -->
<?php if ($toast): ?>
<div id="toast" class="toast-anim fixed top-6 right-6 z-[100] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl font-semibold text-sm
    <?= $toast['type']==='success'?'bg-emerald-500 text-white':($toast['type']==='danger'?'bg-red-500 text-white':'bg-indigo-500 text-white') ?>">
    <span><?= $toast['type']==='success'?'✅':($toast['type']==='danger'?'<i class="fas fa-trash-alt"></i>':'<i class="fas fa-edit"></i>') ?></span>
    <?= $toast['msg'] ?>
</div>
<script>setTimeout(()=>{ const t=document.getElementById('toast'); if(t){t.style.opacity='0';t.style.transition='opacity .4s';setTimeout(()=>t.remove(),400);} },3000);</script>
<?php endif; ?>

<!-- NAVBAR -->
<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="relative bg-gradient-to-br from-emerald-500 to-teal-700 w-10 h-10 rounded-xl shadow-md flex items-center justify-center border border-white/20 overflow-hidden group">
                <div class="absolute -top-3 -right-3 w-8 h-8 bg-white opacity-10 rounded-full blur-sm group-hover:scale-150 transition-transform duration-700"></div>
                <div class="absolute -bottom-2 -left-2 w-6 h-6 bg-teal-300 opacity-20 rounded-full blur-sm group-hover:scale-150 transition-transform duration-700"></div>
                <i class="fa-solid fa-capsules text-white text-lg relative z-10 drop-shadow-md transform group-hover:rotate-12 transition-transform duration-300"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-emerald-600 font-black text-xl uppercase tracking-tighter leading-none mt-1">SIPOLA</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase hidden lg:block mt-0.5">Sistem Informasi Pengelolaan Obat dan Layanan Apotek</span>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <a href="dashboard.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Dashboard</a>
            <a href="kategori.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">Kategori</a>
            <a href="supplier.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Supplier</a>
            <a href="transaksi.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Transaksi</a>
            <a href="riwayat.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Riwayat</a>
            <a href="logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">Logout</a>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-6 py-10">

    <!-- HEADER BANNER -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-500 rounded-3xl p-8 mb-8 text-white relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute -right-4 bottom-0 w-28 h-28 bg-white/10 rounded-full"></div>
        <div class="relative flex justify-between items-center flex-wrap gap-4">
            <div>
                <p class="text-emerald-100 text-sm font-semibold tracking-widest uppercase mb-1">Manajemen Data</p>
                <h1 class="text-4xl font-black mb-2">Kategori Obat</h1>
                <p class="text-emerald-100 text-sm max-w-md">Kelola dan organisir semua kategori produk obat yang tersedia di apotek dengan mudah.</p>
            </div>
            <div class="flex gap-4 items-center">
                <div class="bg-white/20 backdrop-blur rounded-2xl p-4 text-center min-w-[80px]">
                    <div class="text-3xl font-black"><?= count($data) ?></div>
                    <div class="text-xs text-emerald-100 mt-1">Total Kategori</div>
                </div>
                <button onclick="openModal('tambah')" class="bg-white text-emerald-700 font-black px-6 py-3 rounded-2xl hover:bg-emerald-50 transition-all shadow-lg flex items-center gap-2">
                    <span class="text-lg">＋</span> Tambah Kategori
                </button>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 mb-8 flex gap-3 items-center">
        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input id="searchInput" type="text" placeholder="Cari nama atau deskripsi kategori..." oninput="filterKategori()"
            class="flex-1 outline-none text-slate-700 placeholder-slate-400 text-sm bg-transparent">
        <span id="countBadge" class="text-xs font-semibold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
            <?= count($data) ?> kategori
        </span>
    </div>

    <!-- GRID KATEGORI -->
    <div id="kategoriGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($data as $i => $kat):
            $c = $colors[$i % count($colors)];
            $icon = $icons[$i % count($icons)];
            $created = isset($kat['created_at']) ? date('d M Y', strtotime($kat['created_at'])) : '—';
        ?>
        <div class="kategori-card card-hover bg-white rounded-3xl border <?= $c['border'] ?> shadow-sm flex flex-col"
             data-nama="<?= strtolower($kat['nama_kategori']) ?>"
             data-deskripsi="<?= strtolower($kat['deskripsi'] ?? '') ?>">
            <!-- TOP -->
            <div class="<?= $c['bg'] ?> rounded-t-3xl p-5 flex items-center gap-4">
                <div class="text-4xl"><?= $icon ?></div>
                <div class="flex-1 min-w-0">
                    <span class="nama-teks text-lg font-black <?= $c['text'] ?> block truncate"><?= htmlspecialchars($kat['nama_kategori']) ?></span>
                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mt-1 <?= $c['badge'] ?>">
                        Dibuat <?= $created ?>
                    </span>
                </div>
            </div>
            <!-- BODY -->
            <div class="p-5 flex-1 flex flex-col justify-between gap-4">
                <p class="deskripsi-teks text-slate-500 text-sm leading-relaxed">
                    <?= htmlspecialchars($kat['deskripsi'] ?? 'Belum ada deskripsi untuk kategori ini.') ?>
                </p>
                <!-- ACTIONS -->
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <button onclick="openModal('edit', <?= $kat['id'] ?>, `<?= addslashes($kat['nama_kategori']) ?>`, `<?= addslashes($kat['deskripsi'] ?? '') ?>`)"
                        class="flex-1 flex items-center justify-center gap-1.5 bg-indigo-50 text-indigo-600 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-100 transition-all">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="konfirmasiHapus(<?= $kat['id'] ?>, `<?= addslashes($kat['nama_kategori']) ?>`)"
                        class="flex-1 flex items-center justify-center gap-1.5 bg-red-50 text-red-500 py-2.5 rounded-xl text-sm font-bold hover:bg-red-100 transition-all">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- EMPTY STATE -->
        <div id="emptyState" class="col-span-full hidden flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
            <div class="text-6xl mb-4"><i class="fas fa-search"></i></div>
            <p class="text-slate-500 font-semibold text-lg">Kategori tidak ditemukan</p>
            <p class="text-slate-400 text-sm mt-1">Coba kata kunci yang berbeda</p>
        </div>
    </div>

    <?php if (empty($data)): ?>
    <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
        <div class="text-6xl mb-4">🏷️</div>
        <p class="text-slate-500 font-semibold text-lg">Belum ada kategori</p>
        <p class="text-slate-400 text-sm mt-1 mb-6">Mulai dengan menambahkan kategori pertama Anda</p>
        <button onclick="openModal('tambah')" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-emerald-700 transition-all">+ Tambah Kategori</button>
    </div>
    <?php endif; ?>

</main>

<!-- MODAL TAMBAH/EDIT -->
<div id="formModal" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="bg-white w-full max-w-lg m-auto rounded-3xl shadow-2xl overflow-hidden modal-anim border-2 border-emerald-400 flex flex-col max-h-[90vh]">
        <div id="modalHeader" class="bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-white flex justify-between items-center shrink-0">
            <div>
                <h2 id="modalTitle" class="text-2xl font-black">Tambah Kategori</h2>
                <p class="text-emerald-100 text-sm mt-0.5" id="modalSubtitle">Isi data kategori baru</p>
            </div>
            <button onclick="closeModal()" class="text-white/80 hover:text-white text-3xl leading-none transition-all hover:scale-110">×</button>
        </div>
        <form id="formKategori" method="POST" class="p-6 space-y-5 overflow-y-auto grow">
            <input type="hidden" name="id" id="form_id">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Kategori <span class="text-red-400">*</span></label>
                <input type="text" name="nama_kategori" id="form_nama" required
                    class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-2xl outline-none focus:border-emerald-500 transition-colors text-slate-800 font-medium"
                    placeholder="Contoh: Antibiotik, Vitamin...">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Deskripsi</label>
                <textarea name="deskripsi" id="form_deskripsi" rows="4"
                    class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-2xl outline-none focus:border-emerald-500 transition-colors text-slate-700 resize-none"
                    placeholder="Jelaskan jenis obat yang masuk kategori ini..."></textarea>
                <p class="text-xs text-slate-400 mt-1 text-right"><span id="charCount">0</span> karakter</p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="flex-1 py-3.5 rounded-2xl font-bold border-2 border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="submitBtn" name="tambah" class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-700 hover:to-teal-600 text-white py-3.5 rounded-2xl font-bold transition-all shadow-lg shadow-emerald-100">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL HAPUS KONFIRMASI -->
<div id="hapusModal" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="bg-white w-full max-w-sm m-auto rounded-3xl shadow-2xl overflow-hidden modal-anim border-2 border-red-300 text-center p-8">
        <div class="text-5xl mb-4"><i class="fas fa-trash-alt"></i></div>
        <h3 class="text-xl font-black text-slate-800 mb-2">Hapus Kategori?</h3>
        <p class="text-slate-500 text-sm mb-1">Anda akan menghapus kategori:</p>
        <p id="hapusNama" class="font-black text-red-600 text-lg mb-6">—</p>
        <div class="flex gap-3">
            <button onclick="closeHapus()" class="flex-1 py-3 rounded-2xl font-bold border-2 border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">Batal</button>
            <a id="hapusLink" href="#" class="flex-1 py-3 rounded-2xl font-bold bg-red-500 hover:bg-red-600 text-white transition-all text-center">Hapus</a>
        </div>
    </div>
</div>

<script>
function openModal(mode, id='', nama='', deskripsi='') {
    const modal = document.getElementById('formModal');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const submitBtn = document.getElementById('submitBtn');

    document.getElementById('form_id').value = id;
    document.getElementById('form_nama').value = nama;
    document.getElementById('form_deskripsi').value = deskripsi;
    updateCharCount();

    if (mode === 'edit') {
        title.textContent = 'Edit Kategori';
        subtitle.textContent = 'Perbarui data kategori';
        submitBtn.name = 'update';
        submitBtn.textContent = 'Simpan Perubahan';
    } else {
        title.textContent = 'Tambah Kategori';
        subtitle.textContent = 'Isi data kategori baru';
        submitBtn.name = 'tambah';
        submitBtn.textContent = 'Simpan Kategori';
        document.getElementById('form_id').value = '';
        document.getElementById('form_nama').value = '';
        document.getElementById('form_deskripsi').value = '';
        updateCharCount();
    }

    modal.classList.remove('hidden');
    document.getElementById('form_nama').focus();
}

function closeModal() { document.getElementById('formModal').classList.add('hidden'); }

function konfirmasiHapus(id, nama) {
    document.getElementById('hapusNama').textContent = nama;
    document.getElementById('hapusLink').href = `kategori.php?hapus=${id}`;
    document.getElementById('hapusModal').classList.remove('hidden');
}
function closeHapus() { document.getElementById('hapusModal').classList.add('hidden'); }

function updateCharCount() {
    const ta = document.getElementById('form_deskripsi');
    document.getElementById('charCount').textContent = ta.value.length;
}
document.getElementById('form_deskripsi').addEventListener('input', updateCharCount);

function filterKategori() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.kategori-card');
    let visible = 0;
    cards.forEach(card => {
        const nama = card.dataset.nama || '';
        const desc = card.dataset.deskripsi || '';
        const match = !q || nama.includes(q) || desc.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('countBadge').textContent = visible + ' kategori';
    const empty = document.getElementById('emptyState');
    if (empty) empty.style.display = visible === 0 ? 'flex' : 'none';
}


document.getElementById('formModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
document.getElementById('hapusModal').addEventListener('click', function(e){ if(e.target===this) closeHapus(); });

document.addEventListener('keydown', e => { if(e.key==='Escape'){closeModal();closeHapus();} });
</script>
</body>
</html>
