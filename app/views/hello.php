<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <main style="max-width: 720px; margin: 5rem auto; padding: 0 1.5rem; font-family: system-ui, sans-serif;">
        <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>

        <p>
            Esta página NO utiliza los layouts
            <code>appHead.php</code> y
            <code>appFooter.php</code>.
        </p>

        <nav>
            <a href='./'> Home </a>&nbsp;
            <a href='./helloRender'> Render view </a>&nbsp;
            <a href='./helloExtend'> Extend view </a>
        </nav>
    </main>
</body>
</html>