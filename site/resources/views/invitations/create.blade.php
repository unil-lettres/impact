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
            <div class="row">
                <div class="col-12 mb-3 col-md-12 col-lg-7">
                    <label for="email" class="control-label form-label">{{ trans('invitations.email') }}</label>
                    <div>
                        <input id="email"
                               type="email"
                               class="form-control"
                               name="email"
                               value="{{ old('email') }}" required autofocus
                        >
                    </div>
                </div>

                <div class="col-12 mb-3 col-md-12 col-lg-5">
                    <label for="course" class="control-label form-label">{{ trans('invitations.select_space') }}</label>
                    <input id="course" name="course" type="hidden" value="">
                    <div id="rct-single-course-select"
                         reference="course"
                         data='{{ json_encode(['options' => $courses]) }}'
                    ></div>
                </div>
            </div>

            <button type="submit"
                    class="btn btn-primary">
                {{ trans('invitations.create') }}
            </button>
        </form>
    </div>
@endsection
