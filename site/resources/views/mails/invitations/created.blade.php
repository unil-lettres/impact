<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ trans('invitations.mail.create.subject') }}</title>
    </head>
    <body>
        <p>{!! trans('invitations.mail.create.info') !!}</p>

        <p>{{ trans('invitations.mail.create.sent_by') }}: {{ $invitation->creator->name ?? $invitation->creator->email }}</p>

        <p>{{ trans('invitations.mail.create.link') }}</p>

        <p><a href="{{ $invitation->getLink() }}" target="_blank"><strong>{{ $invitation->getLink() }}</strong></a></p>

        <p>{{ trans('mails.do_not_reply') }}</p>

        <p>{{ trans('mails.impact_team') }}</p>
    </body>
</html>
