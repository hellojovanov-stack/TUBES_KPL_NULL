const MODAL_STATE = {
    OPEN: "OPEN",
    CLOSED: "CLOSED"
};

let modalState = MODAL_STATE.CLOSED;

function openModal() {
    modalState = MODAL_STATE.OPEN;
    let m = document.getElementById("modal") || document.getElementById("formModal");
    if (m) m.classList.add('active');
}

function closeModal() {
    modalState = MODAL_STATE.CLOSED;
    let m = document.getElementById("modal") || document.getElementById("formModal");
    if (m) m.classList.remove('active');
}