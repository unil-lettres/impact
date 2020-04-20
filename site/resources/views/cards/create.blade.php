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
                <div class="row">
                    <div class="form-group col-md-12 col-lg-7">
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

                    <div class="form-group col-md-12 col-lg-5">
                        <label for="folder_id" class="control-label">{{ trans('folders.location') }}</label>
                        <input id="folder_id" name="folder_id" type="hidden" value="">
                        <div id="rct-single-folder-select"
                             reference="folder_id"
                             data='{{ json_encode(['options' => $folders]) }}'
                        ></div>
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
