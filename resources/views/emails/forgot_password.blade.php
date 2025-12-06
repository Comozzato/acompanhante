<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            background: #0a0a0a;
            max-width: 480px;
            margin: 40px auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            padding: 32px 28px 24px 28px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            letter-spacing: 0.5px;
        }

        .content {
            color: #f5f5f5;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .code {
            background: #1c1c1c;
            color: #ff368d;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 3px;
            text-align: center;
            border-radius: 6px;
            padding: 14px 0;
            margin: 24px 0;
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
            transition: background 0.2s ease;
        }

        .cta a:hover {
            background: #d82d7a;
        }

        .footer {
            color: #aaa;
            font-size: 13px;
            text-align: center;
            margin-top: 16px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Recuperação de Senha</h1>
        </div>

        <div class="content">
            <p>Olá!</p>
            <p>Você solicitou a recuperação da sua senha. Use o código abaixo para redefini-la:</p>

            <div class="code">{{ $code }}</div>

            <p>Se você não solicitou essa alteração, ignore este e-mail.</p>
        </div>

        <div class="footer">
            <p>Atenciosamente,<br><strong>Equipe Musa Class</strong></p>
        </div>
    </div>
</body>

</html>