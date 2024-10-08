@extends('layouts.app-admin')

@section('admin.content')
    <div id="edit-file">
        @can('update', $file)
            <div class="row">
                <div class="col-md-12 col-lg-8">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div><br />
                    @endif
                    <div class="card">
                        <div class="card-header">
                            <span class="title">{{ $file->name }}</span>

                            @if(Helpers::isFileStatus($file, \App\Enums\FileStatus::Ready))
                                <a href="{{ Helpers::fileUrl($file->filename) }}"
                                   target="_blank"
                                   data-bs-toggle="tooltip"
                                   data-placement="top"
                                   class="btn btn-primary float-end"
                                   title="{{ trans('files.url') }}">
                                    <i class="far fa-share-square"></i>
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <form method="post"
                                  action="{{ route('admin.files.update', $file->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="col-12 mb-3 row">
                                    <label for="name" class="col-md-3 col-form-label">
                                        {{ trans('files.name') }}
                                    </label>
                                    <div class="col-md-9">
                                        <input id="name"
                                               type="text"
                                               name="name"
                                               value="{{ old('name', $file->name) }}"
                                               class="form-control"
                                        >
                                    </div>
                                </div>

                                <div class="col-12 mb-3 row">
                                    <label for="course" class="col-md-3 col-form-label">
                                        {{ trans('files.space') }}
                                    </label>
                                    <div class="col-md-9">
                                        <input id="course_id" name="course" type="hidden" value="{{ $file->course ? $file->course->id : '' }}">
                                        <div id="rct-single-course-select"
                                             reference="course_id"
                                             data='{{ json_encode(['options' => $courses, 'default' => $file->course, 'clearable' => true, 'disabled' => $cards->isNotEmpty()]) }}'
                                        ></div>
                                    </div>
                                </div>

                                @if ($file->filename)
                                    <div class="col-12 mb-3 row">
                                        <label for="filename" class="col-md-3 col-form-label">
                                            {{ trans('files.filename') }}
                                        </label>
                                        <div class="col-md-9">
                                            <input id="filename"
                                                   type="text"
                                                   name="filename"
                                                   value="{{ old('filename', rawurldecode($file->filename)) }}"
                                                   class="form-control"
                                                   disabled
                                            >
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12 mb-3 row">
                                    <label for="status" class="col-md-3 col-form-label">
                                        {{ trans('files.status') }}
                                    </label>
                                    <div class="col-md-9">
                                        <input id="status"
                                               type="text"
                                               name="status"
                                               value="{{ old('status', $file->status) }}"
                                               class="form-control {{ $file->status }}"
                                               disabled
                                        >
                                    </div>
                                </div>

                                <div class="col-12 mb-3 row">
                                    <label for="type" class="col-md-3 col-form-label">
                                        {{ trans('files.type') }}
                                    </label>
                                    <div class="col-md-9">
                                        <input id="type"
                                               type="text"
                                               name="type"
                                               value="{{ old('type', $file->type) }}"
                                               class="form-control"
                                               disabled
                                        >
                                    </div>
                                </div>

                                @if ($file->size)
                                    <div class="col-12 mb-3 row">
                                        <label for="size" class="col-md-3 col-form-label">
                                            {{ trans('files.size_in') }}
                                        </label>
                                        <div class="col-md-9">
                                            <input id="size"
                                                   type="number"
                                                   name="size"
                                                   value="{{ old('size', $file->size) }}"
                                                   class="form-control"
                                                   disabled
                                            >
                                        </div>
                                    </div>
                                @endif

                                @if ($file->width)
                                    <div class="col-12 mb-3 row">
                                        <label for="width" class="col-md-3 col-form-label">
                                            {{ trans('files.width') }}
                                        </label>
                                        <div class="col-md-9">
                                            <input id="width"
                                                   type="number"
                                                   name="width"
                                                   value="{{ old('width', $file->width) }}"
                                                   class="form-control"
                                                   disabled
                                            >
                                        </div>
                                    </div>
                                @endif

                                @if ($file->height)
                                    <div class="col-12 mb-3 row">
                                        <label for="height" class="col-md-3 col-form-label">
                                            {{ trans('files.height') }}
                                        </label>
                                        <div class="col-md-9">
                                            <input id="height"
                                                   type="number"
                                                   name="height"
                                                   value="{{ old('height', $file->height) }}"
                                                   class="form-control"
                                                   disabled
                                            >
                                        </div>
                                    </div>
                                @endif

                                @if ($file->length)
                                    <div class="col-12 mb-3 row">
                                        <label for="length" class="col-md-3 col-form-label">
                                            {{ trans('files.length_in') }}
                                        </label>
                                        <div class="col-md-9">
                                            <input id="length"
                                                   type="number"
                                                   name="length"
                                                   value="{{ old('height', $file->length) }}"
                                                   class="form-control"
                                                   disabled
                                            >
                                        </div>
                                    </div>
                                @endif

                                <hr>
                                <button type="submit" class="btn btn-primary">
                                    {{ trans('files.update') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <span class="title">{{ trans('cards.cards') }}</span>
                        </div>
                        <div class="card-body">
                            @if($cards->isNotEmpty())
                                <ul>
                                    @foreach ($cards as $card)
                                        <li>
                                            <a class="legacy" href="{{ route('cards.show', $card->id) }}">
                                                {{ $card->title }}
                                            </a>

                                            @can('unlinkFile', $card)
                                                <form class="with-unlink-confirm" method="post" style="display: inline;"
                                                      action="{{ route('cards.unlink.file', $card->id) }}">
                                                    @method('PUT')
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            data-bs-toggle="tooltip"
                                                            data-placement="top"
                                                            title="{{ trans('cards.unlink') }}">
                                                        <i class="far fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-secondary">
                                    {{ trans('cards.not_found') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection
