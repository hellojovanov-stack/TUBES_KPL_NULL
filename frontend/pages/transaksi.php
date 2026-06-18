<?php

session_start();

if (!isset($_SESSION['login'])) {

    header("Location: login.php");
    exit;
}

$apiBaseUrl = "http://localhost/TUBES_KPL_NULL/backend/api";

if (isset($_POST['tambah'])) {

    $id_obat = (int) $_POST['id_obat'];
    $jumlah  = (int) $_POST['jumlah'];

    $result = file_get_contents("$apiBaseUrl/obat.php?id=$id_obat");
    if ($result !== false) {
        $response = json_decode($result, true);
        if (isset($response['status']) && $response['status'] === true) {
            $obat = $response['data'];
            
            $subtotal = $obat['harga'] * $jumlah;

            $_SESSION['cart'][] = [
                "id"       => $obat['id'],
                "nama"     => $obat['nama_obat'],
                "harga"    => $obat['harga'],
                "jumlah"   => $jumlah,
                "subtotal" => $subtotal
            ];
            $_SESSION['toast'] = ['type' => 'success', 'msg' => "Ditambahkan ke keranjang"];
        }
    }

    header("Location: transaksi.php");
    exit;
}

if (isset($_POST['bayar'])) {

    if (!empty($_SESSION['cart'])) {

        $valid = true;
        $error_msg = '';
        foreach ($_SESSION['cart'] as $item) {
            $id = $item['id'];
            $jumlah = $item['jumlah'];
            $res_obat = file_get_contents("$apiBaseUrl/obat.php?id=$id");
            if ($res_obat !== false) {
                $resp_obat = json_decode($res_obat, true);
                if (isset($resp_obat['status']) && $resp_obat['status'] === true) {
                    if ($resp_obat['data']['stok'] < $jumlah) {
                        $valid = false;
                        $error_msg = 'Stok ' . $resp_obat['data']['nama_obat'] . ' tidak cukup! (Sisa: ' . $resp_obat['data']['stok'] . ')';
                        break;
                    }
                }
            }
        }

        if (!$valid) {
            $_SESSION['toast'] = ['type' => 'danger', 'msg' => $error_msg];
            header('Location: transaksi.php');
            exit;
        }

        $total_bayar = 0;
        $jumlah_item = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_bayar += $item['subtotal'];
            $jumlah_item += $item['jumlah'];
        }

        $kasir = $_SESSION['username'] ?? 'admin';
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => json_encode([
                    'total_bayar' => $total_bayar,
                    'jumlah_item' => $jumlah_item,
                    'kasir' => $kasir
                ])
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents("$apiBaseUrl/riwayat.php", false, $context);
        
        if ($result !== false) {
            $response = json_decode($result, true);
            if (isset($response['status']) && $response['status'] === true) {
                $id_riwayat = $response['id'];
 
                foreach ($_SESSION['cart'] as $item) {
                    $id = $item['id'];
                    $jumlah = $item['jumlah'];
                    
                    $res_obat = file_get_contents("$apiBaseUrl/obat.php?id=$id");
                    $resp_obat = json_decode($res_obat, true);
                    $obat = $resp_obat['data'];
                    
                    $stok_baru = $obat['stok'] - $jumlah;

                    $putOptions = [
                        'http' => [
                            'method'  => 'PUT',
                            'header'  => "Content-Type: application/json\r\n",
                            'content' => json_encode([
                                'id' => $id,
                                'nama_obat' => $obat['nama_obat'],
                                'kategori' => $obat['kategori'],
                                'id_kategori' => $obat['id_kategori'] ?? null,
                                'id_supplier' => $obat['id_supplier'] ?? null,
                                'stok' => $stok_baru,
                                'harga' => $obat['harga']
                            ])
                        ]
                    ];
                    $putContext = stream_context_create($putOptions);
                    file_get_contents("$apiBaseUrl/obat.php", false, $putContext);

                    $transOptions = [
                        'http' => [
                            'method'  => 'POST',
                            'header'  => "Content-Type: application/json\r\n",
                            'content' => json_encode([
                                'id_obat' => $id,
                                'jumlah' => $jumlah,
                                'sub_total' => $item['subtotal'],
                                'id_riwayat' => $id_riwayat
                            ])
                        ]
                    ];
                    $transContext = stream_context_create($transOptions);
                    file_get_contents("$apiBaseUrl/transaksi.php", false, $transContext);
                }
            }
        }

        unset($_SESSION['cart']);

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Pembayaran berhasil diselesaikan!'];
        header("Location: transaksi.php");
        exit;
    }
}


if (isset($_GET['batal'])) {

    unset($_SESSION['cart']);
    $_SESSION['toast'] = ['type' => 'info', 'msg' => 'Transaksi dibatalkan.'];

    header("Location: transaksi.php");
    exit;
}

$data_obat = [];
$res_all = file_get_contents("$apiBaseUrl/obat.php");
if ($res_all !== false) {
    $resp_all = json_decode($res_all, true);
    if (isset($resp_all['status']) && $resp_all['status'] === true) {
        $data_obat = $resp_all['data'];
        usort($data_obat, function($a, $b) {
            return strcmp($a['nama_obat'], $b['nama_obat']);
        });
    }
}

$total = 0;

if (!empty($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $item) {

        $total += $item['subtotal'];
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

    <title>E-Kasir | SIPOLA</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style1.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php if ($toast): ?>
<div id="toast" class="toast">
    <span><?= $toast['type']==='success'?'✅':($toast['type']==='danger'?'<i class="fas fa-exclamation-triangle text-red-500"></i>':'<i class="fas fa-info-circle text-indigo-500"></i>') ?></span>
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
                <h1>E-Kasir</h1>
                <p>Kelola transaksi penjualan dengan efisien</p>
            </div>
            <div class="header-actions">
                <div class="text-right">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">
                        Status Transaksi
                    </span>
                    <div class="px-6 py-2 rounded-2xl text-sm font-black uppercase tracking-wider <?= empty($_SESSION['cart']) ? 'bg-[var(--azure-mist)] text-[var(--muted-teal)] ring-4 ring-slate-50' : 'bg-[var(--mint-cream)] text-[var(--success)] ring-4 ring-emerald-50' ?>">
                        <?= empty($_SESSION['cart']) ? 'DRAFT' : 'ACTIVE'; ?>
                    </div>
                </div>
            </div>
        </header>

        <div class="grid lg:grid-cols-3 gap-6 animate-fade" style="animation-delay: 0.1s; animation-fill-mode: both;">

            <div class="lg:col-span-2 space-y-6">

                <div class="card p-6 border-t-4 border-[var(--jungle-teal)]">
                    <h2 class="text-lg font-bold text-slate-800 mb-4"><i class="fas fa-cart-plus text-[var(--jungle-teal)] mr-2"></i> Tambah Item</h2>
                    <form method="POST" class="grid md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <select name="id_obat" required class="form-control" style="width:100%;">
                                <option value="">-- Pilih Obat --</option>
                                <?php foreach($data_obat as $obat): ?>
                                    <option value="<?= $obat['id']; ?>" <?= $obat['stok'] == 0 ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($obat['nama_obat']); ?> - Rp <?= number_format($obat['harga'], 0, ',', '.'); ?> (Stok <?= $obat['stok']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <input type="number" name="jumlah" min="1" placeholder="Qty" required class="form-control" style="width:100%;">
                        </div>
                        <div class="md:col-span-1">
                            <button type="submit" name="tambah" class="btn btn-primary w-full h-full justify-center">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Keranjang Belanja</h2>
                    
                    <div class="space-y-4">
                        <?php if(!empty($_SESSION['cart'])): ?>
                            <?php foreach($_SESSION['cart'] as $index => $item): ?>
                                <div class="flex justify-between items-center p-4 rounded-2xl border border-slate-100 bg-[var(--mint-cream)] hover:shadow-md transition-all">
                                    <div class="flex gap-4 items-center">
                                        <div class="w-12 h-12 bg-white text-[var(--success)] rounded-xl shadow-sm flex items-center justify-center text-xl">
                                            <i class="fas fa-pills"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-800"><?= htmlspecialchars($item['nama']); ?></h3>
                                            <p class="text-sm text-slate-500">
                                                <span class="font-semibold text-slate-700"><?= $item['jumlah']; ?>x</span> @ Rp <?= number_format($item['harga'], 0, ',', '.'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="font-black text-[var(--jungle-teal)] text-lg">
                                        Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                <i class="fas fa-shopping-cart text-4xl text-slate-300 mb-3 block"></i>
                                <p class="text-slate-400 font-medium">Keranjang masih kosong.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="card p-6 bg-gradient-to-br from-[var(--jungle-teal)] to-[var(--primary-dark)] text-white shadow-xl sticky top-24">
                    <h2 class="text-lg font-bold text-emerald-100 mb-6 uppercase tracking-wider text-center">Ringkasan Pembayaran</h2>
                    
                    <div class="bg-white/10 rounded-2xl p-5 mb-6 backdrop-blur-sm border border-white/20 text-center">
                        <span class="text-emerald-100 text-sm font-medium block mb-2">Total Tagihan</span>
                        <h2 class="text-4xl font-black text-white drop-shadow-md">
                            Rp <?= number_format($total, 0, ',', '.'); ?>
                        </h2>
                    </div>

                    <div class="space-y-3">
                        <?php if(empty($_SESSION['cart'])): ?>
                            <button type="button" onclick="showWarning()" class="w-full py-4 rounded-xl font-black uppercase tracking-widest text-sm transition-all bg-white/20 text-emerald-100 cursor-not-allowed text-center">
                                <i class="fas fa-check-circle mr-2"></i> Bayar Lunas
                            </button>
                        <?php else: ?>
                            <form method="POST">
                                <button type="submit" name="bayar" class="w-full py-4 rounded-xl font-black uppercase tracking-widest text-sm transition-all bg-[var(--warning)] hover:brightness-110 text-[var(--primary-dark)] shadow-lg shadow-black/20 text-center">
                                    <i class="fas fa-money-bill-wave mr-2"></i> Bayar Lunas
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="<?= empty($_SESSION['cart']) ? '#' : 'transaksi.php?batal=1'; ?>" 
                           <?= empty($_SESSION['cart']) ? 'onclick="showWarning()"' : ''; ?>
                           class="block w-full text-center py-3 rounded-xl font-bold uppercase tracking-wider text-xs transition-all <?= empty($_SESSION['cart']) ? 'text-white/40 cursor-not-allowed' : 'bg-red-500/20 text-red-200 hover:bg-red-500 hover:text-white border border-red-500/30' ?>">
                            Batalkan
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>

<div id="warningBox" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 hidden bg-[var(--warning)] border border-[var(--warning)] text-[var(--dark-text)] px-6 py-4 rounded-2xl text-sm font-black shadow-2xl z-[100] animate-modal">
    <i class="fas fa-exclamation-triangle mr-2"></i> Belum ada item di keranjang transaksi.
</div>

<script>
function showWarning() {
    const box = document.getElementById('warningBox');
    box.classList.remove('hidden');
    box.style.display = 'flex';
    setTimeout(() => {
        box.classList.add('hidden');
        box.style.display = 'none';
    }, 3500);
}
</script>
</body>
</html>