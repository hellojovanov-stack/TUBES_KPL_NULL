function handleLogin(event) {
    event.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    fetch("../../backend/routes/auth.php?action=login", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            window.location.href = "../pages/dashboard.php";
        } else {
            document.getElementById("message").innerText = data.message;
        }

    })
    .catch(err => {
        console.log(err);
        document.getElementById("message").innerText = "Server error";
    });
}