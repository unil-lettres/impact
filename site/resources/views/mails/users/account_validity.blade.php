<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ trans('users.email.account.validity.subject')  }}</title>
    </head>
    <body>
        <p>{{ trans('mails.dear_user') }}</p>
        <p>{!! trans('users.email.account.validity.content', ['url' => route('home'), 'days' => $days]) !!}</p>
        <p>{{ trans('mails.do_not_reply') }}</p>
        <p>{{ trans('mails.impact_managers') }}<br>{!! $contactList !!}</p>
    </body>
</html>
