<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
$apiBaseUrl = "http://localhost/TUBES_KPL_NULL/backend/api";

$data = [];
$res = file_get_contents("$apiBaseUrl/riwayat.php");
if ($res !== false) { $r = json_decode($res, true); if (!empty($r['status'])) $data = $r['data']; }

$totalTransaksi = count($data);
$totalPendapatan = array_sum(array_column($data, 'total_bayar'));
$rataRata = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;
$today = date('Y-m-d');
$hariIni = count(array_filter($data, fn($r) => isset($r['tanggal']) && str_starts_with($r['tanggal'], $today)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi | SIPOLA</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style1.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @media print {
            #printArea { display: block !important; }
            body > *:not(#printOverlay) { display: none !important; }
            #printOverlay { display: block !important; position: static !important; background: white !important; z-index: 9999; width: 100%; height: 100%; }
        }
    </style>
</head>
<body>

<div class="app-layout">

    <?php include 'sidebar.php'; ?>

    <main class="main-content">

        <header class="page-header animate-fade">
            <div>
                <h1>Riwayat Transaksi</h1>
                <p>Pantau semua laporan aktivitas penjualan apotek</p>
            </div>
            <div class="header-actions hidden md:flex">
                <button class="btn-icon" title="Cetak Laporan Lengkap" onclick="window.print()"><i class="fas fa-print"></i></button>
            </div>
        </header>

        <div class="stat-grid animate-fade" style="animation-delay: 0.1s; animation-fill-mode: both;">
            <div class="stat-card">
                <div class="w-12 h-12 bg-[var(--mint-cream)] text-[var(--jungle-teal)] rounded-2xl flex items-center justify-center text-xl shadow-sm mb-3">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value text-slate-800"><?= $totalTransaksi ?></div>
            </div>
            <div class="stat-card bg-gradient-to-br from-[var(--jungle-teal)] to-[var(--primary-dark)] text-white border-transparent">
                <div class="w-12 h-12 bg-white/20 text-white rounded-2xl flex items-center justify-center text-xl shadow-sm mb-3">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-label text-emerald-100">Total Pendapatan</div>
                <div class="text-2xl font-black mt-1 text-white">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl shadow-sm mb-3">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="stat-label">Rata-rata Transaksi</div>
                <div class="text-2xl font-black mt-1 text-amber-600">Rp <?= number_format($rataRata, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl shadow-sm mb-3">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-label">Transaksi Hari Ini</div>
                <div class="text-3xl font-black mt-1 text-rose-600"><?= $hariIni ?></div>
            </div>
        </div>

        <div class="card p-4 mb-6 flex flex-wrap gap-4 items-center animate-fade" style="animation-delay: 0.2s; animation-fill-mode: both;">
            <div class="flex items-center gap-2 flex-1 min-w-[200px] bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
                <i class="fas fa-search text-slate-400"></i>
                <input id="searchKasir" type="text" placeholder="Cari nama kasir..." oninput="filterTable()" class="flex-1 outline-none text-slate-700 placeholder-slate-400 bg-transparent text-sm">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Dari:</label>
                <input type="date" id="dateFrom" onchange="filterTable()" class="form-control text-sm py-2 w-auto">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Sampai:</label>
                <input type="date" id="dateTo" onchange="filterTable()" class="form-control text-sm py-2 w-auto">
            </div>
            <button onclick="resetFilter()" class="btn btn-secondary py-2 px-4 text-sm font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 border-rose-100">Reset Filter</button>
            <span id="rowCount" class="text-xs font-bold text-[var(--success)] bg-[var(--mint-cream)] px-3 py-1.5 rounded-full ml-auto whitespace-nowrap"><?= $totalTransaksi ?> data</span>
        </div>

        <div class="card p-0 overflow-hidden animate-fade" style="animation-delay: 0.3s; animation-fill-mode: both;">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="riwayatTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4 cursor-pointer hover:text-[var(--jungle-teal)] transition-colors" onclick="sortTable('tanggal')">Waktu Transaksi <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-4 cursor-pointer hover:text-[var(--jungle-teal)] transition-colors" onclick="sortTable('kasir')">Nama Kasir <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-4">Jumlah Item</th>
                            <th class="px-6 py-4 cursor-pointer hover:text-[var(--jungle-teal)] transition-colors" onclick="sortTable('total')">Total Pembayaran <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (count($data) > 0): ?>
                            <?php foreach($data as $i => $row): ?>
                            <tr class="riwayat-row border-b border-slate-100 hover:bg-slate-50 transition-colors"
                                data-kasir="<?= strtolower(htmlspecialchars($row['kasir'])) ?>"
                                data-tanggal="<?= $row['tanggal'] ?? '' ?>"
                                data-total="<?= $row['total_bayar'] ?>">
                                <td class="px-6 py-4 text-slate-400 font-mono text-sm"><?= str_pad($i+1, 3, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                                    <div class="text-xs font-medium text-slate-400 mt-0.5"><i class="far fa-clock mr-1"></i><?= date('H:i', strtotime($row['tanggal'])) ?> WIB</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[var(--azure-mist)] text-[var(--jungle-teal)] flex items-center justify-center text-xs font-black uppercase shadow-sm border border-white">
                                            <?= strtoupper(substr($row['kasir'], 0, 1)) ?>
                                        </div>
                                        <span class="font-bold text-slate-700"><?= htmlspecialchars($row['kasir']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold"><?= $row['jumlah_item'] ?> Item</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-[var(--success)] text-base">Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="openDetailModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['kasir']) ?>', '<?= date('d M Y H:i', strtotime($row['tanggal'])) ?>', <?= $row['total_bayar'] ?>)"
                                        class="btn btn-secondary py-2 px-4 text-xs inline-flex justify-center">
                                        <i class="fas fa-file-invoice mr-1.5"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-24 text-center text-slate-400 border-b-0">
                                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                                    <i class="fas fa-box-open text-2xl"></i>
                                </div>
                                <div class="font-semibold text-lg">Belum ada riwayat transaksi</div>
                                <div class="text-sm mt-1">Data penjualan akan muncul di sini setelah transaksi kasir berhasil.</div>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="noResults" class="hidden px-6 py-16 text-center text-slate-400">
                <div class="text-4xl mb-3 text-slate-200"><i class="fas fa-search"></i></div>
                <div class="font-semibold text-lg">Tidak ada data yang cocok</div>
                <div class="text-sm mt-1">Coba ubah filter pencarian tanggal atau nama kasir.</div>
            </div>
        </div>

    </main>
</div>

<div id="detailModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 600px; padding: 0;">

        <div class="modal-header bg-[var(--jungle-teal)] text-white p-6 rounded-t-[var(--r-lg)]" style="margin: 0; border-radius: var(--r-xl) var(--r-xl) 0 0;">
            <div>
                <h2 class="text-white text-xl">Struk Pembayaran</h2>
                <p class="text-[var(--mint-cream)] text-sm opacity-90 mt-1" id="modal-subtitle">—</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="printDetail()" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                    <i class="fas fa-print"></i> Cetak Struk
                </button>
                <button onclick="closeDetail()" class="modal-close text-white hover:bg-white/10" style="background: transparent; color: white; border-radius: 50%;"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div class="bg-[var(--primary-dark)] text-white px-6 py-4 flex justify-between gap-4 border-t border-white/10 shadow-inner">
            <div>
                <span class="text-[var(--muted-teal)] text-xs block mb-0.5">Kasir:</span>
                <span class="font-bold text-sm" id="modal-kasir">—</span>
            </div>
            <div>
                <span class="text-[var(--muted-teal)] text-xs block mb-0.5">Tanggal Transaksi:</span>
                <span class="font-bold text-sm" id="modal-tanggal">—</span>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[50vh]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider sticky top-0">
                        <th class="px-6 py-4">Nama Produk Obat</th>
                        <th class="px-6 py-4 text-center">Qty</th>
                        <th class="px-6 py-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detail-body">
                    <tr><td colspan="3" class="px-6 py-10 text-center text-slate-400">Memuat rincian transaksi...</td></tr>
                </tbody>
            </table>
        </div>

        <div id="detail-footer" class="hidden">
            <div class="bg-[var(--mint-cream)] border-t-2 border-[var(--jungle-teal)] px-6 py-5 flex justify-between items-center rounded-b-[var(--r-xl)]">
                <span class="font-black text-slate-700 uppercase tracking-wider">Total Tagihan</span>
                <span class="font-black text-[var(--jungle-teal)] text-2xl" id="detail-total-footer">—</span>
            </div>
        </div>
    </div>
</div>

<div id="printOverlay" style="display:none;">
    <div id="printArea"></div>
</div>

<script>
const API_BASE = '<?= $apiBaseUrl ?>';
let sortDir = {};
let currentPrintData = null;

function openDetailModal(id, kasir, tanggal, total) {
    document.getElementById('detailModal').classList.add('active');
    document.getElementById('modal-subtitle').textContent = 'ID Riwayat: #' + String(id).padStart(5, '0');
    document.getElementById('modal-kasir').textContent = kasir;
    document.getElementById('modal-tanggal').textContent = tanggal;
    document.getElementById('detail-footer').classList.add('hidden');
    document.getElementById('detail-body').innerHTML = '<tr><td colspan="3" class="px-6 py-10 text-center text-slate-400"><i class="fas fa-circle-notch fa-spin text-2xl mb-3 block"></i> Memuat Data...</td></tr>';

    fetch(`${API_BASE}/transaksi.php?id_riwayat=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status && res.data.length > 0) {
                let html = '', grandTotal = 0;
                res.data.forEach(item => {
                    grandTotal += parseInt(item.sub_total);
                    html += `<tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-700">${item.nama_obat || '(Item Dihapus - ID: '+item.id_obat+')'}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-xs font-black">${item.jumlah}x</span>
                        </td>
                        <td class="px-6 py-4 text-right font-black text-slate-800">Rp ${parseInt(item.sub_total).toLocaleString('id-ID')}</td>
                    </tr>`;
                });
                document.getElementById('detail-body').innerHTML = html;
                document.getElementById('detail-total-footer').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
                document.getElementById('detail-footer').classList.remove('hidden');
                currentPrintData = { id, kasir, tanggal, total, items: res.data };
            } else {
                document.getElementById('detail-body').innerHTML = '<tr><td colspan="3" class="px-6 py-10 text-center text-slate-400">Tidak ada detail item</td></tr>';
            }
        })
        .catch(() => {
            document.getElementById('detail-body').innerHTML = '<tr><td colspan="3" class="px-6 py-10 text-center text-red-400">Terjadi kesalahan memuat data.</td></tr>';
        });
}

function closeDetail() { document.getElementById('detailModal').classList.remove('active'); }

function printDetail() {
    if (!currentPrintData) return;
    const { id, kasir, tanggal, total, items } = currentPrintData;
    let rows = items.map(i => `<tr><td style="padding:4px 0">${i.nama_obat||'ID:'+i.id_obat}</td><td style="text-align:center">${i.jumlah}</td><td style="text-align:right">Rp ${parseInt(i.sub_total).toLocaleString('id-ID')}</td></tr>`).join('');
    
    document.getElementById('printArea').innerHTML = `
        <div style="font-family:monospace; max-width:300px; margin:0 auto; padding:20px 10px; color:#000;">
            <div style="text-align:center; margin-bottom:15px; border-bottom:1px dashed #000; padding-bottom:10px;">
                <h2 style="font-size:18px; font-weight:bold; margin:0 0 5px 0;">APOTEK SIPOLA</h2>
                <p style="font-size:10px; margin:0;">Sistem Informasi Pengelolaan Obat</p>
            </div>
            
            <div style="font-size:11px; margin-bottom:15px;">
                <table style="width:100%;">
                    <tr><td>No. Transaksi</td><td>: #${String(id).padStart(5, '0')}</td></tr>
                    <tr><td>Kasir</td><td>: ${kasir.toUpperCase()}</td></tr>
                    <tr><td>Waktu</td><td>: ${tanggal}</td></tr>
                </table>
            </div>
            
            <div style="border-top:1px dashed #000; border-bottom:1px dashed #000; padding:5px 0; margin-bottom:10px;">
                <table style="width:100%; font-size:11px;">
                    <thead><tr>
                        <th style="text-align:left; padding-bottom:5px;">Item</th>
                        <th style="text-align:center; padding-bottom:5px;">Qty</th>
                        <th style="text-align:right; padding-bottom:5px;">Harga</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
            
            <table style="width:100%; font-size:12px; font-weight:bold; margin-bottom:20px;">
                <tr><td colspan="2">TOTAL KEMBALI</td><td style="text-align:right;">Rp ${parseInt(total).toLocaleString('id-ID')}</td></tr>
            </table>
            
            <div style="text-align:center; font-size:10px; border-top:1px dashed #000; padding-top:10px;">
                <p style="margin:0;">* TERIMA KASIH ATAS KUNJUNGAN ANDA *</p>
                <p style="margin:3px 0 0 0;">Semoga lekas sembuh</p>
            </div>
        </div>
    `;
    
    document.getElementById('printOverlay').style.display = 'block';
    window.print();
    setTimeout(() => { document.getElementById('printOverlay').style.display = 'none'; }, 100);
}

function filterTable() {
    const q = document.getElementById('searchKasir').value.toLowerCase();
    const from = document.getElementById('dateFrom').value;
    const to = document.getElementById('dateTo').value;
    const rows = document.querySelectorAll('.riwayat-row');
    let visible = 0;
    
    rows.forEach(row => {
        const kasir = row.dataset.kasir || '';
        const tanggal = row.dataset.tanggal || '';
        const matchQ = !q || kasir.includes(q);
        const matchFrom = !from || tanggal >= from;
        const matchTo = !to || tanggal.substring(0,10) <= to;
        const show = matchQ && matchFrom && matchTo;
        
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    
    document.getElementById('rowCount').textContent = visible + ' data';
    document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
    document.getElementById('riwayatTable').style.display = visible === 0 ? 'none' : '';
}

function resetFilter() {
    document.getElementById('searchKasir').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    filterTable();
}

function sortTable(col) {
    sortDir[col] = !sortDir[col];
    const tbody = document.getElementById('tableBody');
    const rows = Array.from(tbody.querySelectorAll('.riwayat-row'));
    
    rows.sort((a, b) => {
        let va = '', vb = '';
        if (col === 'kasir') { va = a.dataset.kasir; vb = b.dataset.kasir; }
        if (col === 'tanggal') { va = a.dataset.tanggal; vb = b.dataset.tanggal; }
        if (col === 'total') { va = parseFloat(a.dataset.total); vb = parseFloat(b.dataset.total); }
        
        if (typeof va === 'number') return sortDir[col] ? va-vb : vb-va;
        return sortDir[col] ? va.localeCompare(vb) : vb.localeCompare(va);
    });
    
    rows.forEach(r => tbody.appendChild(r));
 
    event.currentTarget.querySelector('i').className = sortDir[col] ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1';
}

document.getElementById('detailModal').addEventListener('click', e => { if(e.target===document.getElementById('detailModal')) closeDetail(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') closeDetail(); });
</script>
</body>
</html>
