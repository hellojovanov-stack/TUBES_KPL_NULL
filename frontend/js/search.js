const SEARCH_STATE = {

    IDLE: "IDLE",
    LOADING: "LOADING",
    SUCCESS: "SUCCESS",
    EMPTY: "EMPTY",
    ERROR: "ERROR"
};

let currentSearchState =
    SEARCH_STATE.IDLE;

/*
|--------------------------------------------------------------------------
| SEARCH OBAT
|--------------------------------------------------------------------------
*/

async function searchObat() {

    const keyword =
        document.getElementById("searchInput")
        .value
        .trim();

    if (!keyword) {

        setSearchState(
            SEARCH_STATE.EMPTY
        );

        renderEmpty();

        return;
    }

    setSearchState(
        SEARCH_STATE.LOADING
    );

    try {

        const response = await fetch(
            `../../backend/routes/obat.php?action=search&keyword=${encodeURIComponent(keyword)}`
        );

        const data =
            await response.json();

        if (data.length === 0) {

            setSearchState(
                SEARCH_STATE.EMPTY
            );

            renderEmpty();

            return;
        }

        setSearchState(
            SEARCH_STATE.SUCCESS
        );

        renderData(data);

    } catch (error) {

        setSearchState(
            SEARCH_STATE.ERROR
        );

        renderError();
    }
}

/*
|--------------------------------------------------------------------------
| FSM STATE
|--------------------------------------------------------------------------
*/

function setSearchState(state) {

    currentSearchState = state;

    const status =
        document.getElementById("status");

    status.innerText =
        `STATE : ${state}`;
}

/*
|--------------------------------------------------------------------------
| RENDER DATA
|--------------------------------------------------------------------------
*/

function renderData(data) {

    const result =
        document.getElementById("result");

    result.innerHTML = "";

    data.forEach(obat => {

        result.innerHTML += `

            <div class="bg-white rounded-2xl p-5 border border-slate-200">

                <h3 class="font-bold text-slate-800 text-lg">
                    ${obat.nama_obat}
                </h3>

                <p class="text-slate-500 text-sm mt-1">
                    ${obat.kategori}
                </p>

                <div class="mt-3 text-emerald-600 font-bold">
                    Stok : ${obat.stok}
                </div>

            </div>
        `;
    });
}

/*
|--------------------------------------------------------------------------
| EMPTY
|--------------------------------------------------------------------------
*/

function renderEmpty() {

    document.getElementById("result")
        .innerHTML =
        `
        <div class="text-slate-400 text-center py-10">
            Data tidak ditemukan
        </div>
        `;
}

/*
|--------------------------------------------------------------------------
| ERROR
|--------------------------------------------------------------------------
*/

function renderError() {

    document.getElementById("result")
        .innerHTML =
        `
        <div class="text-red-500 text-center py-10">
            Terjadi kesalahan sistem
        </div>
        `;
}