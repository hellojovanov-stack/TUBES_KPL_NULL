const TRANSACTION_STATE = {

    DRAFT: "DRAFT",
    PENDING: "PENDING",
    COMPLETED: "COMPLETED",
    CANCELLED: "CANCELLED"
};

let transactionState =
    TRANSACTION_STATE.DRAFT;

/*
|--------------------------------------------------------------------------
| ADD TO CART
|--------------------------------------------------------------------------
*/

function addToCart() {

    if (
        transactionState ===
        TRANSACTION_STATE.COMPLETED
    ) {

        alert("Transaksi sudah selesai");

        return;
    }

    transactionState =
        TRANSACTION_STATE.PENDING;

    renderTransactionState();
}

/*
|--------------------------------------------------------------------------
| CHECKOUT
|--------------------------------------------------------------------------
*/

async function pay() {

    if (
        transactionState !==
        TRANSACTION_STATE.PENDING
    ) {

        alert(
            "Pembayaran tidak valid"
        );

        return;
    }

    try {

        const response = await fetch(
            "../../backend/routes/transaksi.php?action=bayar",
            {
                method: "POST"
            }
        );

        const data =
            await response.json();

        if (!data.success) {

            throw new Error(
                data.message
            );
        }

        transactionState =
            TRANSACTION_STATE.COMPLETED;

        renderTransactionState();

        alert(data.message);

        window.location.reload();

    } catch (error) {

        alert(error.message);
    }
}

/*
|--------------------------------------------------------------------------
| CANCEL
|--------------------------------------------------------------------------
*/

async function cancelTransaction() {

    await fetch(
        "../../backend/routes/transaksi.php?action=batal"
    );

    transactionState =
        TRANSACTION_STATE.CANCELLED;

    renderTransactionState();

    window.location.reload();
}

/*
|--------------------------------------------------------------------------
| RENDER
|--------------------------------------------------------------------------
*/

function renderTransactionState() {

    const stateBox =
        document.getElementById("trxState");

    if (!stateBox) return;

    stateBox.innerText =
        transactionState;
}