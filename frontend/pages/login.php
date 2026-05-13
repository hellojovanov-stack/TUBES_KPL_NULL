<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['login'])) {

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Apotek</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 text-slate-800">

<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-emerald-500 to-teal-700">

    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">

        <div class="text-center mb-8">

            <div class="bg-emerald-100 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 rotate-3">

                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.282a2 2 0 01-1.806 0l-.628-.282a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.311.467a2 2 0 001.664 3.108h15.428a2 2 0 001.664-3.108l-.311-.467zM8 10V7a4 4 0 118 0v3M8 9h8" />

                </svg>

            </div>

            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
                Login
            </h1>

            <p class="text-slate-500 mt-2">
                Apotek Sehat - Management System
            </p>

        </div>

        <form onsubmit="handleLogin(event)">

    <div class="space-y-5">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Username
            </label>

            <input
                type="text"
                id="username"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl"
                placeholder="Masukkan username"
                required
            >
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Password
            </label>

            <input
                type="password"
                id="password"
                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl"
                placeholder="Masukkan password"
                required
            >
        </div>

        <button
            type="submit"
            id="loginBtn"
            class="w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl"
        >
            Masuk ke Sistem
        </button>

        <p
            id="message"
            class="text-center text-sm font-medium text-red-500"
        ></p>

    </div>

</form>

    </div>

</div>
<script src="../js/login.js"></script>
</body>
</html>