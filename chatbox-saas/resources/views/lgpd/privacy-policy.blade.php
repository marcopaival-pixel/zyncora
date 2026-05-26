<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - {{ $company->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --bg: #f8fafc;
            --text: #1e293b;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        h1 {
            color: var(--primary);
            margin: 0;
            font-size: 24px;
        }
        .content {
            font-size: 16px;
        }
        footer {
            margin-top: 40px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Política de Privacidade</h1>
            <p>{{ $company->name }}</p>
        </header>

        <div class="content">
            {!! $policy !!}
        </div>

        <footer>
            &copy; {{ date('Y') }} {{ $company->name }}. Todos os direitos reservados.
        </footer>
    </div>
</body>
</html>
