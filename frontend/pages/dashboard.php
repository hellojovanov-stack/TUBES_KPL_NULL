<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
// testing
require_once __DIR__ . '/../../backend/models/Obat.php';

$apiBaseUrl = "http://localhost/TUBES_KPL_NULL/backend/api";

$data_kategori = [];
$res_kat = file_get_contents("$apiBaseUrl/kategori.php");
if ($res_kat !== false) {
    $resp_kat = json_decode($res_kat, true);
    if (isset($resp_kat['status']) && $resp_kat['status'] === true) {
        $data_kategori = $resp_kat['data'];
    }
}

$data_supplier = [];
$res_sup = file_get_contents("$apiBaseUrl/supplier.php");
if ($res_sup !== false) {
    $resp_sup = json_decode($res_sup, true);
    if (isset($resp_sup['status']) && $resp_sup['status'] === true) {
        $data_supplier = $resp_sup['data'];
    }
}

if (isset($_POST['tambah'])) {
    $postFields = [
        'nama_obat' => $_POST['nama_obat'],
        'kategori' => $_POST['kategori_text'] ?? '', // legacy text
        'id_kategori' => $_POST['id_kategori'] ?? '',
        'id_supplier' => $_POST['id_supplier'] ?? '',
        'stok' => $_POST['stok'],
        'harga' => $_POST['harga']
    ];

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $postFields['gambar'] = new CURLFile($_FILES['gambar']['tmp_name'], $_FILES['gambar']['type'], $_FILES['gambar']['name']);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$apiBaseUrl/obat.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['update'])) {
    $postData = [
        'id' => $_POST['id'],
        'nama_obat' => $_POST['nama_obat'],
        'kategori' => $_POST['kategori_text'] ?? '',
        'id_kategori' => $_POST['id_kategori'] ?? '',
        'id_supplier' => $_POST['id_supplier'] ?? '',
        'stok' => $_POST['stok'],
        'harga' => $_POST['harga']
    ];
    
    $options = [
        'http' => [
            'method'  => 'PUT',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($postData)
        ]
    ];
    $context  = stream_context_create($options);
    file_get_contents("$apiBaseUrl/obat.php", false, $context);

    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $options = [
        'http' => [
            'method'  => 'DELETE',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode(['id' => $id])
        ]
    ];
    $context  = stream_context_create($options);
    file_get_contents("$apiBaseUrl/obat.php", false, $context);

    header("Location: dashboard.php");
    exit;
}

$keyword = "";
$kategori_filter = "";
$data = [];

$result = file_get_contents("$apiBaseUrl/obat.php");
if ($result !== false) {
    $response = json_decode($result, true);
    if (isset($response['status']) && $response['status'] === true) {
        $allData = $response['data'];
        
        if (isset($_GET['search'])) {
            $keyword_raw = $_GET['keyword'] ?? '';
            $keyword = strtolower(htmlspecialchars($keyword_raw));
            $kategori_filter = $_GET['kategori_filter'] ?? '';
            
            $kategori_filter_nama = '';
            if (!empty($kategori_filter)) {
                foreach($data_kategori as $kat) {
                    if ($kat['id'] == $kategori_filter) {
                        $kategori_filter_nama = strtolower($kat['nama_kategori']);
                        break;
                    }
                }
            }
            
            foreach($allData as $item) {
                $matchKeyword = empty($keyword) || strpos(strtolower($item['nama_obat']), $keyword) !== false;
                
                $matchKategori = true;
                if (!empty($kategori_filter)) {
                    $isIdMatch = (!empty($item['id_kategori']) && $item['id_kategori'] == $kategori_filter);
                    $isNameMatch = (!empty($kategori_filter_nama) && strtolower($item['kategori'] ?? '') == $kategori_filter_nama);
                    $matchKategori = $isIdMatch || $isNameMatch;
                }
                
                if ($matchKeyword && $matchKategori) {
                    $data[] = $item;
                }
            }
        } else {
            $data = $allData;
        }
    }
}

// Calculate summary stats
$total_obat = count($data);
$total_stok = array_reduce($data, fn($sum, $item) => $sum + $item['stok'], 0);
$stok_menipis = array_reduce($data, fn($sum, $item) => $sum + ($item['stok'] < 10 ? 1 : 0), 0);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SIPOLA</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Style with variables -->
    <link rel="stylesheet" href="../css/style1.css">
    
    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Force modal override vs Tailwind */
        .modal-overlay {
            display: flex !important;
            opacity: 0 !important;
            pointer-events: none !important;
            transition: opacity 0.3s ease !important;
        }
        .modal-overlay.active {
            opacity: 1 !important;
            pointer-events: auto !important;
        }
    </style>
</head>
<body>

<div class="app-layout">
    
    <!-- INCLUDE SHARED SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <!-- PAGE HEADER -->
        <header class="page-header animate-fade">
            <div>
                <h1>Dashboard</h1>
                <p>Ringkasan sistem inventaris obat dan apotek</p>
            </div>
            <div class="header-actions hidden md:flex">
                <button class="btn-icon" title="Notifications"><i class="fas fa-bell"></i></button>
            </div>
        </header>

        <!-- STAT CARDS -->
        <div class="stat-grid animate-fade" style="animation-delay: 0.1s; animation-fill-mode: both;">
            <div class="stat-card primary">
                <i class="fas fa-pills stat-icon"></i>
                <div class="stat-label">Total Jenis Obat</div>
                <div class="stat-value"><?= $total_obat ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-boxes-stacked stat-icon text-teal-200"></i>
                <div class="stat-label text-slate-500">Total Stok Fisik</div>
                <div class="stat-value text-teal-800"><?= $total_stok ?></div>
            </div>
            <div class="stat-card <?= $stok_menipis > 0 ? 'bg-red-50' : '' ?>">
                <i class="fas fa-triangle-exclamation stat-icon <?= $stok_menipis > 0 ? 'text-red-200' : 'text-teal-200' ?>"></i>
                <div class="stat-label <?= $stok_menipis > 0 ? 'text-red-500' : 'text-slate-500' ?>">Stok Menipis</div>
                <div class="stat-value <?= $stok_menipis > 0 ? 'text-red-600' : 'text-teal-800' ?>"><?= $stok_menipis ?></div>
            </div>
        </div>

        <!-- SEARCH WRAP -->
        <form method="GET" class="search-wrap animate-fade" style="animation-delay: 0.2s; animation-fill-mode: both;">
            <i class="fas fa-search text-slate-400 text-lg"></i>
            <input 
                type="text" 
                name="keyword" 
                value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>" 
                class="search-input" 
                placeholder="Cari nama obat..."
            >
            <div style="width: 1px; height: 24px; background: var(--frozen-water); margin: 0 5px;"></div>
            <select name="kategori_filter" class="search-input" style="flex: 0 0 auto; width: auto; cursor: pointer;">
                <option value="">Semua Kategori</option>
                <?php foreach($data_kategori as $kat): ?>
                    <option value="<?= $kat['id'] ?>" <?= (isset($_GET['kategori_filter']) && $_GET['kategori_filter'] == $kat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="search" class="btn btn-secondary hidden sm:flex">Cari</button>
            <button type="button" class="btn btn-primary whitespace-nowrap" onclick="openModal()">
                <i class="fas fa-plus"></i> Tambah Obat
            </button>
        </form>

        <!-- DATA GRID -->
        <div class="obat-grid animate-fade" style="animation-delay: 0.3s; animation-fill-mode: both;">
            <?php if(count($data) > 0): ?>
                <?php foreach($data as $obat): ?>
                    <div class="obat-card">
                        
                        <?php if(!empty($obat['gambar'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($obat['gambar']) ?>" class="obat-card-img" alt="Gambar Obat">
                        <?php else: ?>
                            <div class="obat-card-no-img"><i class="fas fa-capsules"></i></div>
                        <?php endif; ?>
                        
                        <div class="obat-card-body">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="obat-card-name"><?= htmlspecialchars($obat['nama_obat']) ?></h3>
                                    <p class="obat-card-cat"><?= htmlspecialchars($obat['kategori']) ?></p>
                                </div>
                                <div class="stok-badge <?= $obat['stok'] < 10 ? 'low' : '' ?>">
                                    Stok <?= $obat['stok'] ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <span class="obat-card-price-label">Harga Jual</span>
                                <div class="obat-card-price">Rp <?= number_format($obat['harga'], 0, ',', '.') ?></div>
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="openEditModal(
                                    '<?= $obat['id'] ?>',
                                    '<?= addslashes(htmlspecialchars($obat['nama_obat'])) ?>',
                                    '<?= $obat['id_kategori'] ?? '' ?>',
                                    '<?= $obat['id_supplier'] ?? '' ?>',
                                    '<?= $obat['stok'] ?>',
                                    '<?= $obat['harga'] ?>'
                                )" class="btn btn-secondary flex-1 justify-center"><i class="fas fa-pen"></i> Edit</button>
                                
                                <a href="dashboard.php?hapus=<?= $obat['id'] ?>" 
                                   onclick="return confirm('Yakin hapus obat ini?')" 
                                   class="btn btn-danger justify-center" style="padding:10px 14px;"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-2xl border-2 border-dashed border-slate-200">
                    <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-2xl mb-4">
                        <i class="fas fa-pills"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Data obat tidak ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- ADD MODAL -->
<div id="modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2>Tambah Obat</h2>
                <p>Tambahkan data obat ke dalam inventaris</p>
            </div>
            <button onclick="closeModal()" class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="modal-body">
            
            <div class="form-group">
                <label class="form-label">Gambar Obat</label>
                <input type="file" name="gambar" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-emerald-50 file:text-teal-700 file:font-semibold hover:file:bg-emerald-100 transition-all cursor-pointer">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Obat</label>
                <input type="text" name="nama_obat" placeholder="Contoh: Paracetamol 500mg" required class="form-control">
            </div>

            <div class="grid grid-cols-2 gap-4 form-group">
                <div>
                    <label class="form-label">Kategori</label>
                    <select name="id_kategori" required class="form-control">
                        <option value="">-- Pilih --</option>
                        <?php foreach($data_kategori as $kat): ?>
                            <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Supplier</label>
                    <select name="id_supplier" class="form-control">
                        <option value="">-- Pilih --</option>
                        <?php foreach($data_supplier as $sup): ?>
                            <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['nama_supplier']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="form-label">Stok Awal</label>
                    <input type="number" name="stok" placeholder="0" required class="form-control">
                </div>
                <div>
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="harga" placeholder="0" required class="form-control">
                </div>
            </div>

            <button type="submit" name="tambah" class="btn btn-primary w-full justify-center py-3 text-base">Simpan Obat Baru</button>

        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--warning), #f4a261);">
            <div>
                <h2>Edit Obat</h2>
                <p>Ubah detail informasi obat</p>
            </div>
            <button onclick="closeEditModal()" class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        
        <form method="POST" class="modal-body">
            <input type="hidden" name="id" id="edit_id">

            <div class="form-group">
                <label class="form-label">Nama Obat</label>
                <input type="text" name="nama_obat" id="edit_nama" required class="form-control">
            </div>

            <div class="grid grid-cols-2 gap-4 form-group">
                <div>
                    <label class="form-label">Kategori</label>
                    <select name="id_kategori" id="edit_id_kategori" required class="form-control">
                        <option value="">-- Pilih --</option>
                        <?php foreach($data_kategori as $kat): ?>
                            <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Supplier</label>
                    <select name="id_supplier" id="edit_id_supplier" class="form-control">
                        <option value="">-- Pilih --</option>
                        <?php foreach($data_supplier as $sup): ?>
                            <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['nama_supplier']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="form-label">Update Stok</label>
                    <input type="number" name="stok" id="edit_stok" required class="form-control">
                </div>
                <div>
                    <label class="form-label">Update Harga (Rp)</label>
                    <input type="number" name="harga" id="edit_harga" required class="form-control">
                </div>
            </div>

            <button type="submit" name="update" class="btn btn-primary w-full justify-center py-3 text-base" style="background: var(--warning); color: var(--dark-text);">Simpan Perubahan</button>
        </form>
    </div>
</div>

<script src="../js/modal.js"></script>
<script>
function openEditModal(id, nama, id_kategori, id_supplier, stok, harga) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_id_kategori').value = id_kategori;
    document.getElementById('edit_id_supplier').value = id_supplier;
    document.getElementById('edit_stok').value = stok;
    document.getElementById('edit_harga').value = harga;
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeModal(); closeEditModal(); }
});
</script>
</body>
</html>
