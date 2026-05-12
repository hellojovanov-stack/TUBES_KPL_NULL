<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Apotek</title>

<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet" href="css/style.css">

</head>

<body class="bg-slate-100">

<nav class="navbar">

    <div class="brand">
        💊 Apotek Sehat
    </div>

    <div class="nav-links">

        <a href="index.php" class="active">
            Dashboard
        </a>

        <a href="#">
            Transaksi
        </a>

        <a href="#">
            Logout
        </a>

    </div>

</nav>

<div class="container">

    <div class="dashboard-header">

        <div>
            <h1 class="dashboard-title">
                Dashboard Inventaris
            </h1>

            <p class="dashboard-subtitle">
                Kelola stok obat modern
            </p>
        </div>

        <button onclick="openModal()" class="add-btn">
            + Tambah Obat
        </button>

    </div>

    <div class="result-grid">

        <?php foreach($data as $obat): ?>

            <div class="obat-card">

                <div class="obat-top">

                    <h3>
                        <?= $obat['nama_obat']; ?>
                    </h3>

                    <span class="kategori">
                        <?= $obat['kategori']; ?>
                    </span>

                </div>

                <div class="stok">
                    <?= $obat['stok']; ?>
                </div>

                <p class="harga">
                    Rp <?= number_format($obat['harga']); ?>
                </p>

                <div class="card-actions">

                    <a
                        href="index.php?action=hapus&id=<?= $obat['id']; ?>"
                        class="delete-btn"
                    >
                        Hapus
                    </a>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<!-- MODAL -->

<div id="modal" class="modal">

    <div class="modal-content">

        <h2>Tambah Obat</h2>

        <form method="POST" action="index.php?action=tambah">

            <input type="text" name="nama_obat" placeholder="Nama Obat" required>

            <input type="text" name="kategori" placeholder="Kategori">

            <input type="number" name="stok" placeholder="Stok" required>

            <input type="number" name="harga" placeholder="Harga" required>

            <button type="submit">
                Simpan
            </button>

        </form>

        <button onclick="closeModal()" class="close-btn">
            Tutup
        </button>

    </div>

</div>

<script src="js/modalFSM.js"></script>

</body>
</html>