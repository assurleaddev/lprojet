<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — USED</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #374151;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0d9488;
            margin-bottom: 32px;
        }

        .icon {
            width: 72px;
            height: 72px;
            background: #f0fdfa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .icon svg {
            width: 36px;
            height: 36px;
            stroke: #0d9488;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        p {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
        }

        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 32px 0;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #0d9488;
            font-weight: 500;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: #0d9488;
            border-radius: 50%;
            animation: pulse 1.8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.75); }
        }

        @media (max-width: 480px) {
            .card { padding: 36px 24px; }
            h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">USED</div>

        <div class="icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </div>

        <h1>Site en maintenance</h1>
        <p>Nous effectuons une mise à jour pour améliorer votre expérience. Le site sera de retour très bientôt.</p>

        <hr class="divider">

        <span class="status">
            <span class="dot"></span>
            Retour imminent
        </span>
    </div>
</body>
</html>
