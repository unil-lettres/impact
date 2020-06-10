@extends('layouts.app-base')

@section('content')
    <div id="card">
        @can('view', $card)
            @section('title')
                {{ $card->title }}

                @can('update', $card)
                    <a href="{{ route('cards.edit', $card->id) }}"
                       class="btn btn-primary float-right">
                        {{ trans('cards.configure') }}
                    </a>
                @endcan
            @endsection
            <hr>
            <div>
                <input id="card_id" name="card_id" type="hidden" value="{{ $card->id }}">
                <input id="course_id" name="course_id" type="hidden" value="{{ $course->id }}">

                <div id="rct-uploader"
                     data='{{ json_encode(['locale' => Helpers::currentLocal(), 'maxFileSize' => 1000000000, 'modal' => true]) }}'
                ></div>
            </div>
        @endcan
    </div>
@endsection
