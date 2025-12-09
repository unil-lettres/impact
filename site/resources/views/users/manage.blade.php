@extends('layouts.app-admin')

@section('admin.content')
    <div id="users">
        <div class="card">
            <div class="card-header d-flex justify-content-between gap-2">
                <div class="title">
                    {{ trans('users.manage') }}
                    <span class="badge bg-secondary">
                        {{ $users->total() }}
                    </span>
                </div>
                <div class="header-actions d-flex gap-2 flex-wrap">
                    <div class="search-users">
                        <form method="get" action="{{ route('admin.users.manage') }}">
                            <div class="input-group">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="{{ trans('users.search') }}"
                                       aria-label="{{ trans('users.search') }}"
                                       aria-describedby="button-search-user"
                                       value="{{ $search }}">

                                @if($filter)
                                    <input type="hidden" name="filter" value="{{ $filter }}">
                                @endif

                                @if($search)
                                    <a class="btn bg-white border-top border-bottom"
                                       type="button"
                                       id="button-clear-user"
                                       href="{{ route('admin.users.manage', ['filter' => $filter]) }}">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @endif

                                <button class="btn{{ $search ? ' btn-primary' : ' btn-secondary'  }}"
                                        type="submit"
                                        id="button-search-user">
                                    {{ trans('general.search') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="filter-users dropdown show">
                        <a class="btn dropdown-toggle{{ $filter ? ' btn-primary' : ' btn-secondary'  }}"
                           href="#"
                           role="button"
                           id="dropdownUsersFiltersLink"
                           data-bs-toggle="dropdown"
                           aria-haspopup="true"
                           aria-expanded="false">
                            {{ trans('admin.filters') }}
                            <i class="fa-solid{{ $filter ? ' fa-check' : '' }}"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownUsersFiltersLink">
                            <a class="dropdown-item" href="{{ route('admin.users.manage', ['search' => $search]) }}">
                                -
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.users.manage', ['filter' => \App\Enums\UsersFilter::Expired, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\UsersFilter::Expired) !!}
                                {{ trans('users.expired') }}
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.users.manage', ['filter' => \App\Enums\UsersFilter::Aai, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\UsersFilter::Aai) !!}
                                {{ trans('users.aai') }}
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.users.manage', ['filter' => \App\Enums\UsersFilter::Local, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\UsersFilter::Local) !!}
                                {{ trans('users.local') }}
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('admin.users.manage', ['filter' => \App\Enums\UsersFilter::Contact, 'search' => $search]) }}">
                                {!! Helpers::filterSelectedMark($filter, \App\Enums\UsersFilter::Contact) !!}
                                {{ trans('users.contact') }}
                            </a>
                        </div>
                    </div>

                    <div class="create-users">
                        <a href="{{ route('admin.users.create') }}"
                           class="btn btn-primary">
                            {{ trans('users.create') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body table-responsive">
                @if ($users->items())
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ trans('users.email') }}</th>
                                <th>{{ trans('users.name') }}</th>
                                <th>{{ trans('users.created_at') }}</th>
                                <th>{{ trans('users.type') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users->items() as $user)
                                @can('view', $user)
                                    <tr class="{{ $user->type }}{{ $user->isValid() ? '' : ' invalid' }}">
                                        <td>
                                            {{ $user->email }}
                                            @if ($user->admin)
                                                <span class="badge bg-primary">{{ trans('users.admin') }}</span>
                                            @endif
                                            @unless ($user->isValid())
                                                <span class="badge bg-danger">{{ trans('users.expired') }}</span>
                                            @endunless
                                        </td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $user->type }}</td>
                                        <td class="actions">
                                            @can('update', $user)
                                                <span>
                                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                                       data-bs-toggle="tooltip"
                                                       data-placement="top"
                                                       class="btn btn-primary"
                                                       title="{{ trans('users.edit') }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                </span>
                                            @endcan
                                            @can('extend', $user)
                                                <span>
                                                    <a href="{{ route('admin.users.extend', $user->id) }}"
                                                       data-bs-toggle="tooltip"
                                                       data-placement="top"
                                                       class="btn btn-primary"
                                                       title="{{ trans('users.validity.extend', ['months' => config('const.users.validity')]) }}">
                                                        <i class="far fa-clock"></i>
                                                    </a>
                                                </span>
                                            @endcan
                                            @can('delete', $user)
                                                <span>
                                                    <form class="with-delete-confirm" method="post"
                                                          action="{{ route('admin.users.destroy', $user->id) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-placement="top"
                                                                title="{{ trans('users.delete') }}">
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
                    {{ $users->onEachSide(1)->links() }}
                @else
                    <p class="text-secondary text-center">
                        {{ trans('users.not_found') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
