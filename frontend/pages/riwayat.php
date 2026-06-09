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
    <title>Riwayat Transaksi - SIPOLA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .fade-in { animation: fadeIn .3s ease forwards; }
        @keyframes modalIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
        .modal-anim { animation: modalIn .2s ease forwards; }
        @media print {
            #printArea { display: block !important; }
            body > *:not(#printOverlay) { display: none; }
            #printOverlay { display: block !important; position: static !important; background: white !important; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800">

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
            <a href="kategori.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Kategori</a>
            <a href="supplier.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Supplier</a>
            <a href="transaksi.php" class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">Transaksi</a>
            <a href="riwayat.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">Riwayat</a>
            <a href="logout.php" class="text-slate-500 hover:text-red-500 font-medium transition-colors">Logout</a>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-6 py-10">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 rounded-3xl p-8 mb-8 text-white relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute right-20 -bottom-6 w-32 h-32 bg-white/10 rounded-full"></div>
        <div class="relative">
            <p class="text-indigo-200 text-sm font-semibold tracking-widest uppercase mb-1">Laporan Penjualan</p>
            <h1 class="text-4xl font-black mb-2">Riwayat Transaksi</h1>
            <p class="text-indigo-200 text-sm">Pantau semua aktivitas transaksi yang telah dilakukan di apotek.</p>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm fade-in">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-clipboard-list"></i></div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Transaksi</span>
            </div>
            <div class="text-3xl font-black text-slate-800"><?= $totalTransaksi ?></div>
            <div class="text-xs text-slate-400 mt-1">semua waktu</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm fade-in">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-money-bill-wave"></i></div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pendapatan</span>
            </div>
            <div class="text-2xl font-black text-emerald-600">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></div>
            <div class="text-xs text-slate-400 mt-1">kumulatif</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm fade-in">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-chart-bar"></i></div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rata-rata</span>
            </div>
            <div class="text-2xl font-black text-amber-600">Rp <?= number_format($rataRata, 0, ',', '.') ?></div>
            <div class="text-xs text-slate-400 mt-1">per transaksi</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm fade-in">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-xl"><i class="fas fa-calendar-alt"></i></div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hari Ini</span>
            </div>
            <div class="text-3xl font-black text-rose-600"><?= $hariIni ?></div>
            <div class="text-xs text-slate-400 mt-1">transaksi</div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 mb-6 flex flex-wrap gap-3 items-center">
        <div class="flex items-center gap-2 flex-1 min-w-[200px]">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="searchKasir" type="text" placeholder="Cari kasir..." oninput="filterTable()"
                class="flex-1 outline-none text-slate-700 placeholder-slate-400 text-sm bg-transparent">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-500">Dari:</label>
            <input type="date" id="dateFrom" onchange="filterTable()" class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-indigo-400">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-500">Sampai:</label>
            <input type="date" id="dateTo" onchange="filterTable()" class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-indigo-400">
        </div>
        <button onclick="resetFilter()" class="text-xs font-bold text-slate-500 hover:text-red-500 transition-colors px-3 py-2 rounded-xl hover:bg-red-50">Reset ↺</button>
        <span id="rowCount" class="text-xs font-semibold text-slate-400 bg-slate-100 px-3 py-1.5 rounded-full ml-auto"><?= $totalTransaksi ?> data</span>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse" id="riwayatTable">
            <thead>
                <tr class="bg-slate-50 border-b-2 border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <th class="px-5 py-4">No</th>
                    <th class="px-5 py-4 cursor-pointer hover:text-indigo-600" onclick="sortTable('tanggal')">Tanggal ↕</th>
                    <th class="px-5 py-4 cursor-pointer hover:text-indigo-600" onclick="sortTable('kasir')">Kasir ↕</th>
                    <th class="px-5 py-4">Jumlah Item</th>
                    <th class="px-5 py-4 cursor-pointer hover:text-indigo-600" onclick="sortTable('total')">Total Bayar ↕</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if (count($data) > 0): ?>
                    <?php foreach($data as $i => $row): ?>
                    <tr class="riwayat-row border-b border-slate-100 hover:bg-indigo-50/40 transition-colors"
                        data-kasir="<?= strtolower(htmlspecialchars($row['kasir'])) ?>"
                        data-tanggal="<?= $row['tanggal'] ?? '' ?>"
                        data-total="<?= $row['total_bayar'] ?>">
                        <td class="px-5 py-4 text-slate-400 font-mono text-sm"><?= $i+1 ?></td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-800 text-sm"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                            <div class="text-xs text-slate-400"><?= date('H:i', strtotime($row['tanggal'])) ?> WIB</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-black uppercase">
                                    <?= strtoupper(substr($row['kasir'], 0, 1)) ?>
                                </div>
                                <span class="font-semibold text-slate-700"><?= htmlspecialchars($row['kasir']) ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs font-bold"><?= $row['jumlah_item'] ?> item</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-black text-emerald-600 text-base">Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <button onclick="openDetailModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['kasir']) ?>', '<?= date('d M Y H:i', strtotime($row['tanggal'])) ?>', <?= $row['total_bayar'] ?>)"
                                    class="bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-all">
                                    <i class="fas fa-search"></i> Detail
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="px-5 py-20 text-center text-slate-400">
                        <div class="text-5xl mb-3"><i class="fas fa-box-open text-5xl mb-3"></i></div>
                        <div class="font-semibold">Belum ada riwayat transaksi</div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- NO RESULTS -->
        <div id="noResults" class="hidden px-5 py-16 text-center text-slate-400">
            <div class="text-4xl mb-3"><i class="fas fa-search"></i></div>
            <div class="font-semibold">Tidak ada data yang cocok</div>
            <div class="text-sm mt-1">Coba ubah filter pencarian</div>
        </div>
    </div>

</main>

<!-- MODAL DETAIL -->
<div id="detailModal" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="bg-white w-full max-w-2xl m-auto rounded-3xl shadow-2xl overflow-hidden modal-anim flex flex-col max-h-[90vh] border-2 border-indigo-400">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 p-6 text-white shrink-0">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-black">Detail Transaksi</h2>
                    <p class="text-indigo-200 text-sm mt-1" id="modal-subtitle">—</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="printDetail()" class="bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="closeDetail()" class="text-white/80 hover:text-white text-3xl leading-none transition-all hover:scale-110 ml-2">×</button>
                </div>
            </div>
            <!-- Info strip -->
            <div class="flex gap-4 mt-4">
                <div class="bg-white/20 rounded-xl px-3 py-2 text-xs">
                    <span class="text-indigo-200">Kasir:</span>
                    <span class="font-bold ml-1" id="modal-kasir">—</span>
                </div>
                <div class="bg-white/20 rounded-xl px-3 py-2 text-xs">
                    <span class="text-indigo-200">Tanggal:</span>
                    <span class="font-bold ml-1" id="modal-tanggal">—</span>
                </div>
                <div class="bg-white/20 rounded-xl px-3 py-2 text-xs">
                    <span class="text-indigo-200">Total:</span>
                    <span class="font-black ml-1 text-white" id="modal-total">—</span>
                </div>
            </div>
        </div>
        <!-- Body -->
        <div class="overflow-y-auto grow">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Nama Obat</th>
                        <th class="px-5 py-3 text-center">Qty</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detail-body">
                    <tr><td colspan="3" class="px-5 py-10 text-center text-slate-400">Memuat data...</td></tr>
                </tbody>
                <tfoot id="detail-footer" class="hidden">
                    <tr class="border-t-2 border-slate-200 bg-emerald-50">
                        <td class="px-5 py-4 font-black text-slate-700" colspan="2">TOTAL PEMBAYARAN</td>
                        <td class="px-5 py-4 font-black text-emerald-600 text-right text-lg" id="detail-total-footer">—</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <!-- Footer -->
        <div class="p-5 border-t border-slate-100 bg-slate-50 shrink-0 flex justify-between items-center">
            <span class="text-xs text-slate-400">Data ditampilkan secara real-time dari sistem</span>
            <button onclick="closeDetail()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-5 py-2 rounded-xl font-bold transition-all text-sm">Tutup</button>
        </div>
    </div>
</div>

<!-- PRINT AREA (hidden, only for print) -->
<div id="printOverlay" style="display:none;">
    <div id="printArea"></div>
</div>

<script>
const API_BASE = '<?= $apiBaseUrl ?>';
let sortDir = {};
let currentPrintData = null;

function openDetailModal(id, kasir, tanggal, total) {
    document.getElementById('detailModal').classList.remove('hidden');
    document.getElementById('modal-subtitle').textContent = 'ID Riwayat: #' + id;
    document.getElementById('modal-kasir').textContent = kasir;
    document.getElementById('modal-tanggal').textContent = tanggal;
    document.getElementById('modal-total').textContent = 'Rp ' + parseInt(total).toLocaleString('id-ID');
    document.getElementById('detail-footer').classList.add('hidden');
    document.getElementById('detail-body').innerHTML = '<tr><td colspan="3" class="px-5 py-10 text-center text-slate-400"><i class="fas fa-hourglass-half"></i> Memuat...</td></tr>';

    fetch(`${API_BASE}/transaksi.php?id_riwayat=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status && res.data.length > 0) {
                let html = '', grandTotal = 0;
                res.data.forEach(item => {
                    grandTotal += parseInt(item.sub_total);
                    html += `<tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-5 py-3 font-semibold text-slate-700">${item.nama_obat || '(ID: '+item.id_obat+')'}</td>
                        <td class="px-5 py-3 text-center"><span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-lg text-xs font-bold">${item.jumlah}x</span></td>
                        <td class="px-5 py-3 text-right font-bold text-slate-700">Rp ${parseInt(item.sub_total).toLocaleString('id-ID')}</td>
                    </tr>`;
                });
                document.getElementById('detail-body').innerHTML = html;
                document.getElementById('detail-total-footer').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
                document.getElementById('detail-footer').classList.remove('hidden');
                currentPrintData = { kasir, tanggal, total, items: res.data };
            } else {
                document.getElementById('detail-body').innerHTML = '<tr><td colspan="3" class="px-5 py-10 text-center text-slate-400">Tidak ada item</td></tr>';
            }
        })
        .catch(() => {
            document.getElementById('detail-body').innerHTML = '<tr><td colspan="3" class="px-5 py-10 text-center text-red-400">Gagal memuat data</td></tr>';
        });
}

function closeDetail() { document.getElementById('detailModal').classList.add('hidden'); }

function printDetail() {
    if (!currentPrintData) return;
    const { kasir, tanggal, total, items } = currentPrintData;
    let rows = items.map(i => `<tr><td>${i.nama_obat||'ID:'+i.id_obat}</td><td style="text-align:center">${i.jumlah}x</td><td style="text-align:right">Rp ${parseInt(i.sub_total).toLocaleString('id-ID')}</td></tr>`).join('');
    document.getElementById('printArea').innerHTML = `
        <div style="font-family:monospace;max-width:320px;margin:auto;padding:20px;">
            <h2 style="text-align:center;font-size:18px;font-weight:900;border-bottom:2px dashed #000;padding-bottom:8px;margin-bottom:12px;"><i class="fas fa-hospital"></i> SIPOLA</h2>
            <div style="font-size:12px;margin-bottom:10px;">
                <div><b>Kasir:</b> ${kasir}</div>
                <div><b>Tanggal:</b> ${tanggal}</div>
            </div>
            <table style="width:100%;font-size:12px;border-collapse:collapse;">
                <thead><tr style="border-bottom:1px solid #000;"><th>Obat</th><th>Qty</th><th style="text-align:right">Harga</th></tr></thead>
                <tbody>${rows}</tbody>
                <tfoot><tr style="border-top:2px solid #000;font-weight:900;"><td colspan="2">TOTAL</td><td style="text-align:right">Rp ${parseInt(total).toLocaleString('id-ID')}</td></tr></tfoot>
            </table>
            <p style="text-align:center;font-size:10px;margin-top:16px;border-top:1px dashed #000;padding-top:8px;">Terima kasih telah berbelanja!</p>
        </div>`;
    document.getElementById('printOverlay').style.display = 'block';
    window.print();
    document.getElementById('printOverlay').style.display = 'none';
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
}

document.getElementById('detailModal').addEventListener('click', e => { if(e.target===document.getElementById('detailModal')) closeDetail(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') closeDetail(); });
</script>
</body>
</html>
