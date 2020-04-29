@extends('layouts.app-base')

@section('content')
    <div id="edit-folder">
        @can('update', $folder)
            @section('title')
                {{ trans('folders.edit') }}
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
                  action="{{ route('folders.update', $folder->id) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="form-group col-md-12 col-lg-7">
                        <label for="title" class="control-label">{{ trans('folders.title') }}</label>
                        <div>
                            <input id="title"
                                   type="text"
                                   value="{{ old('name', $folder->title) }}"
                                   class="form-control"
                                   name="title"
                                   required autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group col-md-12 col-lg-5">
                        <label for="parent_id" class="control-label">{{ trans('folders.location') }}</label>
                        <input id="parent_id" name="parent_id" type="hidden" value="{{ old('name', $folder->parent_id) }}">
                        <div id="rct-single-folder-select"
                             reference="parent_id"
                             data='{{ json_encode(['options' => $folders, 'default' => $parent]) }}'
                        ></div>
                    </div>
                </div>

                <button type="submit"
                        class="btn btn-primary">
                    {{ trans('folders.edit') }}
                </button>
            </form>
        @endcan
    </div>
@endsection
