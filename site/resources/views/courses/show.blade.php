@extends('layouts.app-base')

@section('title')
    {{ $course->name }}
@endsection

@can('view', $course)
    @if (false
        || Auth::user()->can('configure', $course)
        || Auth::user()->can('create', [\App\Folder::class, $course])
        || Auth::user()->can('create', [\App\Card::class, $course])
    )
        @section('actions')
            @can('editConfiguration', $course)
                <a href="{{ route('courses.configure', $course->id) }}"
                   class="btn btn-primary me-1">
                    {{ trans('courses.configure') }}
                </a>
            @endcan
            @can('create', [\App\Folder::class, $course])
                <a href="{{ route('folders.create', ['course' => $course->id]) }}"
                   class="btn btn-primary me-1">
                    Créer un dossier
                </a>
            @endcan
            @can('create', [\App\Card::class, $course])
                <a href="{{ route('cards.create', ['course' => $course->id]) }}"
                   class="btn btn-primary">
                    {{ trans('cards.create') }}
                </a>
            @endcan
        @endsection
    @endif
    @section('content')
        <div id="course">
            <div>
                @include('shared.folders')
                @include('shared.cards')
            </div>
        </div>
    @endsection
@endcan
