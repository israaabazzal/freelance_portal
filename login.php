<?php
// If already logged in, redirect away
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'freelancer') header('Location: dashboard.php');
    else header('Location: client-view.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance Portal — Login</title>
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

    <video class="bg-video" autoplay muted loop playsinline>
        <source src="images/bg-video.mp4" type="video/mp4">
    </video>

    <div class="login-wrapper">

        <!-- Logo -->

    <div class="login-logo">
        
         <p class="subtitle2">Your Creative Workspace</p>

        <h1 class="logo-text">Freelancer<span> Portal<span></h1>
         <div class="logo-wrap">
        <img src="images/logo.png" alt="logo" class="logo-img">
        </div>
    </div>

        <!-- Card -->
        <div class="login-card glass">
            <h2>Welcome back </h2>
            

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="you@portal.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input type="password" id="password" placeholder="••••••••" autocomplete="current-password">
                    <button type="button" class="show-pass" id="togglePass">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="error-msg" id="errorMsg"></div>

            <button class="btn-login" id="loginBtn">
                <span id="btnText">Log in</span>
                <span id="btnLoader" class="loader hidden"></span>
            </button>
        </div>

    </div>

    <script>
        // Toggle password visibility
        const togglePass = document.getElementById('togglePass');
        const passInput  = document.getElementById('password');
        togglePass.addEventListener('click', () => {
            const isText = passInput.type === 'text';
            passInput.type = isText ? 'password' : 'text';
            document.getElementById('eyeIcon').style.opacity = isText ? '1' : '0.4';
        });

        // Login
        document.getElementById('loginBtn').addEventListener('click', async () => {
            const email    = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const errorMsg = document.getElementById('errorMsg');
            const btnText  = document.getElementById('btnText');
            const btnLoader= document.getElementById('btnLoader');

            errorMsg.textContent = '';

            if (!email || !password) {
                errorMsg.textContent = 'Please fill in all fields.';
                return;
            }

            // Show loader
            btnText.classList.add('hidden');
            btnLoader.classList.remove('hidden');

            try {
                const res  = await fetch('api/login.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ email, password })
                });
                const data = await res.json();

                if (data.success) {
                    if (data.role === 'freelancer') window.location.href = 'dashboard.php';
                    else window.location.href = 'client-view.php';
                } else {
                    errorMsg.textContent = data.error || 'Something went wrong.';
                    btnText.classList.remove('hidden');
                    btnLoader.classList.add('hidden');
                }
            } catch (e) {
                errorMsg.textContent = 'Could not connect. Is XAMPP running?';
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
            }
        });

        // Allow Enter key to submit
        document.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('loginBtn').click();
        });
    </script>

</body>
</html>