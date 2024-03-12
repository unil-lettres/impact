<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <title>{{ trans('invitations.mail.subject') }}</title>
    </head>
    <body>
        <p>{!! trans('invitations.mail.info') !!}</p>
        <ul>
            <li>{{ trans('invitations.mail.sent_by') }}: {{ $invitation->creator->name ?? $invitation->creator->email }}</li>
            <li>{{ trans('invitations.mail.enrolled_in') }}: {{ $invitation->course->name }}</li>
        </ul>
        <p>{{ trans('invitations.mail.local.link') }}</p>
        <p><a href="{{ $invitation->getLink() }}" target="_blank"><strong>{{ $invitation->getLink() }}</strong></a></p>
        <p>{{ trans('mails.do_not_reply') }}</p>
        <p>{{ trans('mails.impact_team') }}</p>
    </body>
</html>
