@extends('layouts.app-admin')

@section('admin.content')
    <div id="create-file">
        @can('create', \App\File::class)
            @section('title')
                {{ trans('files.create') }}
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
            <div class="row">
                @can('upload', [\App\File::class, null, null])
                    <div class="col-md-12 col-lg-7">
                        <div id="rct-uploader"
                             data='{{ json_encode(['locale' => Helpers::currentLocal(), 'maxFileSize' => 2000000000, 'maxNumberOfFiles' => 10]) }}'
                        ></div>
                    </div>
                @endcan

                <div class="col-md-12 col-lg-5">
                    <label for="course_id" class="control-label">{{ trans('files.select_space') }}</label>
                    <input id="course_id" name="course" type="hidden" value="">
                    <div id="rct-single-course-select"
                         reference="course_id"
                         data='{{ json_encode(['options' => $courses]) }}'
                    ></div>
                </div>
            </div>
        @endcan
    </div>
@endsection
