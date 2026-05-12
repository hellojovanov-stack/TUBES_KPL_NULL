const SEARCH_STATE = {
    IDLE: "IDLE",
    LOADING: "LOADING",
    SUCCESS: "SUCCESS",
    EMPTY: "EMPTY",
    ERROR: "ERROR"
};

let currentSearchState = SEARCH_STATE.IDLE;

async function searchObat() {
    const keyword = document.getElementById("searchInput").value.trim();

    if (!keyword) {
        setSearchState(SEARCH_STATE.EMPTY);
        renderEmpty();
        return;
    }

    setSearchState(SEARCH_STATE.LOADING);

    try {
        // PAKAI API BARU (bukan ke routes lagi)
        const response = await fetch(`../../backend/api/search.php?keyword=${encodeURIComponent(keyword)}`);
        const result = await response.json();

        if (!result.success || result.total === 0) {
            setSearchState(SEARCH_STATE.EMPTY);
            renderEmpty();
            return;
        }

        setSearchState(SEARCH_STATE.SUCCESS);
        renderData(result.data);

    } catch (error) {
        console.error("Search error:", error);
        setSearchState(SEARCH_STATE.ERROR);
        renderError();
    }
}

function setSearchState(state) {
    currentSearchState = state;
    const status = document.getElementById("searchStatus");
    if (status) {
        status.innerText = `STATE : ${state}`;
    }
    console.log(`STATE : ${state}`);
}

function renderData(data) {
    const result = document.getElementById("searchResult");
    if (!result) return;
    
    result.innerHTML = "";
    data.forEach(obat => {
        result.innerHTML += `
            <div class="bg-white rounded-2xl p-5 border border-slate-200">
                <h3 class="font-bold text-slate-800 text-lg">${obat.nama_obat}</h3>
                <p class="text-slate-500 text-sm mt-1">${obat.kategori}</p>
                <div class="mt-3 text-emerald-600 font-bold">Stok : ${obat.stok}</div>
                <div class="text-emerald-600">Rp ${obat.harga.toLocaleString('id-ID')}</div>
            </div>
        `;
    });
}

function renderEmpty() {
    const result = document.getElementById("searchResult");
    if (result) {
        result.innerHTML = `<div class="text-slate-400 text-center py-10">Data tidak ditemukan</div>`;
    }
}

function renderError() {
    const result = document.getElementById("searchResult");
    if (result) {
        result.innerHTML = `<div class="text-red-500 text-center py-10">Terjadi kesalahan sistem</div>`;
    }
}