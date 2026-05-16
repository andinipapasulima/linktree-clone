<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan | Neo-Linktree</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffde4d;
            background-image: radial-gradient(#1a1a1a 1.5px, transparent 1.5px);
            background-size: 24px 24px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
        }

        .card {
            background: #ffffff;
            border: 4px solid #1a1a1a;
            border-radius: 24px;
            box-shadow: 12px 12px 0px #1a1a1a;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.85) translateY(30px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .error-number {
            font-size: 96px;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #b460f3, #ff76ce);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            animation: wobble 2.5s ease-in-out infinite;
        }

        @keyframes wobble {
            0%, 100% { transform: rotate(-2deg); }
            50%       { transform: rotate(2deg); }
        }

        .emoji { font-size: 48px; margin-bottom: 16px; display: block; }

        h1 {
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        p {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            border: 3px solid #1a1a1a;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.1s ease;
            box-shadow: 4px 4px 0px #1a1a1a;
        }
        .btn:hover  { transform: translate(-2px,-2px); box-shadow: 6px 6px 0px #1a1a1a; }
        .btn:active { transform: translate(2px,2px);   box-shadow: 0 0 0 #1a1a1a; }

        .btn-primary { background: #b460f3; color: white; }
        .btn-outline { background: #94ffd8; color: #1a1a1a; }

        .divider {
            border: none;
            border-top: 2px dashed #ddd;
            margin: 24px 0;
        }

        .hint {
            font-size: 11px;
            color: #999;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="error-number">404</div>
    <span class="emoji">🔍</span>
    <h1>Halaman Tidak Ditemukan</h1>
    <p>
        Ups! Halaman atau username yang kamu cari tidak ada,<br>
        mungkin sudah dihapus atau URL-nya salah ketik.
    </p>

    <div class="btn-group">
        <a href="login.php" class="btn btn-primary">
            <i class="fas fa-home"></i> Ke Beranda
        </a>
        <a href="register.php" class="btn btn-outline">
            <i class="fas fa-user-plus"></i> Daftar Gratis
        </a>
    </div>

    <hr class="divider">
    <p class="hint">
        <i class="fas fa-lightbulb"></i>
        Ingin punya halaman seperti ini? Daftar dan buat profilmu sekarang!
    </p>
</div>

<script>
    // Jika user login, tambahkan tombol dashboard
    // (tidak perlu session PHP karena ini halaman statis — cukup localStorage trick)
</script>
</body>
</html>