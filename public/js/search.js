const SEARCH_STATE = {
    IDLE: "IDLE",
    LOADING: "LOADING",
    SUCCESS: "SUCCESS",
    EMPTY: "EMPTY",
    ERROR: "ERROR"
};

let currentSearchState = SEARCH_STATE.IDLE;

async function searchObat() {

    const keyword =
        document.getElementById("searchInput").value;

    setSearchState(SEARCH_STATE.LOADING);

    try {

        const response = await fetch(
            `http://localhost:3000/api/obat/search?q=${keyword}`
        );

        const data = await response.json();

        if (data.length === 0) {

            setSearchState(SEARCH_STATE.EMPTY);

            document.getElementById("result").innerHTML =
                "Data tidak ditemukan";

            return;
        }

        setSearchState(SEARCH_STATE.SUCCESS);

        renderData(data);

    } catch (error) {

        setSearchState(SEARCH_STATE.ERROR);

        document.getElementById("result").innerHTML =
            "Terjadi kesalahan";
    }
}

function setSearchState(state) {

    currentSearchState = state;

    const status =
        document.getElementById("status");

    status.innerText = state;
}

function renderData(data) {

    const result =
        document.getElementById("result");

    result.innerHTML = "";

    data.forEach(obat => {

        result.innerHTML += `
            <div class="card">
                <h3>${obat.nama_obat}</h3>
                <p>Stok: ${obat.stok}</p>
            </div>
        `;
    });
}