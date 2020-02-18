@extends(Route::is('admin.invitations.create') ? 'layouts.app-admin' : 'layouts.app-base')

@section(Route::is('admin.invitations.create') ? 'admin.content' : 'content')
    <div id="create-invitation">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div><br />
        @endif
        <form method="post"
              action="{{ route('invitations.store') }}">
            @csrf
            <div class="form-group">
                <label for="email" class="control-label">{{ trans('invitations.email') }}</label>
                <div>
                    <input id="email"
                           type="email"
                           class="form-control"
                           name="email"
                           value="{{ old('email') }}" required autofocus
                    >
                </div>
            </div>

            <button type="submit"
                    class="btn btn-primary">
                {{ trans('invitations.create') }}
            </button>
        </form>
        <!-- TODO: add possibility to choose the course attached to the invitation (list managed courses only) -->
    </div>
@endsection
