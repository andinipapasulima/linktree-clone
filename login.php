<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = isset($_GET['registered']) ? 'Akun berhasil dibuat! Silakan login.' : '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['login'] = true;
                $_SESSION['id_user'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['nama'] = $row['nama'];
                
                // Remember me (7 hari)
                if ($remember) {
                    setcookie('remember_user', $row['id'], time() + (86400 * 7), "/");
                }
                
                header("Location: dashboard.php");
                exit;
            }
        }
        $error = "Username atau password salah.";
        
        // Kode login_attempts sudah dihapus agar tidak error
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Masuk | Neo-Linktree</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffde4d;
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
            max-width: 440px;
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
        .logo p {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        h2 {
            font-size: 26px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
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
            font-size: 16px;
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

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0;
            font-weight: 600;
            text-transform: none;
            cursor: pointer;
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
            background: #b460f3;
            color: white;
            box-shadow: 5px 5px 0px #1a1a1a;
        }

        button[type="submit"]:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0px #1a1a1a;
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

        .forgot-link {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 15px;
        }
        .forgot-link a {
            font-size: 12px;
            color: #ff76ce;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="logo">
        <h1>⚡ Neo-Linktree</h1>
        <p>Biodata Link Modern & Keren</p>
    </div>
    <h2>Masuk</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="Username kamu" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" style="font-size: 16px;">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Password kamu" style="font-size: 16px;">
        </div>
        <div class="forgot-link">
            <a href="lupa_password.php">Lupa password?</a>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Ingat saya selama 7 hari</label>
        </div>
        <button type="submit" name="login">🚀 Masuk Sekarang</button>
    </form>
    
    <p class="auth-footer">
        Belum punya akun? <a href="register.php">Daftar Gratis</a>
    </p>
</div>
</body>
</html>