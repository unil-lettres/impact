@extends('layouts.app-admin')

@section('admin.content')
    @can('manage', \App\File::class)
        <div id="files">
            <div class="card">
                <div class="card-header">
                    <span class="title">{{ trans('files.files') }} <span class="badge bg-secondary">{{ $files->total() }}</span></span>

                    @can('upload', [\App\File::class, null, null])
                        <div class="float-end">
                            <div id="rct-files" class="float-end"
                                 data='{{ json_encode(['locale' => Helpers::currentLocal(), 'label' => trans('files.create'), 'maxNumberOfFiles' => 10, 'reloadOnModalClose' => true, 'note' => trans('messages.file.reload')]) }}'
                            ></div>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
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
                                        <td>{{ Helpers::truncate($file->name, 25) }}</td>
                                        <td>{{ Helpers::fileType($file->type) }}</td>
                                        <td>
                                            {{
                                                Helpers::isFileStatus($file, \App\Enums\FileStatus::Ready) ?
                                                Number::fileSize($file->size, precision: 2) : '-'
                                            }}
                                        </td>
                                        <td>{!! Helpers::fileStatusBadge($file) !!}</td>
                                        <td>{{ $file->course ? Helpers::truncate($file->course->name, 25) : '-' }}</td>
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
                                            @if(Helpers::isFileStatus($file, \App\Enums\FileStatus::Ready))
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
                                            @endif
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
                        <p class="text-secondary">
                            {{ trans('files.not_found') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endcan
@endsection
