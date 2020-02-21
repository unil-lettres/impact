@extends('layouts.app-base')

@section('content')
    <div id="create-card">
        @can('create', [\App\Card::class, $course])
            @section('title')
                {{ trans('cards.create') }}
            @endsection
            <hr>
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
                  action="{{ route('cards.store') }}">
                @csrf
                <div class="form-group">
                    <label for="title" class="control-label">{{ trans('cards.title') }}</label>
                    <div>
                        <input id="title"
                               type="text"
                               class="form-control"
                               name="title"
                               required autofocus
                        >
                    </div>
                </div>

                <input type="hidden" name="course_id" value="{{ $course->id }}" >

                <button type="submit"
                        class="btn btn-primary">
                    {{ trans('cards.create') }}
                </button>
            </form>
        @endcan
    </div>
@endsection
