@extends('layouts.app-base')

@section('content')
    <div id="card">
        @can('view', $card)
            @section('title')
                {{ $card->title }}
            @endsection

            @section('actions')
                @can('update', $card)
                    <a href="{{ route('cards.edit', $card->id) }}"
                       class="btn btn-primary float-right">
                        {{ trans('cards.configure') }}
                    </a>
                @endcan
            @endsection
            <hr>
            <div>
                @can('upload', [\App\File::class, $course, $card])
                    <input id="card_id" name="card_id" type="hidden" value="{{ $card->id }}">
                    <input id="course_id" name="course_id" type="hidden" value="{{ $course->id }}">

                    <div id="rct-uploader"
                         data='{{ json_encode(['locale' => Helpers::currentLocal(), 'modal' => true, 'label' => trans('files.create')]) }}'
                    ></div>
                @endcan
                @if($card->file && Helpers::isFileReady($card->file))
                    <div class="mt-2">
                        @if($card->file->type === 'video')
                            <video width="{{ $card->file->width }}" height="{{ $card->file->height }}" controls>
                                <source src="{{ Helpers::fileUrl($card->file->filename) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @elseif($card->file->type === 'audio')
                            <audio controls>
                                <source src="{{ Helpers::fileUrl($card->file->filename) }}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        @endif
                    </div>
                @endif
            </div>
        @endcan
    </div>
@endsection
