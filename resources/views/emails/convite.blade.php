<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Convite</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            background: #070707;
            max-width: 480px;
            margin: 40px auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            padding: 32px 28px 24px 28px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h2 {
            color:#f5f5f5;
            margin: 0 0 8px 0;
        }

        .content {
            color: #444;
            font-size: 16px;
            margin-bottom: 24px;
        }

        .cta {
            text-align: center;
            margin-bottom: 24px;
        }

        .cta a {
            background: #ff368d;
            color: #fff !important;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            display: inline-block;
            transition: background 0.2s;
        }

        .cta a:hover {
            background: #3730a3;
        }

        .footer {
            color: #888;
            font-size: 13px;
            text-align: center;
            margin-top: 16px;
        }

        .content {
            color: #f5f5f5;
            font-size: 16px;
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Convite Especial</h2>
        </div>
        <div class="content">
            <p>Você foi convidado para participar da nossa plataforma <b>Musa Class</b>!</p>
            <p>Para aceitar o convite, clique no botão abaixo:</p>
        </div>
        <div class="cta">
            <a href="{{ $link }}">Aceitar convite</a>
        </div>
        <div class="footer">
            <p>Se você não esperava este convite, pode ignorar este e-mail.</p>
            <p>Atenciosamente,<br>Equipe Musa Class</p>
        </div>
    </div>
</body>

</html>