<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ trans('users.email.local.account.created.subject') }}</title>
    </head>
    <body>
        <p>{{ trans('mails.hello') }}</p>
        <p>{!! trans('users.email.local.account.created.content', ['url' => route('home')]) !!}</p>
        <p>{{ trans('users.email.local.account.created.password', ['password' => $password]) }}</p>
        <p>{!! trans('users.email.local.account.created.password_change', ['profile' => route('users.profile', $user->id)]) !!}</p>
        <p>{{ trans('mails.do_not_reply') }}</p>
        <p>{{ trans('mails.impact_team') }}</p>
    </body>
</html>
