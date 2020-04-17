@extends('layouts.app-base')

@section('content')
    <div id="course">
        @can('view', $course)
            @section('title')
                {{ $course->name }}

                @can('create', [\App\Card::class, $course])
                    <a href="{{ route('cards.create', ['course' => $course->id]) }}"
                       class="btn btn-primary float-right">
                        {{ trans('cards.create') }}
                    </a>
                @endcan

                @can('create', [\App\Folder::class, $course])
                    <a href="{{ route('folders.create', ['course' => $course->id]) }}"
                       class="btn btn-primary float-right mr-1">
                        Créer un dossier
                    </a>
                @endcan

                @can('configure', $course)
                    <a href="{{ route('courses.configure', $course->id) }}"
                       class="btn btn-primary float-right mr-1">
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
