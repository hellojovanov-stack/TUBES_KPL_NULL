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
    <title>Manajemen Kategori | SIPOLA</title>
    

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style1.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php if ($toast): ?>
<div id="toast" class="toast">
    <span><?= $toast['type']==='success'?'✅':($toast['type']==='danger'?'<i class="fas fa-trash-alt text-red-500"></i>':'<i class="fas fa-edit text-indigo-500"></i>') ?></span>
    <?= htmlspecialchars($toast['msg']) ?>
</div>
<script>
document.getElementById('toast').style.display = 'flex';
setTimeout(()=>{ 
    const t=document.getElementById('toast'); 
    if(t){
        t.style.opacity='0';
        t.style.transition='opacity .4s';
        setTimeout(()=>t.remove(),400);
    } 
},3000);
</script>
<?php endif; ?>

<div class="app-layout">

    <?php include 'sidebar.php'; ?>
    <main class="main-content">

        <header class="page-header animate-fade">
            <div>
                <h1>Kategori Obat</h1>
                <p>Kelola dan organisir semua kategori produk obat</p>
            </div>
            <div class="header-actions hidden md:flex">
                <button class="btn-icon" title="Notifications"><i class="fas fa-bell"></i></button>
            </div>
        </header>

        <div class="bg-gradient-to-r from-[var(--jungle-teal)] to-[var(--success)] rounded-[var(--r-xl)] p-8 mb-8 text-white relative overflow-hidden animate-fade" style="animation-delay: 0.1s; animation-fill-mode: both;">
            <div class="absolute -right-8 -top-8 w-48 h-48 bg-white/10 rounded-full"></div>
            <div class="absolute -right-4 bottom-0 w-28 h-28 bg-white/10 rounded-full"></div>
            <div class="relative flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h2 class="text-3xl font-black mb-1">Daftar Kategori</h2>
                    <p class="text-[var(--azure-mist)] text-sm max-w-md opacity-90">Kategorikan obat untuk memudahkan pencarian dan manajemen inventaris apotek.</p>
                </div>
                <div class="flex gap-4 items-center">
                    <div class="bg-white/20 backdrop-blur rounded-2xl p-4 text-center min-w-[80px]">
                        <div class="text-3xl font-black"><?= count($data) ?></div>
                        <div class="text-xs text-[var(--azure-mist)] mt-1 opacity-90">Total Kategori</div>
                    </div>
                    <button onclick="openModal('tambah')" class="bg-white text-[var(--jungle-teal)] font-black px-6 py-3 rounded-[var(--r-md)] hover:bg-[var(--mint-cream)] transition-all shadow-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
                </div>
            </div>
        </div>

        <div class="search-wrap animate-fade" style="animation-delay: 0.2s; animation-fill-mode: both;">
            <i class="fas fa-search text-slate-400 text-lg"></i>
            <input id="searchInput" type="text" placeholder="Cari nama atau deskripsi kategori..." oninput="filterKategori()" class="search-input">
            <span id="countBadge" class="text-xs font-semibold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                <?= count($data) ?> kategori
            </span>
        </div>

        <div id="kategoriGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade" style="animation-delay: 0.3s; animation-fill-mode: both;">
            <?php foreach($data as $i => $kat):
                $c = $colors[$i % count($colors)];
                $icon = $icons[$i % count($icons)];
                $created = isset($kat['created_at']) ? date('d M Y', strtotime($kat['created_at'])) : '—';
            ?>
            <div class="card flex flex-col p-0 overflow-hidden"
                 data-nama="<?= strtolower(htmlspecialchars($kat['nama_kategori'])) ?>"
                 data-deskripsi="<?= strtolower(htmlspecialchars($kat['deskripsi'] ?? '')) ?>" style="transition: transform .25s ease; border: 1px solid transparent;">

                <div class="<?= $c['bg'] ?> p-5 flex items-center gap-4">
                    <div class="text-4xl opacity-80"><?= $icon ?></div>
                    <div class="flex-1 min-w-0">
                        <span class="nama-teks text-lg font-black <?= $c['text'] ?> block truncate"><?= htmlspecialchars($kat['nama_kategori']) ?></span>
                        <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mt-1 <?= $c['badge'] ?>">
                            Dibuat <?= $created ?>
                        </span>
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col justify-between gap-4">
                    <p class="deskripsi-teks text-slate-500 text-sm leading-relaxed">
                        <?= htmlspecialchars($kat['deskripsi'] ?? 'Belum ada deskripsi untuk kategori ini.') ?>
                    </p>

                    <div class="flex gap-2 pt-4 border-t border-slate-100 mt-2">
                        <button onclick="openModal('edit', <?= $kat['id'] ?>, `<?= addslashes(htmlspecialchars($kat['nama_kategori'])) ?>`, `<?= addslashes(htmlspecialchars($kat['deskripsi'] ?? '')) ?>`)"
                            class="btn btn-secondary flex-1 justify-center">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="konfirmasiHapus(<?= $kat['id'] ?>, `<?= addslashes(htmlspecialchars($kat['nama_kategori'])) ?>`)"
                            class="btn btn-danger flex-1 justify-center">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>


            <div id="emptyState" class="col-span-full hidden flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="text-6xl mb-4 text-slate-200"><i class="fas fa-search"></i></div>
                <p class="text-slate-500 font-semibold text-lg">Kategori tidak ditemukan</p>
                <p class="text-slate-400 text-sm mt-1">Coba kata kunci yang berbeda</p>
            </div>
        </div>

    </main>
</div>

<div id="formModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 id="modalTitle">Tambah Kategori</h2>
                <p id="modalSubtitle">Isi data kategori baru</p>
            </div>
            <button onclick="closeModal()" class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form id="formKategori" method="POST" class="modal-body">
            <input type="hidden" name="id" id="form_id">
            <div class="form-group">
                <label class="form-label">Nama Kategori <span class="text-red-400">*</span></label>
                <input type="text" name="nama_kategori" id="form_nama" required class="form-control" placeholder="Contoh: Antibiotik, Vitamin...">
            </div>
            <div class="form-group mb-6">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="form_deskripsi" rows="4" class="form-control resize-none" placeholder="Jelaskan jenis obat yang masuk kategori ini..."></textarea>
                <p class="text-xs text-slate-400 mt-1 text-right"><span id="charCount">0</span> karakter</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal()" class="btn flex-1 justify-center bg-slate-100 text-slate-600 hover:bg-slate-200">Batal</button>
                <button type="submit" id="submitBtn" name="tambah" class="btn btn-primary flex-1 justify-center text-base">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<div id="hapusModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 400px;">
        <div class="p-8 text-center">
            <div class="text-5xl mb-4 text-red-500"><i class="fas fa-trash-alt"></i></div>
            <h3 class="text-xl font-black text-slate-800 mb-2">Hapus Kategori?</h3>
            <p class="text-slate-500 text-sm mb-1">Anda akan menghapus kategori:</p>
            <p id="hapusNama" class="font-black text-red-600 text-lg mb-6">—</p>
            <div class="flex gap-3">
                <button onclick="closeHapus()" class="btn flex-1 justify-center bg-slate-100 text-slate-600 hover:bg-slate-200">Batal</button>
                <a id="hapusLink" href="#" class="btn btn-danger flex-1 justify-center text-center">Hapus</a>
            </div>
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

    modal.classList.add('active');
    document.getElementById('form_nama').focus();
}

function closeModal() { document.getElementById('formModal').classList.remove('active'); }

function konfirmasiHapus(id, nama) {
    document.getElementById('hapusNama').textContent = nama;
    document.getElementById('hapusLink').href = `kategori.php?hapus=${id}`;
    document.getElementById('hapusModal').classList.add('active');
}
function closeHapus() { document.getElementById('hapusModal').classList.remove('active'); }

function updateCharCount() {
    const ta = document.getElementById('form_deskripsi');
    if(ta) document.getElementById('charCount').textContent = ta.value.length;
}
document.getElementById('form_deskripsi')?.addEventListener('input', updateCharCount);

function filterKategori() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('#kategoriGrid .card');
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

const cards = document.querySelectorAll('#kategoriGrid .card');
cards.forEach(card => {
    card.addEventListener('mouseenter', () => { card.style.transform = 'translateY(-4px)'; card.style.boxShadow = 'var(--shadow-lg)'; card.style.borderColor = 'var(--frozen-water)'; });
    card.addEventListener('mouseleave', () => { card.style.transform = 'translateY(0)'; card.style.boxShadow = 'var(--shadow)'; card.style.borderColor = 'transparent'; });
});
</script>
</body>
</html>
