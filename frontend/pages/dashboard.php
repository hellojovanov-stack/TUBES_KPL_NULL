<?php

/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

session_start();

if (!isset($_SESSION['login'])) {

    header("Location: login.php");
    exit;
}

$apiBaseUrl = "http://localhost/TUBES_KPL_NULL/backend/api";

// Fetch Kategori Data
$data_kategori = [];
$res_kat = file_get_contents("$apiBaseUrl/kategori.php");
if ($res_kat !== false) {
    $resp_kat = json_decode($res_kat, true);
    if (isset($resp_kat['status']) && $resp_kat['status'] === true) {
        $data_kategori = $resp_kat['data'];
    }
}

// Fetch Supplier Data
$data_supplier = [];
$res_sup = file_get_contents("$apiBaseUrl/supplier.php");
if ($res_sup !== false) {
    $resp_sup = json_decode($res_sup, true);
    if (isset($resp_sup['status']) && $resp_sup['status'] === true) {
        $data_supplier = $resp_sup['data'];
    }
}

/*
|--------------------------------------------------------------------------
| TAMBAH OBAT
|--------------------------------------------------------------------------
*/

if (isset($_POST['tambah'])) {

    // Using cURL for file upload via API
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

/*
|--------------------------------------------------------------------------
| UPDATE OBAT (Add block for Update from Modal)
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {
    // If we wanted to process file upload in update, it gets tricky with PUT.
    // So we use POST to the obat.php API but pass `_method` (not natively supported by our simple API though)
    // Actually, our API accepts POST for PUT if we decode it, wait no, our API accepts PUT via file_get_contents
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

/*
|--------------------------------------------------------------------------
| HAPUS OBAT
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| SEARCH & FETCH DATA
|--------------------------------------------------------------------------
*/

$keyword = "";
$data = [];

// For now, since the API doesn't have a specific search endpoint yet, we fetch all and filter, or we just rely on standard GET
$result = file_get_contents("$apiBaseUrl/obat.php");
if ($result !== false) {
    $response = json_decode($result, true);
    if (isset($response['status']) && $response['status'] === true) {
        $allData = $response['data'];
        
        if (isset($_GET['search'])) {
            $keyword = strtolower(htmlspecialchars($_GET['keyword']));
            foreach($allData as $item) {
                if (strpos(strtolower($item['nama_obat']), $keyword) !== false) {
                    $data[] = $item;
                }
            }
        } else {
            $data = $allData;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
    
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

            <a href="dashboard.php"
               class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">
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
               class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
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

<main class="max-w-6xl mx-auto px-6 mt-10">

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 mb-10">

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">

            <div>

                <h2 class="text-2xl font-bold text-slate-800">
                    Inventaris Obat
                </h2>

                <p class="text-slate-500">
                    Cari dan kelola ketersediaan stok obat.
                </p>

            </div>

            <div
                class="mt-4 md:mt-0 px-4 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-[0.2em]"
            >
                <?= count($data); ?> DATA
            </div>

        </div>

        <form method="GET">

            <div class="flex flex-col md:flex-row gap-4">

                <div class="relative flex-1">

                    <input
                        type="text"
                        name="keyword"
                        value="<?= $keyword ?>"
                        class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none transition-all"
                        placeholder="Masukkan nama obat..."
                    >

                </div>

                <button
                    type="submit"
                    name="search"
                    class="bg-emerald-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-emerald-700 transition-all"
                >
                    Cari Obat
                </button>

                <button
                    type="button"
                    onclick="openModal()"
                    class="bg-indigo-600 text-white px-10 py-3.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all"
                >
                    + Tambah
                </button>

            </div>

        </form>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <?php if(count($data) > 0): ?>

            <?php foreach($data as $obat): ?>

                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-lg transition-all">
                        <?php if(!empty($obat['gambar'])): ?>

                            <img
                                src="../uploads/<?= $obat['gambar']; ?>"
                                class="w-full h-48 object-cover rounded-2xl mb-5"
                            >

                        <?php endif; ?>
                    <div class="flex justify-between items-start mb-5">

                        <div>

                            <h3 class="text-xl font-bold text-slate-800">
                                <?= $obat['nama_obat']; ?>
                            </h3>

                            <p class="text-slate-400 text-sm mt-1">
                                <?= $obat['kategori']; ?>
                            </p>

                        </div>

                        <div class="bg-emerald-100 text-emerald-700 px-4 py-1 rounded-full text-xs font-black uppercase">
                            Stok <?= $obat['stok']; ?>
                        </div>

                    </div>

                    <div class="mb-6">

                        <p class="text-slate-400 text-sm mb-1">
                            Harga
                        </p>

                        <h2 class="text-3xl font-black text-emerald-600">
                            Rp <?= number_format($obat['harga']); ?>
                        </h2>

                    </div>

                    <div class="flex gap-3">

                        <button
    onclick="openEditModal(
        '<?= $obat['id']; ?>',
        '<?= addslashes($obat['nama_obat']); ?>',
        '<?= $obat['id_kategori'] ?? ''; ?>',
        '<?= $obat['id_supplier'] ?? ''; ?>',
        '<?= $obat['stok']; ?>',
        '<?= $obat['harga']; ?>'
    )"
    class="flex-1 text-center bg-indigo-50 text-indigo-600 py-3 rounded-2xl font-bold hover:bg-indigo-100 transition-all"
>
    Edit
</button>

                        <a
                            href="dashboard.php?hapus=<?= $obat['id']; ?>"
                            onclick="return confirm('Yakin hapus obat?')"
                            class="flex-1 text-center bg-red-50 text-red-600 py-3 rounded-2xl font-bold hover:bg-red-100 transition-all"
                        >
                            Hapus
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-span-full flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">

                <div class="bg-slate-50 p-6 rounded-full mb-4">
                    <i class="fas fa-pills"></i>
                </div>

                <p class="text-slate-400 font-medium text-lg">
                    Data obat tidak ditemukan.
                </p>

            </div>

        <?php endif; ?>

    </div>

</main>

<!-- MODAL -->

<div
    id="modal"
   class="fixed inset-0 hidden z-50 flex items-center justify-center min-h-screen px-4 bg-black/40 backdrop-blur-sm animate-fade"
>

    <div class="bg-white w-full max-w-2xl m-auto rounded-3xl shadow-2xl overflow-hidden scale-100 animate-modal flex flex-col max-h-[90vh]">

        <!-- HEADER -->

        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white shrink-0">

            <div class="flex justify-between items-center">

                <div>

                    <h2 class="text-2xl font-black">
                        Tambah Obat
                    </h2>

                    <p class="text-emerald-100 text-sm mt-1">
                        Tambahkan data obat baru
                    </p>

                </div>

                <button
                    onclick="closeModal()"
                    class="text-white text-3xl leading-none hover:scale-110 transition-all"
                >
                    ×
                </button>

            </div>

        </div>

        

        <!-- FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
            class="p-6 space-y-5 overflow-y-auto grow"
        >

            <!-- FOTO -->

            <div>

                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Gambar Obat
                </label>

                <input
                    type="file"
                    name="gambar"
                    accept="image/*"
                    class="w-full text-sm text-slate-500
                    file:mr-4
                    file:px-4
                    file:py-2
                    file:rounded-xl
                    file:border-0
                    file:bg-emerald-600
                    file:text-white
                    file:font-bold
                    hover:file:bg-emerald-700"
                >

            </div>

            <!-- NAMA -->

            <div>

                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Nama Obat
                </label>

                <input
                    type="text"
                    name="nama_obat"
                    placeholder="Masukkan nama obat"
                    required
                    class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"
                >

            </div>

            <!-- KATEGORI -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Kategori Database</label>
                <select name="id_kategori" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($data_kategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SUPPLIER -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Supplier</label>
                <select name="id_supplier" class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach($data_supplier as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['nama_supplier']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- GRID -->

            <div class="grid grid-cols-2 gap-4">

                <div>

                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Stok
                    </label>

                    <input
                        type="number"
                        name="stok"
                        placeholder="0"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"
                    >

                </div>

                <div>

                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Harga
                    </label>

                    <input
                        type="number"
                        name="harga"
                        placeholder="5000"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"
                    >

                </div>

            </div>

            <!-- BUTTON -->

            <button
                type="submit"
                name="tambah"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold transition-all shadow-lg shadow-emerald-100"
            >
                Simpan Obat
            </button>

        </form>

    </div>

</div>
<!-- MODAL EDIT -->

<div
    id="editModal"
    class="fixed inset-0 hidden z-50 flex items-center justify-center min-h-screen px-4 bg-black/40 backdrop-blur-sm animate-fade"
>

    <div class="bg-white w-full max-w-2xl m-auto rounded-3xl shadow-2xl overflow-hidden animate-modal flex flex-col max-h-[90vh]">

        <!-- HEADER -->

        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-white shrink-0">

            <div class="flex justify-between items-center">

                <div>

                    <h2 class="text-2xl font-black">
                        Edit Obat
                    </h2>

                    <p class="text-emerald-100 text-sm mt-1">
                        Ubah data obat
                    </p>

                </div>

                <button
                    onclick="closeEditModal()"
                    class="text-white text-3xl leading-none hover:scale-110 transition-all"
                >
                    ×
                </button>

            </div>

        </div>

        <!-- FORM -->

        <form
            method="POST"
            class="p-6 space-y-5 overflow-y-auto grow"
        >

            <input type="hidden" name="id" id="edit_id">

            <div>

                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Nama Obat
                </label>

                <input
                    type="text"
                    name="nama_obat"
                    id="edit_nama"
                    required
                    class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"
                >

            </div>

            <!-- KATEGORI -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Kategori Database</label>
                <select name="id_kategori" id="edit_id_kategori" required class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($data_kategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SUPPLIER -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Supplier</label>
                <select name="id_supplier" id="edit_id_supplier" class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach($data_supplier as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['nama_supplier']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">

                <div>

                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Stok
                    </label>

                    <input
                        type="number"
                        name="stok"
                        id="edit_stok"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"
                    >

                </div>

                <div>

                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Harga
                    </label>

                    <input
                        type="number"
                        name="harga"
                        id="edit_harga"
                        required
                        class="w-full px-4 py-3 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500"
                    >

                </div>

            </div>

            <button
                type="submit"
                name="update"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-2xl font-bold transition-all shadow-lg shadow-emerald-100"
            >
                Update Obat
            </button>

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

    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {

    document.getElementById('editModal').classList.add('hidden');
}

</script>
</body>
</html>