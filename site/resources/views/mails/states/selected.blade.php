<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ $subject }}</title>
    </head>
    <body>
        {!! $content !!}
    </body>
</html>
