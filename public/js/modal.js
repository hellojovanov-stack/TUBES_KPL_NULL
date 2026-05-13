const MODAL_STATE = {
    OPEN: "OPEN",
    CLOSED: "CLOSED"
};

let modalState = MODAL_STATE.CLOSED;

function openModal() {

    modalState = MODAL_STATE.OPEN;

    document.getElementById("modal")
        .style.display = "block";
}

function closeModal() {

    modalState = MODAL_STATE.CLOSED;

    document.getElementById("modal")
        .style.display = "none";
}