@extends('layouts.app-admin')

@section('admin.menu')
    @include('admin.menu')
@stop
<!-- TODO: translations -->
@section('admin.content')
    <div id="users">
        <div class="card">
            <div class="card-header">
                <span class="title">Géstion des utilisateurs <span class="badge badge-secondary">{{ $users->total() }}</span></span>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary float-right">Créer un utilisateur</a>
            </div>
            <div class="card-body">
                @if ($users->items())
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Date de création</th>
                                <th>Type</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users->items() as $user)
                                @can('view', $user)
                                    <tr class="{{ Helpers::isUserValid($user) ? '' : 'invalid' }}">
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->created_at }}</td>
                                        <td>{{ $user->type }}</td>
                                        <td class="actions">
                                            @can('delete', $user)
                                                <span>
                                                    <form class="with-delete-confirm" method="post"
                                                          action="{{ route('admin.users.destroy', $user->id) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Supprimer l'utilisateur">
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
                    <p class="text-secondary">Aucun utilisateur</p>
                @endif
            </div>
        </div>
    </div>
@stop
