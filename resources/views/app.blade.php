<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-app-env="{{ env('APP_ENV') }}">

<head>
    <meta charset="utf-8">
    <!-- Pour iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Pour Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#000000">

    <!-- Empêcher le zoom -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Platform d'expérimentation</title>

    <!-- Optimisation du chargement des polices -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Lexend:wght@100..900&display=swap"
        rel="stylesheet">

    @viteReactRefresh
    @vite('resources/js/app.jsx')
</head>

<body class="font-sans">
    <div id="root"></div>
</body>

</html>
