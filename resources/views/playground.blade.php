<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Gateway Playground</title>
    @if(!app()->environment('testing'))
        @vite(['resources/js/playground/main.js'])
    @endif
</head>
<body>
    <div id="app"></div>
</body>
</html>
