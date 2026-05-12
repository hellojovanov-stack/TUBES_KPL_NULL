const TRANSACTION_STATE = {
    DRAFT: "DRAFT",
    PENDING: "PENDING",
    COMPLETED: "COMPLETED",
    CANCELLED: "CANCELLED"
};

let transactionState =
    TRANSACTION_STATE.DRAFT;

function checkout() {

    if (transactionState !== TRANSACTION_STATE.DRAFT) {
        alert("Checkout tidak valid");
        return;
    }

    transactionState =
        TRANSACTION_STATE.PENDING;

    renderTransactionState();
}

function pay() {

    if (transactionState !== TRANSACTION_STATE.PENDING) {
        alert("Pembayaran tidak valid");
        return;
    }

    transactionState =
        TRANSACTION_STATE.COMPLETED;

    renderTransactionState();
}

function cancelTransaction() {

    transactionState =
        TRANSACTION_STATE.CANCELLED;

    renderTransactionState();
}

function renderTransactionState() {

    document.getElementById("trxState").innerText =
        transactionState;
}