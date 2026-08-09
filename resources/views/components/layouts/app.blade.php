<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Preview, validate and safely import customers from CSV files.">
        <title>{{ $title ?? 'Klientu CSV importas' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-900">
        {{ $slot }}
    </body>
</html>
