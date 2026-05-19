<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body { margin: 0; background: #f4f6f8; color: #182230; font-family: Arial, sans-serif; }
        .box { max-width: 560px; margin: 80px auto; padding: 28px; background: #fff; border: 1px solid #d9e0e8; border-radius: 8px; text-align: center; }
        h1 { margin: 0 0 12px; font-size: 24px; }
        p { margin: 0; color: #475569; line-height: 1.6; }
    </style>
</head>
<body>
    <main class="box">
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
    </main>
</body>
</html>
