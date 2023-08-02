@extends('layouts.app-base')

@section('content')
    <div id="course">
        @can('view', $course)
            @section('title')
                {{ $course->name }}
            @endsection

            @section('actions')
                @can('create', [\App\Card::class, $course])
                    <a href="{{ route('cards.create', ['course' => $course->id]) }}"
                       class="btn btn-primary float-end">
                        {{ trans('cards.create') }}
                    </a>
                @endcan

                @can('create', [\App\Folder::class, $course])
                    <a href="{{ route('folders.create', ['course' => $course->id]) }}"
                       class="btn btn-primary float-end me-1">
                        Créer un dossier
                    </a>
                @endcan

                @can('editConfiguration', $course)
                    <a href="{{ route('courses.configure', $course->id) }}"
                       class="btn btn-primary float-end me-1">
                        {{ trans('courses.configure') }}
                    </a>
                @endcan
            @endsection
            <hr>
            <div>
                @include('shared.folders')
                @include('shared.cards')
            </div>
        @endcan
    </div>
@endsection
