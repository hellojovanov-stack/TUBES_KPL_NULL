const TRANSACTION_STATE = {
    DRAFT: "DRAFT",
    PENDING: "PENDING",
    COMPLETED: "COMPLETED",
    CANCELLED: "CANCELLED"
};

let transactionState = TRANSACTION_STATE.DRAFT;

// Load cart saat halaman dimuat
async function loadCart() {
    try {
        const response = await fetch('../../backend/api/transaction.php?action=cart');
        const result = await response.json();
        
        if (result.success) {
            transactionState = result.transaction_state === 'PENDING' ? TRANSACTION_STATE.PENDING : TRANSACTION_STATE.DRAFT;
            renderTransactionState();
            renderCart(result.cart, result.total);
        }
    } catch (error) {
        console.error("Error loading cart:", error);
    }
}

async function addToCart() {
    if (transactionState === TRANSACTION_STATE.COMPLETED) {
        alert("Transaksi sudah selesai");
        return;
    }

    const id_obat = document.getElementById("id_obat").value;
    const jumlah = document.getElementById("jumlah").value;

    if (!id_obat || !jumlah) {
        alert("Pilih obat dan masukkan jumlah");
        return;
    }

    try {
        const formData = new URLSearchParams();
        formData.append('id_obat', id_obat);
        formData.append('jumlah', jumlah);

        const response = await fetch('../../backend/api/transaction.php?action=add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            transactionState = TRANSACTION_STATE.PENDING;
            renderTransactionState();
            renderCart(result.cart, result.total);
            alert(result.message);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error("Error adding to cart:", error);
        alert("Gagal menambahkan ke keranjang");
    }
}

async function pay() {
    if (transactionState !== TRANSACTION_STATE.PENDING) {
        alert("Tidak ada transaksi yang bisa dibayar");
        return;
    }

    try {
        const response = await fetch('../../backend/api/transaction.php?action=checkout', {
            method: 'POST'
        });
        const result = await response.json();
        
        if (result.success) {
            transactionState = TRANSACTION_STATE.COMPLETED;
            renderTransactionState();
            alert(result.message);
            loadCart(); // Reload cart
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error("Error during payment:", error);
        alert("Gagal melakukan pembayaran");
    }
}

async function cancelTransaction() {
    if (transactionState !== TRANSACTION_STATE.PENDING) {
        alert("Tidak ada transaksi yang bisa dibatalkan");
        return;
    }

    try {
        const response = await fetch('../../backend/api/transaction.php?action=clear');
        const result = await response.json();
        
        if (result.success) {
            transactionState = TRANSACTION_STATE.CANCELLED;
            renderTransactionState();
            renderCart([], 0);
            alert("Transaksi dibatalkan");
            setTimeout(() => {
                transactionState = TRANSACTION_STATE.DRAFT;
                renderTransactionState();
            }, 2000);
        }
    } catch (error) {
        console.error("Error cancelling transaction:", error);
        alert("Gagal membatalkan transaksi");
    }
}

function renderCart(cart, total) {
    const cartContainer = document.getElementById("cartItems");
    const totalContainer = document.getElementById("totalAmount");
    
    if (!cartContainer) return;
    
    if (!cart || cart.length === 0) {
        cartContainer.innerHTML = `<div class="text-center py-10 text-slate-400">Keranjang kosong</div>`;
        if (totalContainer) totalContainer.innerText = "Rp 0";
        return;
    }
    
    let html = '';
    cart.forEach(item => {
        html += `
            <div class="flex justify-between items-center p-4 border-b">
                <div>
                    <h4 class="font-bold">${item.nama}</h4>
                    <p class="text-sm text-slate-500">${item.jumlah} x Rp ${item.harga.toLocaleString('id-ID')}</p>
                </div>
                <div class="font-bold">Rp ${item.subtotal.toLocaleString('id-ID')}</div>
            </div>
        `;
    });
    
    cartContainer.innerHTML = html;
    if (totalContainer) totalContainer.innerText = `Rp ${total.toLocaleString('id-ID')}`;
}

function renderTransactionState() {
    const stateElement = document.getElementById("transactionState");
    if (stateElement) {
        stateElement.innerText = transactionState;
        stateElement.className = `px-4 py-2 rounded-full text-sm font-bold ${
            transactionState === 'DRAFT' ? 'bg-gray-200 text-gray-600' :
            transactionState === 'PENDING' ? 'bg-yellow-100 text-yellow-700' :
            transactionState === 'COMPLETED' ? 'bg-green-100 text-green-700' :
            'bg-red-100 text-red-700'
        }`;
    }
}

// Load cart saat halaman dimuat
document.addEventListener('DOMContentLoaded', loadCart);