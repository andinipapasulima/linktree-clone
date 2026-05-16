<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi
    if (strlen($username) < 3) {
        $error = "Username minimal 3 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username hanya boleh huruf, angka, dan underscore.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // Cek username sudah dipakai
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Username sudah dipakai. Silakan pilih username lain.";
        } else {
            // Cek email sudah dipakai
            $cek_email = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
            mysqli_stmt_bind_param($cek_email, "s", $email);
            mysqli_stmt_execute($cek_email);
            mysqli_stmt_store_result($cek_email);
            
            if (mysqli_stmt_num_rows($cek_email) > 0) {
                $error = "Email sudah terdaftar. Silakan gunakan email lain.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $default_bio = "Halo! Saya menggunakan Neo-Linktree untuk berbagi tautan pentingku.";
                
                $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, email, password, bio) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssss", $nama, $username, $email, $hash, $default_bio);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Akun berhasil dibuat! Silakan login.";
                    // Redirect after 2 seconds
                    header("refresh:2; url=login.php?registered=1");
                } else {
                    $error = "Gagal mendaftar. Silakan coba lagi.";
                }
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
    <title>Daftar | Neo-Linktree</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e3fafc;
            background-image: radial-gradient(#1a1a1a 1.5px, transparent 1.5px);
            background-size: 24px 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 24px;
        }

        .auth-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 480px;
            border: 4px solid #1a1a1a;
            box-shadow: 12px 12px 0px #1a1a1a;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo h1 {
            font-size: 32px;
            font-weight: 900;
            text-transform: uppercase;
            background: linear-gradient(135deg, #b460f3, #ff76ce);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        h2 {
            font-size: 26px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 3px solid #1a1a1a;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            background-color: #fff;
            transition: all 0.15s ease;
        }

        .form-group input:focus {
            outline: none;
            background-color: #f0fdf4;
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0px #1a1a1a;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            font-size: 11px;
            color: #666;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            border: 3px solid #1a1a1a;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.1s ease;
            background: #94ffd8;
            color: #1a1a1a;
            box-shadow: 5px 5px 0px #1a1a1a;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0px #1a1a1a;
            background: #ffde4d;
        }

        button[type="submit"]:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px #1a1a1a;
        }

        .alert {
            padding: 12px 16px;
            border: 3px solid #1a1a1a;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 700;
            box-shadow: 4px 4px 0px #1a1a1a;
        }
        .alert-error {
            background: #ff6b6b;
            color: white;
        }
        .alert-success {
            background: #94ffd8;
            color: #1a1a1a;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-weight: 600;
        }
        .auth-footer a {
            color: #b460f3;
            text-decoration: none;
            font-weight: 800;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }

        .password-strength {
            margin-top: 8px;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        .strength-text {
            font-size: 10px;
            margin-top: 4px;
            text-align: right;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo">
        <h1>✨ Neo-Linktree</h1>
    </div>
    <h2>Daftar Akun</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?> ⏳ Mengalihkan...</div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required placeholder="Contoh: Ahmad Fauzi" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="tanpa spasi, contoh: ahmadfauzi" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" id="username">
            <small>Username akan muncul di URL halaman publikmu: /u/username</small>
            <div id="usernameStatus" style="font-size: 11px; margin-top: 5px;"></div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="email@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Minimal 6 karakter" id="password">
            <div class="password-strength">
                <div class="strength-bar" id="strengthBar"></div>
            </div>
            <div class="strength-text" id="strengthText"></div>
        </div>
        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm_password" required placeholder="Ketik ulang password" id="confirmPassword">
            <div id="passwordMatch" style="font-size: 11px; margin-top: 5px;"></div>
        </div>
        <button type="submit" name="register">🎉 Daftar Sekarang</button>
    </form>
    
    <p class="auth-footer">
        Sudah punya akun? <a href="login.php">Masuk</a>
    </p>
</div>

<script>
// Cek ketersediaan username (live)
const usernameInput = document.getElementById('username');
const usernameStatus = document.getElementById('usernameStatus');

let typingTimer;
const doneTyping = () => {
    const username = usernameInput.value.trim();
    if (username.length >= 3) {
        fetch(`cek_username.php?username=${encodeURIComponent(username)}`)
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    usernameStatus.innerHTML = '✅ Username tersedia';
                    usernameStatus.style.color = '#10b981';
                } else {
                    usernameStatus.innerHTML = '❌ Username sudah dipakai';
                    usernameStatus.style.color = '#ef4444';
                }
            });
    } else if (username.length > 0) {
        usernameStatus.innerHTML = '⚠️ Minimal 3 karakter';
        usernameStatus.style.color = '#ffde4d';
    } else {
        usernameStatus.innerHTML = '';
    }
};

usernameInput?.addEventListener('keyup', () => {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(doneTyping, 500);
});

// Password strength checker
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');

passwordInput?.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    const percentage = (strength / 5) * 100;
    strengthBar.style.width = percentage + '%';
    
    if (percentage < 20) {
        strengthBar.style.background = '#ef4444';
        strengthText.textContent = '💪 Lemah';
        strengthText.style.color = '#ef4444';
    } else if (percentage < 60) {
        strengthBar.style.background = '#ffde4d';
        strengthText.textContent = '👍 Sedang';
        strengthText.style.color = '#ffde4d';
    } else {
        strengthBar.style.background = '#10b981';
        strengthText.textContent = '💪 Kuat!';
        strengthText.style.color = '#10b981';
    }
    
    if (password.length === 0) {
        strengthText.textContent = '';
    }
});

// Confirm password match
const confirmPassword = document.getElementById('confirmPassword');
const passwordMatch = document.getElementById('passwordMatch');

function checkPasswordMatch() {
    const password = passwordInput?.value;
    const confirm = confirmPassword?.value;
    
    if (confirm.length > 0) {
        if (password === confirm) {
            passwordMatch.innerHTML = '✅ Password cocok';
            passwordMatch.style.color = '#10b981';
        } else {
            passwordMatch.innerHTML = '❌ Password tidak cocok';
            passwordMatch.style.color = '#ef4444';
        }
    } else {
        passwordMatch.innerHTML = '';
    }
}

confirmPassword?.addEventListener('input', checkPasswordMatch);
passwordInput?.addEventListener('input', checkPasswordMatch);
</script>
</body>
</html>