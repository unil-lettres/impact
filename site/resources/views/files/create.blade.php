@extends('layouts.app-admin')

@section('title')
    {{ trans('files.create') }}
@endsection

@section('admin.content')
    <div id="create-file">
        @can('create', \App\File::class)
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
                        <div id="rct-files"
                             data='{{ json_encode(['locale' => Helpers::currentLocal(), 'modal' => false, 'maxNumberOfFiles' => 10]) }}'
                        ></div>
                    </div>
                @endcan
            </div>
        @endcan
    </div>
@endsection
