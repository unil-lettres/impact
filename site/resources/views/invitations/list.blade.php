<div id="invitations">
    <div class="card">
        <div class="card-header d-flex justify-content-between gap-2">
            <div class="title">
                {{ trans('invitations.pending') }}
                <span class="badge bg-secondary">
                    {{ $invitations->total() }}
                </span>
            </div>
            <div class="header-actions d-flex gap-2 flex-wrap">
                @if(Route::is('admin.invitations.manage'))
                    <div class="search-invitations">
                        <form method="get" action="{{ route('admin.invitations.manage') }}">
                            <div class="input-group">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="{{ trans('invitations.search') }}"
                                       aria-label="{{ trans('invitations.search') }}"
                                       aria-describedby="button-search-invitation"
                                       value="{{ $search }}">

                                @if($filter)
                                    <input type="hidden" name="filter" value="{{ $filter }}">
                                @endif

                                @if($search)
                                    <a class="btn bg-white border-top border-bottom"
                                       type="button"
                                       id="button-clear-invitation"
                                       href="{{ route('admin.invitations.manage', ['filter' => $filter]) }}">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @endif

                                <button class="btn{{ $search ? ' btn-primary' : ' btn-secondary'  }}"
                                        type="submit"
                                        id="button-search-invitation">
                                    {{ trans('general.search') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="filter-invitations dropdown show">
                        <a class="btn dropdown-toggle{{ $filter ? ' btn-primary' : ' btn-secondary'  }}"
                           href="#"
                           role="button"
                           id="dropdownInvitationsFiltersLink"
                           data-bs-toggle="dropdown"
                           aria-haspopup="true"
                           aria-expanded="false">
                            {{ trans('admin.filters') }}
                            <i class="fa-solid{{ $filter ? ' fa-check' : '' }}"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownInvitationsFiltersLink">
                            <a class="dropdown-item" href="{{ route('admin.invitations.manage', ['search' => $search]) }}">
                                -
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.invitations.manage', ['filter' => \App\Enums\InvitationType::Local, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\InvitationType::Local) !!}
                                {{ trans('invitations.local') }}
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.invitations.manage', ['filter' => \App\Enums\InvitationType::Aai, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\InvitationType::Aai) !!}
                                {{ trans('invitations.aai') }}
                            </a>
                        </div>
                    </div>
                @endif

                @can('create', [\App\Invitation::class, null])
                    <div class="create-invitations">
                        <a href="{{ Route::is('admin.invitations.manage') ? route('admin.invitations.create') : route('invitations.create') }}"
                           class="btn btn-primary">
                            {{ trans('invitations.create') }}
                        </a>
                    </div>
                @endcan
            </div>
        </div>

        <div class="card-body table-responsive">
            @if ($invitations->items())
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{ trans('invitations.email') }}</th>
                        <th>{{ trans('invitations.created_at') }}</th>
                        <th>{{ trans('courses.course') }}</th>
                        <th>{{ trans('invitations.type') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($invitations->items() as $invitation)
                        @can('view', $invitation)
                            <tr>
                                <td>{{ $invitation->email }}</td>
                                <td>{{ $invitation->created_at->format('d/m/Y H:i:s') }}</td>
                                <td title="{{ $invitation->course->name }}">
                                    {{ Helpers::truncate($invitation->course->name, 25) }}
                                </td>
                                <td>{{ $invitation->type }}</td>
                                <td class="actions">
                                    @can('view', $invitation)
                                        @if($invitation->getLink())
                                            <span>
                                                <button type="button"
                                                        class="btn btn-primary base-popover"
                                                        title="{{ trans('invitations.link') }}"
                                                        data-bs-html="true"
                                                        data-bs-toggle="popover"
                                                        data-bs-trigger="hover click"
                                                        data-bs-content="<em>{{ $invitation->getLink() }}</em>">
                                                    <i class="far fa-share-square"></i>
                                                </button>
                                            </span>
                                        @endif
                                    @endcan
                                    @can('mail', $invitation)
                                        <span>
                                            <a href="{{ route('send.invite', $invitation->id) }}"
                                               data-bs-toggle="tooltip"
                                               data-placement="top"
                                               class="btn btn-primary"
                                               title="{{ trans('invitations.send') }}">
                                                <i class="far fa-paper-plane"></i>
                                            </a>
                                        </span>
                                    @endcan
                                    @can('forceDelete', $invitation)
                                        <span>
                                            <form class="with-delete-confirm" method="post"
                                                  action="{{ route('invitations.destroy', $invitation->id) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-danger"
                                                        data-bs-toggle="tooltip"
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
                <p class="text-secondary text-center">
                    {{ trans('invitations.not_found') }}
                </p>
            @endif
        </div>
    </div>
</div>
