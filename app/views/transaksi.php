<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

            <a href="dashboard.html"
               class="text-slate-500 hover:text-emerald-600 font-medium transition-colors">
                Dashboard
            </a>

            <a href="transaksi.html"
               class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-1">
                Transaksi
            </a>

        </div>

    </div>

</nav>

<main class="max-w-4xl mx-auto px-6 mt-10">

    <div class="bg-white p-10 rounded-3xl shadow-xl border border-slate-200">

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
                    id="trxState"
                    class="px-6 py-2 rounded-2xl text-sm font-black uppercase tracking-wider bg-slate-100 text-slate-600 ring-4 ring-slate-50 transition-all"
                >
                    DRAFT
                </div>

            </div>

        </div>

        <div class="space-y-6 mb-10">

            <div class="flex justify-between items-center p-4 rounded-2xl bg-slate-50 border border-slate-100">

                <div class="flex gap-4 items-center">

                    <div class="bg-white p-3 rounded-xl shadow-sm">
                        💊
                    </div>

                    <div>

                        <h3 class="font-bold text-slate-800">
                            Paracetamol 500mg
                        </h3>

                        <p class="text-sm text-slate-500">
                            2 Strip @ Rp 15.000
                        </p>

                    </div>

                </div>

                <span class="font-bold text-slate-700">
                    Rp 30.000
                </span>

            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <button
                onclick="checkout()"
                class="bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 active:scale-[0.98]"
            >
                Checkout
            </button>

            <button
                onclick="pay()"
                class="bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 active:scale-[0.98]"
            >
                Bayar Lunas
            </button>

            <button
                onclick="cancelTransaction()"
                class="bg-red-50 text-red-600 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-red-100 transition-all active:scale-[0.98]"
            >
                Batalkan
            </button>

        </div>

    </div>

</main>

<script src="../js/transaction.js"></script>

</body>
</html>