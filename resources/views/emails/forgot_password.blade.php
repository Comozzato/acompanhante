<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            text-align: center;
            padding: 40px;
        }

        .container {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            margin: auto;
        }

        h1 {
            color: #00c3ff;
        }

        p {
            color: #cccccc;
        }

        .code {
            font-size: 24px;
            font-weight: bold;
            color: #00c3ff;
            background-color: #2a2a2a;
            padding: 10px;
            border-radius: 6px;
            display: inline-block;
            margin: 20px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777777;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Recuperação de Senha</h1>
        <p>Olá!</p>
        <p>Você solicitou a recuperação da sua senha. Use o código abaixo para redefini-la:</p>

        <div class="code">{{ $code }}</div>

        <p>Se você não solicitou essa alteração, ignore este e-mail.</p>

        <div class="footer">
            <p>Equipe {{ config('app.name') }}</p>
        </div>
    </div>
</body>

</html>