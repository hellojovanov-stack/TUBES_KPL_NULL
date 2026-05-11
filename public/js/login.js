const LOGIN_STATE = {
    IDLE: "IDLE",
    LOADING: "LOADING",
    SUCCESS: "SUCCESS",
    ERROR: "ERROR"
};

let currentState = LOGIN_STATE.IDLE;

async function login() {

    setState(LOGIN_STATE.LOADING);

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    try {

        const response = await fetch("http://localhost:3000/api/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                username,
                password
            })
        });

        if (!response.ok) {
            throw new Error("Login gagal");
        }

        setState(LOGIN_STATE.SUCCESS);

        window.location.href = "dashboard.html";

    } catch (error) {

        setState(LOGIN_STATE.ERROR);

        document.getElementById("message").innerText =
            "Username atau password salah";
    }
}

function setState(newState) {

    currentState = newState;

    const button = document.getElementById("loginBtn");

    switch (newState) {

        case LOGIN_STATE.LOADING:
            button.innerText = "Loading...";
            button.disabled = true;
            break;

        case LOGIN_STATE.SUCCESS:
            button.innerText = "Berhasil";
            break;

        case LOGIN_STATE.ERROR:
            button.innerText = "Login";
            button.disabled = false;
            break;

        default:
            button.innerText = "Login";
    }
}