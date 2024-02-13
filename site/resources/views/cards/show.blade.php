@extends('layouts.app-base')

@section('title')
    <livewire:card-title :card="$card" />
@endsection

@section('sub-title')
    <div>
        <span class="me-3">{{ trans('cards.state') }}: {{ $card->state?->name }}</span>
        <span class="me-3">{{ trans('cards.date') }}: {{ $card->options['presentation_date'] ?? '-' }} </span>
        <span>{{ trans('cards.tags') }}: {{ $card->tags->isEmpty() ? '-' : $card->tags->implode('name', ', ') }}</span>
    </div>
@endsection

@can('view', $card)
    @canany(['hide', 'update'], $card)
        @section('actions')
            @can('hide', $card)
                <button type="submit"
                        id="btn-hide-boxes"
                        class="btn btn-secondary"
                        data-bs-toggle="tooltip"
                        data-placement="top"
                        title="{{ trans('cards.hide_boxes') }}">
                    <i class="fa-solid fa-video"></i>
                </button>
            @endcan
            @can('update', $card)
                <a href="{{ route('cards.edit', $card->id) }}"
                    class="btn btn-primary">
                    {{ trans('cards.configure') }}
                </a>
            @endcan
        @endsection
    @endcanany

    @section('content')
        <div id="card">
                <div>
                    <div class="row">
                        <div class="col-xl-5 col-lg-5 col-md-5 col-sm-12">
                            @include('cards.show.box1', ['reference' => 'box1'])
                            @include('cards.show.box3', ['reference' => 'box3'])
                            @include('cards.show.box5', ['reference' => 'box5'])
                        </div>
                        <div class="col-xl-7 col-lg-7 col-md-7 col-sm-12">
                            @include('cards.show.box2', ['reference' => 'box2'])
                            @include('cards.show.box4', ['reference' => 'box4'])
                        </div>
                    </div>
                </div>
        </div>
    @endsection
@endcan

@section('scripts-head')
    <script type="text/javascript" src="{{ asset('js/vendor/@ckeditor/translations/en.js') }}"></script>
@endsection

@section('scripts-footer')
    <script type="text/javascript">
        // Hide or show boxes on button click
        $('#btn-hide-boxes').on('click', function() {
            $(this).toggleClass(['btn-primary', 'btn-secondary']);
            $(this).toggleClass('enabled');
            $('.hide-on-read-only').toggle();
        });
    </script>
    <script>
        document.addEventListener('livewire:init', () => {
            // Customizing Livewire page expiration behavior (avoid confirm() dialog on logout)
            // https://livewire.laravel.com/docs/javascript#customizing-page-expiration-behavior
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419) {
                        preventDefault()
                    }
                })
            })
        })
    </script>
@endsection
