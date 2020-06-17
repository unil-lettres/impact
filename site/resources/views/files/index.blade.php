@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@stop

@section('content')
    @can('viewAny', [\App\File::class, $course])
        <div id="files">
            <div class="card">
                <div class="card-header">
                    <span class="title">{{ trans('files.files') }} <span class="badge badge-secondary">{{ $files->total() }}</span></span>

                    @can('upload', [\App\File::class, $course, null])
                        <input id="course_id" name="course_id" type="hidden" value="{{ $course->id }}">

                        <div id="rct-uploader"
                             class="float-right"
                             data='{{ json_encode(['locale' => Helpers::currentLocal(), 'maxFileSize' => 1000000000, 'maxNumberOfFiles' => 5, 'modal' => true, 'label' => trans('files.create')]) }}'
                        ></div>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($files->items())
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('files.name') }}</th>
                                <th>{{ trans('files.type') }}</th>
                                <th>{{ trans('files.status') }}</th>
                                <th>{{ trans('files.used') }}</th>
                                <th>{{ trans('files.created_at') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($files->items() as $file)
                                @can('view', $file)
                                    <tr>
                                        <td>{{ Helpers::truncate($file->name) }}</td>
                                        <td>{{ Helpers::fileType($file->type) }}</td>
                                        <td>{!! Helpers::fileStatusBadge($file->status) !!}</td>
                                        <td>
                                            <span style="cursor: pointer"
                                                  class="base-popover"
                                                  data-toggle="popover"
                                                  data-content='{!! Helpers::fileCards($file) !!}'>
                                                {{ trans_choice('cards.card(s)', $file->cards->count()) }}
                                            </span>
                                        </td>
                                        <td>{{ $file->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td class="actions">
                                            @if(Helpers::isFileReady($file))
                                                <span>
                                                <a href="{{ Helpers::fileUrl($file->filename) }}"
                                                   target="_blank"
                                                   data-toggle="tooltip"
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
                                                            data-toggle="tooltip"
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
