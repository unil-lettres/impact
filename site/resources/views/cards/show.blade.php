@extends('layouts.app-base')

@section('title')
    <livewire:card-title :card="$card" />
@endsection

@section('sub-title')
    <div class="d-flex gap-3 flex-column flex-md-row">
        <span>{{ trans('cards.state') }}: {{ $card->state?->name }}</span>
        <span>{{ trans('cards.date') }}: {{ $card->options['presentation_date'] ?? '-' }} </span>
        <x-cards.tags :card="$card"/>
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
                    <div class="col-xxl-6 col-xl-5 col-lg-4 col-md-12">
                        <div id="lg-box1">
                            <div id="wrapper-box1">
                                @include('cards.show.box1', ['reference' => 'box1'])
                            </div>
                        </div>
                        <div id="lg-box3">
                            <div id="wrapper-box3">
                                @include('cards.show.box3', ['reference' => 'box3'])
                            </div>
                        </div>
                        <div id="lg-box5">
                            <div id="wrapper-box5">
                                @include('cards.show.box5', ['reference' => 'box5'])
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-12">
                        <div id="lg-box2">
                            <div id="wrapper-box2">
                                @include('cards.show.box2', ['reference' => 'box2'])
                            </div>
                        </div>
                        <div id="lg-box4">
                            <div id="wrapper-box4">
                                @include('cards.show.box4', ['reference' => 'box4'])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div id="md-box1"></div>
                        <div id="md-box2"></div>
                        <div id="md-box3"></div>
                        <div id="md-box4"></div>
                        <div id="md-box5"></div>
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
        });

        // Process boxes layout. On small screen, we must reorganize the boxes
        // to show them in a single column with a logical order.
        (function() {
            const breakPoint = getComputedStyle(document.body)
                .getPropertyValue('--bs-breakpoint-lg');

            const breakPointWidth = parseInt(breakPoint, 10);

            let currentBreakpoint = null;

            const processBoxesLayout = () => {
                const prefix = window.innerWidth < breakPointWidth ? 'md' : 'lg';

                if (currentBreakpoint === prefix) {
                    // No change in breakpoint, no need to reorganize the boxes.
                    return;
                }

                currentBreakpoint = prefix;

                // Move the boxes to the correct parent element according to the
                // breakpoint (md or lg).
                for (let i = 1; i <= 5; i++) {
                    const wrapperBox = document.getElementById(`wrapper-box${i}`);
                    document
                        .getElementById(`${prefix}-box${i}`)
                        .replaceChildren(wrapperBox);
                }
            }

            window.addEventListener('resize', () => processBoxesLayout());
            processBoxesLayout();
        }());
    </script>
    @stack("scripts-boxes")
@endsection
