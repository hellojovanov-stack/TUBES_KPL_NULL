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
            echo "<script>alert('$error_msg'); window.location='transaksi.php';</script>";
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

        echo "
        <script>
            alert('Pembayaran berhasil!');
            window.location='transaksi.php';
        </script>
        ";
    }
}


if (isset($_GET['batal'])) {

    unset($_SESSION['cart']);

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

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transaksi</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style.css">

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

            <a href="dashboard.php"
               class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Dashboard
            </a>

            <a href="kategori.php"
               class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Kategori
            </a>

            <a href="supplier.php"
               class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Supplier
            </a>

            <a href="transaksi.php"
                class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">
                Transaksi
            </a>

            <a href="riwayat.php"
               class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Riwayat
            </a>

            <a href="logout.php"
               class="text-slate-500 hover:text-red-500 font-medium transition-colors">
                Logout
            </a>

        </div>

    </div>

</nav>
<!-- CONTENT -->

<main class="max-w-5xl mx-auto px-6 mt-10">

    <div class="bg-white p-10 rounded-3xl shadow-xl border border-slate-200">

        <!-- HEADER -->

        <div class="flex justify-between items-center mb-10 pb-6 border-b border-slate-100">

            <div>

                <h1 class="text-3xl font-black text-slate-800">
                    E-Kasir
                </h1>

                <p class="text-slate-400 mt-1">
                    Kelola transaksi pelanggan dengan efisien.
                </p>

            </div>

            <div class="text-right">

                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">
                    Status Transaksi
                </span>

                <div
                    class="px-6 py-2 rounded-2xl text-sm font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 ring-4 ring-emerald-50"
                >
                    <?= empty($_SESSION['cart']) ? 'DRAFT' : 'ACTIVE'; ?>
                </div>

            </div>

        </div>

        <!-- FORM TAMBAH -->

        <form method="POST" class="grid md:grid-cols-3 gap-4 mb-8">

            <select
                name="id_obat"
                required
                class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none"
            >

                <option value="">
                    Pilih Obat
                </option>

                <?php foreach($data_obat as $obat): ?>

                    <option value="<?= $obat['id']; ?>">

                        <?= $obat['nama_obat']; ?>
                        -
                        Stok <?= $obat['stok']; ?>
                        -
                        Rp <?= number_format($obat['harga']); ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <input
                type="number"
                name="jumlah"
                min="1"
                placeholder="Jumlah"
                required
                class="px-4 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none"
            >

            <button
                type="submit"
                name="tambah"
                class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black transition-all"
            >
                + Tambah
            </button>

        </form>

        <!-- LIST TRANSAKSI -->

        <div class="space-y-5 mb-10">

            <?php if(!empty($_SESSION['cart'])): ?>

                <?php foreach($_SESSION['cart'] as $item): ?>

                    <div class="flex justify-between items-center p-4 rounded-2xl bg-slate-50 border border-slate-100">

                        <div class="flex gap-4 items-center">

                            <div class="bg-white p-3 rounded-xl shadow-sm">
                                <i class="fas fa-pills"></i>
                            </div>

                            <div>

                                <h3 class="font-bold text-slate-800">
                                    <?= $item['nama']; ?>
                                </h3>

                                <p class="text-sm text-slate-500">
                                    <?= $item['jumlah']; ?> x
                                    Rp <?= number_format($item['harga']); ?>
                                </p>

                            </div>

                        </div>

                        <span class="font-bold text-slate-700">
                            Rp <?= number_format($item['subtotal']); ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="text-center py-16 bg-slate-50 rounded-3xl">

                    <p class="text-slate-400 font-medium">
                        Belum ada transaksi.
                    </p>

                </div>

            <?php endif; ?>

        </div>

        <!-- TOTAL -->

        <div class="bg-slate-50 rounded-3xl p-6 mb-8 border border-slate-200">

            <div class="flex justify-between items-center">

                <span class="text-slate-500 font-medium">
                    Total Pembayaran
                </span>

                <h2 class="text-4xl font-black text-emerald-600">
                    Rp <?= number_format($total); ?>
                </h2>

            </div>

        </div>

        <!-- BUTTON -->

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <!-- BAYAR -->

   <!-- BAYAR -->

<?php if(empty($_SESSION['cart'])): ?>

    <button
        type="button"
        onclick="showWarning()"
        class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all
        bg-slate-200 text-slate-400 cursor-not-allowed"
    >
        Bayar Lunas
    </button>

<?php else: ?>

    <form method="POST">

        <button
            type="submit"
            name="bayar"
            class="w-full py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all
            bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-100"
        >
            Bayar Lunas
        </button>

    </form>

<?php endif; ?>

    <!-- BATAL -->

    <a
        href="<?= empty($_SESSION['cart']) ? '#' : 'transaksi.php?batal=1'; ?>"

        <?= empty($_SESSION['cart'])
            ? 'onclick="showWarning()"'
            : ''; ?>

        class="w-full flex items-center justify-center py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all

        <?= empty($_SESSION['cart'])
            ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
            : 'bg-red-50 text-red-600 hover:bg-red-100'; ?>
        "
    >
        Batalkan
    </a>

</div>

<!-- WARNING -->

<div
    id="warningBox"
    class="hidden mt-5 bg-amber-50 border border-amber-200 text-amber-700 px-5 py-4 rounded-2xl text-sm font-medium"
>
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