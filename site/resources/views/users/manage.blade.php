@extends('layouts.app-admin')

@section('admin.content')
    <div id="users">
        <div class="card">
            <div class="card-header">
                <span class="title">{{ trans('users.manage') }} <span class="badge badge-secondary">{{ $users->total() }}</span></span>
                <a href="{{ route('admin.users.create') }}"
                   class="btn btn-primary float-right">
                    {{ trans('users.create') }}
                </a>
                <div class="dropdown show float-right mr-1">
                    <a class="btn btn-primary dropdown-toggle"
                       href="#"
                       role="button"
                       id="dropdownUsersFiltersLink"
                       data-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false">
                        {{ trans('admin.filters') }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownUsersFiltersLink">
                        <a class="dropdown-item" href="{{ route('admin.users.manage') }}">
                            -
                        </a>
                        <a class="dropdown-item"
                           href="{{ route('admin.users.manage', ['filter' => \App\Enums\UsersFilter::Expired]) }}">
                            {{ trans('users.expired') }}
                        </a>
                        <a class="dropdown-item"
                           href="{{ route('admin.users.manage', ['filter' => \App\Enums\UsersFilter::Aai]) }}">
                            {{ trans('users.aai') }}
                        </a>
                        <a class="dropdown-item"
                           href="{{ route('admin.users.manage', ['filter' => \App\Enums\UsersFilter::Local]) }}">
                            {{ trans('users.local') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
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
                                    <tr class="{{ $user->type }}{{ Helpers::isUserValid($user) ? '' : ' invalid' }}">
                                        <td>
                                            {{ $user->email }}
                                            <div>
                                                @if ($user->admin)
                                                    <span class="badge badge-primary">{{ trans('users.admin') }}</span>
                                                @endif
                                                @unless (Helpers::isUserValid($user))
                                                    <span class="badge badge-danger">{{ trans('users.expired') }}</span>
                                                @endunless
                                            </div>
                                        </td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $user->type }}</td>
                                        <td class="actions">
                                            @can('update', $user)
                                                <span>
                                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                                       data-toggle="tooltip"
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
                                                       data-toggle="tooltip"
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
                                                                data-toggle="tooltip"
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
                    <p class="text-secondary">
                        {{ trans('users.not_found') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
