<div id="invitations">
    <div class="card">
        <div class="card-header">
            <span class="title">{{ trans('invitations.pending') }} <span class="badge badge-secondary">{{ $invitations->total() }}</span></span>
            <a href="{{ Route::is('admin.invitations.manage') ? route('admin.invitations.create') : route('invitations.create') }}"
               class="btn btn-primary float-right">
                {{ trans('invitations.create') }}
            </a>
        </div>
        <div class="card-body">
            @if ($invitations->items())
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{ trans('invitations.email') }}</th>
                        <th>{{ trans('invitations.created_at') }}</th>
                        <th>{{ trans('courses.course') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($invitations->items() as $invitation)
                        @can('view', $invitation)
                            <tr>
                                <td>{{ $invitation->email }}</td>
                                <td>{{ $invitation->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ Helpers::truncate($invitation->course->name) }}</td>
                                <td class="actions">
                                    @can('view', $invitation)
                                        <span>
                                                <button type="button"
                                                        class="btn btn-primary base-popover"
                                                        data-toggle="popover"
                                                        title="{{ trans('invitations.link') }}"
                                                        data-content="<em>{{ $invitation->getLink() }}</em>">
                                                    <i class="far fa-share-square"></i>
                                                </button>
                                            </span>
                                    @endcan
                                    @can('mail', $invitation)
                                        <span>
                                                <a href="{{ route('send.invite', $invitation->id) }}"
                                                   data-toggle="tooltip"
                                                   data-placement="top"
                                                   class="btn btn-primary"
                                                   title="{{ trans('invitations.send') }}">
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
                                                    <button type="submit"
                                                            class="btn btn-danger"
                                                            data-toggle="tooltip"
                                                            data-placement="top"
                                                            title="{{ trans('invitations.delete') }}">
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
                {{ $invitations->onEachSide(1)->links() }}
            @else
                <p class="text-secondary">
                    {{ trans('invitations.not_found') }}
                </p>
            @endif
        </div>
    </div>
</div>
