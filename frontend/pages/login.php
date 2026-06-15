<?php
session_start();

$message = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['login_username'] ?? $_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $message = "Username dan password wajib diisi";
    } else {

        $apiUrl = "http://localhost/TUBES_KPL_NULL/backend/api/auth.php";
        $postData = json_encode(['username' => $username, 'password' => $password]);

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $postData,
                'ignore_errors' => true
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);

        if ($result === false) {
            $message = "Gagal menghubungi server API";
        } else {
            $response = json_decode($result, true);
            
            if (isset($response['status']) && $response['status'] === true) {
                $_SESSION['login'] = true;
                $_SESSION['username'] = $response['data']['username'];

                header("Location: dashboard.php");
                exit;
            } else {
                $message = $response['message'] ?? "Username atau password salah";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login SIPOLA</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="../css/style1.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-slate-50 text-slate-800">

<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-emerald-500 to-teal-700">

    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">

        <div class="text-center mb-8">

            <div class="relative bg-gradient-to-br from-emerald-500 to-teal-700 w-20 h-20 rounded-3xl shadow-[0_12px_24px_-8px_rgba(16,185,129,0.6)] flex items-center justify-center mx-auto mb-5 border border-white/20 overflow-hidden group transform hover:-translate-y-1 transition-all duration-300">
                <div class="absolute -top-6 -right-6 w-16 h-16 bg-white opacity-10 rounded-full blur-md group-hover:scale-125 transition-transform duration-700"></div>
                <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-teal-300 opacity-20 rounded-full blur-md group-hover:scale-125 transition-transform duration-700"></div>
                <i class="fa-solid fa-capsules text-white text-[36px] relative z-10 drop-shadow-lg transform group-hover:rotate-12 transition-transform duration-300"></i>
            </div>

            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
                Login
            </h1>

            <p class="text-slate-500 mt-2 font-medium leading-relaxed">
                SIPOLA - Sistem Informasi Pengelolaan<br>Obat dan Layanan Apotek
            </p>

        </div>

        <form method="POST">

            <div class="space-y-5">

                <div>

                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none transition-all"
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
                        name="password"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none transition-all"
                        placeholder="Masukkan password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    name="login"
                    id="loginBtn"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-200 active:scale-[0.98]"
                >
                    Masuk ke Sistem
                </button>

                <p class="text-center text-sm font-medium text-red-500 min-h-[1.5rem]">
                    <?= $message ?>
                </p>

            </div>

        </form>

    </div>

</div>

</body>
</html>