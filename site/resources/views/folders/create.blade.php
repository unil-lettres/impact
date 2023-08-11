@extends('layouts.app-base')

@section('title')
    {{ trans('folders.create') }}
@endsection

@section('content')
    <div id="create-folder">
        @can('create', [\App\Folder::class, $course])
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
                  action="{{ route('folders.store') }}">
                @csrf
                <div class="row">
                    <div class="col-12 mb-3 col-md-12 col-lg-7">
                        <label for="title" class="control-label form-label">{{ trans('folders.title') }}</label>
                        <div>
                            <input id="title"
                                   type="text"
                                   class="form-control"
                                   name="title"
                                   required autofocus
                            >
                        </div>
                    </div>

                    <div class="col-12 mb-3 col-md-12 col-lg-5">
                        <label for="parent_id" class="control-label form-label">{{ trans('folders.location') }}</label>
                        <input id="parent_id" name="parent_id" type="hidden" value="">
                        <div id="rct-single-folder-select"
                             reference="parent_id"
                             data='{{ json_encode(['options' => $folders]) }}'
                        ></div>
                    </div>
                </div>

                <input type="hidden" name="course_id" value="{{ $course?->id }}" >

                <button type="submit"
                        class="btn btn-primary">
                    {{ trans('folders.create') }}
                </button>
            </form>
        @endcan
    </div>
@endsection
