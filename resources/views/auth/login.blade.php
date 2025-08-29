<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store">
    <title>Connexion Enseignant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f5f7fb; margin:0;}
        .wrap {min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;}
        .card {background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(18,38,63,.08); width:100%; max-width:420px; padding:28px 28px 22px;}
        h1 {font-size:22px; margin:0 0 6px; color:#111827;}
        p.sub {margin:0 0 18px; color:#6b7280; font-size:14px}
        label {display:block; font-size:13px; color:#374151; margin:10px 0 6px;}
        input[type="email"], input[type="password"] {
            width:100%; padding:12px 14px; border:1px solid #e5e7eb; border-radius:8px; outline:none; font-size:14px; transition:.2s;
        }
        input:focus {border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12)}
        .row {display:flex; align-items:center; justify-content:space-between; margin:10px 0 2px}
        .btn {width:100%; background:#2563eb; color:#fff; border:none; padding:12px; border-radius:8px; font-weight:600; cursor:pointer; margin-top:14px}
        .btn:hover {background:#1e40af}
        .error {background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:10px}
        .footer {text-align:center; color:#9ca3af; font-size:12px; margin-top:16px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Connexion</h1>
        <p class="sub">Entrez votre email et mot de passe pour accéder à la plateforme.</p>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="email">Adresse email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">

            <div class="row">
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#4b5563;">
                    <input type="checkbox" name="remember" value="1"> Se souvenir de moi
                </label>
                <div></div>
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div style="display:flex; align-items:center; gap:8px; margin:14px 0; color:#9ca3af;">
            <div style="height:1px; background:#e5e7eb; flex:1"></div>
            <div style="font-size:12px;">ou</div>
            <div style="height:1px; background:#e5e7eb; flex:1"></div>
        </div>

        <a href="{{ route('users.create') }}" class="btn" style="display:inline-block; text-align:center; background:#059669">Créer un compte</a>

        <div class="footer">© {{ date('Y') }} - Établissement</div>
    </div>
</div>
</body>
</html>
