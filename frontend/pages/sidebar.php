<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
  
    <div class="sidebar-logo p-6 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-white shadow-inner backdrop-blur-sm border border-white/30">
                <i class="fa-solid fa-capsules text-lg"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-white font-black text-xl tracking-wide">SIPOLA</span>
                <span class="text-[10px] text-[var(--mint-cream)] uppercase font-semibold opacity-80">Manajemen Apotek</span>
            </div>
        </div>
      
        <button class="md:hidden text-white/70 hover:text-white" onclick="toggleSidebar()">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="px-6 py-5 border-b border-white/10 flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-[var(--warning-color)] flex items-center justify-center text-[var(--primary-dark)] font-bold shadow-md">
            <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="flex flex-col flex-1 min-w-0">
            <span class="text-white font-bold text-sm truncate"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin Apotek') ?></span>
            <span class="text-white text-xs truncate">Administrator</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar">
        <p class="px-3 text-xs font-bold text-white/50 uppercase tracking-widest mb-3 mt-2">Menu Utama</p>
        
        <a href="dashboard.php" class="sidebar-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home w-5"></i>
            <span>Dashboard</span>
        </a>

        <a href="kategori.php" class="sidebar-link <?= $currentPage == 'kategori.php' ? 'active' : '' ?>">
            <i class="fas fa-tags w-5"></i>
            <span>Kategori Obat</span>
        </a>

        <a href="supplier.php" class="sidebar-link <?= $currentPage == 'supplier.php' ? 'active' : '' ?>">
            <i class="fas fa-truck-medical w-5"></i>
            <span>Supplier</span>
        </a>

        <p class="px-3 text-xs font-bold text-white/50 uppercase tracking-widest mb-3 mt-6 pt-4 border-t border-white/10">Penjualan</p>

        <a href="transaksi.php" class="sidebar-link <?= $currentPage == 'transaksi.php' ? 'active' : '' ?>">
            <i class="fas fa-cash-register w-5"></i>
            <span>E-Kasir</span>
        </a>

        <a href="riwayat.php" class="sidebar-link <?= $currentPage == 'riwayat.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice w-5"></i>
            <span>Riwayat Transaksi</span>
        </a>
    </nav>

    <div class="p-4 border-t border-white/10">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-white/10 rounded-xl transition-all font-semibold" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<button class="md:hidden fixed bottom-6 right-6 z-40 w-14 h-14 bg-[var(--jungle-teal)] text-white rounded-full shadow-[var(--shadow-lg)] flex items-center justify-center text-xl hover:scale-105 transition-transform" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    

    if (sidebar.style.transform === 'translateX(0px)') {
        sidebar.style.transform = ''; 
        overlay.classList.remove('active');
    } else {
        sidebar.style.transform = 'translateX(0px)';
        overlay.classList.add('active');
    }
}

window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) { 
        document.getElementById('sidebar').style.transform = '';
        document.getElementById('sidebarOverlay').classList.remove('active');
    }
});
</script>
