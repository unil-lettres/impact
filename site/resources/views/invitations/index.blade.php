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
                            <th>{{ trans('invitations.link') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($invitations as $invitation)
                            @can('view', $invitation)
                                <tr>
                                    <td>{{ $invitation->email }}</td>
                                    <td>{{ $invitation->created_at }}</td>
                                    <td>
                                        <kbd>{{ $invitation->getLink() }}</kbd>
                                    </td>
                                    <td>
                                        @can('delete', $invitation)
                                            <form class="with-delete-confirm" method="post"
                                                  action="{{ route('invitations.destroy', $invitation->id) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
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
