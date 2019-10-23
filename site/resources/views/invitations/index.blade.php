@extends('layouts.app-base')

@section('title')
    {{ trans('invitations.manage') }}
@stop

@section('content')
    <div id="invitations">
        <div class="card">
            <div class="card-header">
                <span class="title">{{ trans('invitations.pending') }} <span class="badge badge-secondary">{{ $count }}</span></span>
                <a href="{{ route('invitations.create') }}" class="btn btn-primary float-right">{{ trans('invitations.create') }}</a>
            </div>
            <div class="card-body">
                @if ($count > 0)
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ trans('invitations.email') }}</th>
                            <th>{{ trans('invitations.created_at') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($invitations as $invitation)
                            @can('view', $invitation)
                                <tr>
                                    <td>{{ $invitation->email }}</td>
                                    <td>{{ $invitation->created_at }}</td>
                                    <td class="actions">
                                        @can('view', $invitation)
                                            <span>
                                                <button type="button" class="btn btn-primary base-popover" data-toggle="popover" title="{{ trans('invitations.link') }}" data-content="<em>{{ $invitation->getLink() }}</em>">
                                                    <i class="far fa-share-square"></i>
                                                </button>
                                            </span>
                                        @endcan
                                        @can('mail', $invitation)
                                            <span>
                                                <a href="{{ route('sendInvite', $invitation->id) }}" class="btn btn-primary" title="{{ trans('invitations.send') }}">
                                                    <i class="far fa-paper-plane"></i>
                                                </a>
                                            </span>
                                        @endcan
                                        @can('delete', $invitation)
                                            <span>
                                                <form class="with-delete-confirm" method="post"
                                                      action="{{ route('invitations.destroy', $invitation->id) }}">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger" title="{{ trans('invitations.delete') }}">
                                                        <i class="far fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </span>
                                        @endcan
                                    </td>
                                </tr>
                            @endcan
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-secondary">{{ trans('invitations.not_found') }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection
