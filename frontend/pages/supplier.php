<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$apiBaseUrl = "http://localhost/TUBES_KPL_NULL/backend/api";

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

    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Supplier berhasil ditambahkan!'];
    header("Location: supplier.php");
    exit;
}

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

    $_SESSION['toast'] = ['type' => 'info', 'msg' => 'Supplier berhasil diperbarui!'];
    header("Location: supplier.php");
    exit;
}

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

    $_SESSION['toast'] = ['type' => 'danger', 'msg' => 'Supplier berhasil dihapus.'];
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

$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Supplier | SIPOLA</title>
    
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
                <h1>Manajemen Supplier</h1>
                <p>Kelola daftar kontak dan informasi supplier obat</p>
            </div>
            <div class="header-actions">
                <button onclick="openModal('tambah')" class="btn btn-primary whitespace-nowrap">
                    <i class="fas fa-plus"></i> Tambah Supplier
                </button>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fade" style="animation-delay: 0.1s; animation-fill-mode: both;">
            <?php foreach($data as $supplier): ?>
                <div class="card flex flex-col justify-between" style="transition: transform .25s ease; border: 1px solid transparent;" onmouseenter="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'; this.style.borderColor='var(--frozen-water)';" onmouseleave="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)'; this.style.borderColor='transparent';">
                    <div>
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-[var(--azure-mist)]">
                            <div class="w-12 h-12 bg-[var(--mint-cream)] text-[var(--jungle-teal)] rounded-2xl flex items-center justify-center text-xl shadow-sm">
                                <i class="fas fa-truck-medical"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($supplier['nama_supplier']); ?></h3>
                                <div class="text-xs font-semibold text-[var(--success)] bg-[var(--azure-mist)] inline-block px-2 py-0.5 rounded-full mt-0.5">Active Partner</div>
                            </div>
                        </div>
                        
                        <div class="mb-5 space-y-3">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-phone-alt text-[var(--muted-teal)] mt-1"></i>
                                <div>
                                    <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-0.5">Telepon</div>
                                    <div class="text-sm font-medium text-slate-700"><?= htmlspecialchars($supplier['telepon'] ?? '-'); ?></div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-map-marker-alt text-[var(--muted-teal)] mt-1"></i>
                                <div>
                                    <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-0.5">Alamat</div>
                                    <div class="text-sm font-medium text-slate-700 leading-snug"><?= htmlspecialchars($supplier['alamat'] ?? '-'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="openEditModal('<?= $supplier['id']; ?>', '<?= addslashes(htmlspecialchars($supplier['nama_supplier'])); ?>', '<?= addslashes(htmlspecialchars($supplier['alamat'] ?? '')); ?>', '<?= addslashes(htmlspecialchars($supplier['telepon'] ?? '')); ?>')" class="btn btn-secondary flex-1 justify-center py-2 text-sm">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="supplier.php?hapus=<?= $supplier['id']; ?>" onclick="return confirm('Yakin hapus supplier ini?')" class="btn btn-danger justify-center px-4">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($data)): ?>
                <div class="col-span-full flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="text-6xl mb-4 text-slate-200"><i class="fas fa-truck-medical"></i></div>
                    <p class="text-slate-500 font-semibold text-lg">Belum ada supplier</p>
                    <p class="text-slate-400 text-sm mt-1">Tambahkan supplier untuk manajemen stok obat.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div id="formModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 id="modalTitle">Tambah Supplier</h2>
                <p id="modalSubtitle">Isi data supplier obat</p>
            </div>
            <button onclick="closeModal()" class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form id="formSupplier" method="POST" class="modal-body">
            <input type="hidden" name="id" id="form_id">
            
            <div class="form-group">
                <label class="form-label">Nama Supplier <span class="text-red-400">*</span></label>
                <input type="text" name="nama_supplier" id="form_nama" required class="form-control" placeholder="Contoh: PT. Kimia Farma">
            </div>
            
            <div class="form-group">
                <label class="form-label">Telepon</label>
                <input type="text" name="telepon" id="form_telepon" class="form-control" placeholder="Contoh: 08123456789">
            </div>
            
            <div class="form-group mb-6">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" id="form_alamat" rows="3" class="form-control resize-none" placeholder="Masukkan alamat lengkap supplier..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeModal()" class="btn flex-1 justify-center bg-slate-100 text-slate-600 hover:bg-slate-200">Batal</button>
                <button type="submit" id="submitBtn" name="tambah" class="btn btn-primary flex-1 justify-center text-base">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(mode, id='', nama='', alamat='', telepon='') {
    const modal = document.getElementById('formModal');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const submitBtn = document.getElementById('submitBtn');

    document.getElementById('form_id').value = id;
    document.getElementById('form_nama').value = nama;
    document.getElementById('form_alamat').value = alamat;
    document.getElementById('form_telepon').value = telepon;

    if (mode === 'edit') {
        title.textContent = 'Edit Supplier';
        subtitle.textContent = 'Perbarui data supplier';
        submitBtn.name = 'update';
        submitBtn.textContent = 'Simpan Perubahan';
    } else {
        title.textContent = 'Tambah Supplier';
        subtitle.textContent = 'Isi data supplier baru';
        submitBtn.name = 'tambah';
        submitBtn.textContent = 'Simpan Supplier';
        document.getElementById('form_id').value = '';
        document.getElementById('form_nama').value = '';
        document.getElementById('form_alamat').value = '';
        document.getElementById('form_telepon').value = '';
    }

    modal.classList.add('active');
    document.getElementById('form_nama').focus();
}

function openEditModal(id, nama, alamat, telepon) {
    openModal('edit', id, nama, alamat, telepon);
}

function closeModal() { document.getElementById('formModal').classList.remove('active'); }
document.getElementById('formModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });
</script>
</body>
</html>
