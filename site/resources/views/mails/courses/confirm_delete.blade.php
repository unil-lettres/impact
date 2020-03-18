<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ $course->name }}</title>
    </head>
    <body>
        <!-- TODO: Add content & translations -->
        <p>{{ $course->name }}</p>
    </body>
</html>
