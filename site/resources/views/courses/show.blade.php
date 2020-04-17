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
                @unless ($folders->isEmpty())
                    <ul>
                        @foreach ($folders as $folder)
                            <li>
                                <a href="{{ route('folders.show', $folder->id) }}">[-]{{ $folder->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endunless
                @unless ($cards->isEmpty())
                    <ul>
                        @foreach ($cards as $card)
                            @can('view', $card)
                                <li>
                                    <a href="{{ route('cards.show', $card->id) }}">{{ $card->title }}</a>
                                    @can('delete', $card)
                                        <form class="with-delete-confirm" method="post" style="display: inline;"
                                              action="{{ route('cards.destroy', $card->id) }}">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-link"
                                                    style="color: red; padding: 0;">
                                                ({{ trans('cards.delete') }})
                                            </button>
                                        </form>
                                    @endcan
                                </li>
                            @endcan
                        @endforeach
                    </ul>
                @else
                    <p class="text-secondary">
                        {{ trans('cards.not_found') }}
                    </p>
                @endunless
            </div>
        @endcan
    </div>
@endsection
