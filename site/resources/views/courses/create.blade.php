@extends('layouts.app-admin')

@section('admin.content')
    <div id="create-course">
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
              action="{{ route('admin.courses.store') }}">
            @csrf
            <div class="form-group">
                <label for="name" class="control-label">{{ trans('courses.name') }}</label>
                <div>
                    <input id="name"
                           type="text"
                           class="form-control"
                           name="name"
                           required autofocus
                    >
                </div>
            </div>

            <!-- TODO: add description field -->
            <!-- TODO: add external id field -->

            <button type="submit"
                    class="btn btn-primary">
                {{ trans('courses.create') }}
            </button>
        </form>
    </div>
@endsection
