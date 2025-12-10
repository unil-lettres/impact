<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ trans('courses.mail.confirm_delete.subject') }}</title>
    </head>
    <body>
        <p>{{ trans('mails.dear_user') }}</p>
        <p>{{ trans('courses.mail.confirm_delete.request', ['name' => $course->name]) }}</p>
        <p>{{ trans('courses.mail.confirm_delete.confirm') }}</p>
        <p>{{ trans('courses.mail.confirm_delete.destroyed') }}</p>
        <p>{{ trans('mails.impact_managers') }}<br>{!! $contactList !!}</p>
    </body>
</html>
