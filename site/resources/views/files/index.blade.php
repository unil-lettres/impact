@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('title')
    {{ trans('files.files') }}
@endsection

@can('viewAny', [\App\File::class, $course])
    @can('upload', [\App\File::class, $course, null])
        @section('actions')
            @can('upload', [\App\File::class, $course, null])
                <div id="rct-files"
                     data='{{ json_encode(['locale' => Helpers::currentLocal(), 'maxNumberOfFiles' => 5, 'label' => trans('files.create'), 'course_id' => $course->id, 'reloadOnModalClose' => true, 'note' => trans('messages.file.reload')]) }}'
                ></div>
            @endcan
        @endsection
    @endcan
    @section('content')
            <div id="files">
                <div class="card">
                    <div class="card-body">
                        @if ($files->items())
                            <table class="table borderless" style="border-top: none !important;">
                                <thead>
                                    <tr>
                                        <th>{{ trans('files.name') }}</th>
                                        <th>{{ trans('files.type') }}</th>
                                        <th>{{ trans('files.size') }}</th>
                                        <th>{{ trans('files.status') }}</th>
                                        <th>{{ trans('files.used') }}</th>
                                        <th>{{ trans('files.created_at') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($files->items() as $file)
                                        @can('view', $file)
                                            <tr class="{{ $file->type }} {{ $file->status }} {{ Helpers::fileState($file) }}">
                                                <td>{{ Helpers::truncate($file->name) }}</td>
                                                <td>{{ Helpers::fileType($file->type) }}</td>
                                                <td>
                                                    @if(Helpers::isFileStatus($file, \App\Enums\FileStatus::Ready))
                                                        {{ Number::fileSize($file->size, precision: 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{!! Helpers::fileStatusBadge($file) !!}</td>
                                                <td>
                                                    <span style="cursor: pointer"
                                                        class="base-popover"
                                                        data-bs-html="true"
                                                        data-bs-toggle="popover"
                                                        data-bs-content='{{ Helpers::fileCards($file) }}'>
                                                        {{ trans_choice('cards.card(s)', $file->cards->count()) }}
                                                    </span>
                                                </td>
                                                <td>{{ $file->created_at->format('d/m/Y H:i:s') }}</td>
                                                <td class="actions">
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
                                                                action="{{ route('files.destroy', $file->id) }}">
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
    @endsection
@endcan
