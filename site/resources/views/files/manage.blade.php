@extends('layouts.app-admin')

@section('admin.content')
    @can('manage', \App\File::class)
        <div id="files">
            <div class="card">
                <div class="card-header d-flex justify-content-between gap-2">
                    <div class="title">
                        {{ trans('files.files') }}
                        <span class="badge bg-secondary">
                            {{ $files->total() }}
                        </span>
                    </div>
                    <div class="header-actions d-flex gap-2 flex-wrap">
                        <div class="search-files">
                            <form method="get" action="{{ route('admin.files.manage') }}">
                                <div class="input-group">
                                    <input type="text"
                                           name="search"
                                           class="form-control"
                                           placeholder="{{ trans('files.search') }}"
                                           aria-label="{{ trans('files.search') }}"
                                           aria-describedby="button-search-file"
                                           value="{{ $search }}">

                                    @if($filter)
                                        <input type="hidden" name="filter" value="{{ $filter }}">
                                    @endif

                                    @if($search)
                                        <a class="btn bg-white border-top border-bottom"
                                           type="button"
                                           id="button-clear-file"
                                           href="{{ route('admin.files.manage', ['filter' => $filter]) }}">
                                            <i class="fa-solid fa-xmark"></i>
                                        </a>
                                    @endif

                                    <button class="btn{{ $search ? ' btn-primary' : ' btn-secondary'  }}"
                                            type="submit"
                                            id="button-search-file">
                                        {{ trans('general.search') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="filter-files dropdown show">
                            <a class="btn dropdown-toggle{{ $filter ? ' btn-primary' : ' btn-secondary'  }}"
                               href="#"
                               role="button"
                               id="dropdownFilesFiltersLink"
                               data-bs-toggle="dropdown"
                               aria-haspopup="true"
                               aria-expanded="false">
                                {{ trans('admin.filters') }}
                                <i class="fa-solid{{ $filter ? ' fa-check' : '' }}"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownFilesFiltersLink">
                                <a class="dropdown-item" href="{{ route('admin.files.manage', ['search' => $search]) }}">
                                    -
                                </a>
                                <a class="dropdown-item"
                                   href="{{ route('admin.files.manage', ['filter' => \App\Enums\FileStatus::Ready, 'search' => $search]) }}">
                                    {!! Helpers::filterSelectedMark($filter, \App\Enums\FileStatus::Ready) !!}
                                    {{ trans('files.ready') }}
                                </a>
                                <a class="dropdown-item"
                                   href="{{ route('admin.files.manage', ['filter' => \App\Enums\FileStatus::Processing, 'search' => $search]) }}">
                                    {!! Helpers::filterSelectedMark($filter, \App\Enums\FileStatus::Processing) !!}
                                    {{ trans('files.processing') }}
                                </a>
                                <a class="dropdown-item"
                                   href="{{ route('admin.files.manage', ['filter' => \App\Enums\FileStatus::Transcoding, 'search' => $search]) }}">
                                    {!! Helpers::filterSelectedMark($filter, \App\Enums\FileStatus::Transcoding) !!}
                                    {{ trans('files.transcoding') }}
                                </a>
                                <a class="dropdown-item"
                                   href="{{ route('admin.files.manage', ['filter' => \App\Enums\FileStatus::Failed, 'search' => $search]) }}">
                                    {!! Helpers::filterSelectedMark($filter, \App\Enums\FileStatus::Failed) !!}
                                    {{ trans('files.failed') }}
                                </a>
                            </div>
                        </div>

                        @can('upload', [\App\File::class, null, null])
                            <div class="upload-files">
                                <div id="rct-files"
                                     data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.create'), 'filenameLabel' => trans('files.filename.label'), 'maxNumberOfFiles' => 10, 'reloadOnModalClose' => true, 'note' => trans('messages.file.reload')]) }}'
                                ></div>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body table-responsive">
                    @if ($files->items())
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('files.name') }}</th>
                                <th>{{ trans('files.type') }}</th>
                                <th>{{ trans('files.size') }}</th>
                                <th>{{ trans('files.status') }}</th>
                                <th>{{ trans('files.space') }}</th>
                                <th>{{ trans('files.created_at') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($files->items() as $file)
                                @can('view', $file)
                                    <tr class="{{ $file->type }} {{ $file->status }} {{ Helpers::fileState($file) }}">
                                        <td title="{{ $file->name }}">{{ Helpers::truncate($file->name, 25) }}</td>
                                        <td>{{ Helpers::fileType($file->type) }}</td>
                                        <td>
                                            {{
                                                Helpers::isFileStatus($file, \App\Enums\FileStatus::Ready) ?
                                                Number::fileSize($file->size, precision: 2) : '-'
                                            }}
                                        </td>
                                        <td>{!! Helpers::fileStatusBadge($file) !!}</td>
                                        <td title="{{ $file->course?->name }}">
                                            {{ $file->course ? Helpers::truncate($file->course->name, 25) : '-' }}
                                        </td>
                                        <td>{{ $file->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td class="actions">
                                            @can('update', $file)
                                                <span>
                                                    <a href="{{ route('admin.files.edit', $file->id) }}"
                                                       data-bs-toggle="tooltip"
                                                       data-placement="top"
                                                       class="btn btn-primary"
                                                       title="{{ trans('files.edit') }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                </span>
                                            @endcan

                                            @can('download', $file)
                                                <span>
                                                    <a href="{{ route('files.download', ['file' => $file->id]) }}"
                                                       data-bs-toggle="tooltip"
                                                       data-placement="top"
                                                       class="btn btn-primary"
                                                       title="{{ trans('files.download') }}">
                                                        <i class="fa-solid fa-download"></i>
                                                    </a>
                                                </span>
                                            @endcan

                                            @can('url', $file)
                                                <span>
                                                    <a href="{{ Helpers::fileUrl($file->filename) }}"
                                                       target="_blank"
                                                       data-bs-toggle="tooltip"
                                                       data-placement="top"
                                                       class="btn btn-primary"
                                                       title="{{ trans('files.url') }}">
                                                        <i class="far fa-share-square"></i>
                                                    </a>
                                                </span>
                                            @endcan

                                            @can('forceDelete', $file)
                                                <span>
                                                    <form class="with-delete-confirm" method="post"
                                                          action="{{ route('admin.files.destroy', $file->id) }}">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-placement="top"
                                                                title="{{ trans('files.delete') }}">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </span>
                                            @endcan
                                        </td>
                                    </tr>
                                @endcan
                            @endforeach
                            </tbody>
                        </table>
                        {{ $files->onEachSide(1)->links() }}
                    @else
                        <p class="text-secondary text-center">
                            {{ trans('files.not_found') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endcan
@endsection
