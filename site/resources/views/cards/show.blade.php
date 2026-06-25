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

@section('navigation')
    <a
        class="icon-link icon-link-hover text-light text-decoration-none @if (!$previousCard) disabled @endif"
        href="{{ $previousCard ? route('cards.show', $previousCard->id) : '#' }}"
        dusk="navigation-previous-card"
    >
        <i class="fas fa-arrow-left icon-link-hover-left"></i>
        {{ trans('cards.navigation.previous') }}
    </a>
    <span class="text-light">|</span>
    <a
        class="icon-link icon-link-hover text-light text-decoration-none @if (!$nextCard) disabled @endif"
        href="{{ $nextCard ? route('cards.show', $nextCard->id) : '#' }}"
        dusk="navigation-next-card"
    >
        {{ trans('cards.navigation.next') }}
        <i class="fas fa-arrow-right"></i>
    </a>
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
                    <i class="fas fa-video"></i>
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

        {{-- Modal: confirm before leaving the page --}}
        <div class="modal fade" id="leavePageModal" tabindex="-1" aria-labelledby="leavePageModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leavePageModalLabel">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            {{ trans('cards.leave_page.title') }}
                        </h5>
                    </div>
                    <div class="modal-body">
                        {{ trans('cards.leave_page.body') }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="leave-continue-editing">
                            {{ trans('cards.leave_page.continue_editing') }}
                        </button>
                        <button type="button" class="btn btn-danger" id="leave-without-saving">
                            {{ trans('cards.leave_page.leave_without_saving') }}
                        </button>
                        <button type="button" class="btn btn-primary" id="leave-save-and-quit">
                            <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true" id="leave-save-spinner"></span>
                            {{ trans('cards.leave_page.save_and_leave') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@endcan

@section('scripts-footer')
    <script type="module">
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

    <script>
        (function () {
            let clickedLink = null;
            const leavePageModalEl = document.getElementById('leavePageModal');
            const saveAndQuitBtn = document.getElementById('leave-save-and-quit');

            if (!leavePageModalEl) return;

            leavePageModalEl.addEventListener('shown.bs.modal', function (e) {
                saveAndQuitBtn.focus();
            });

            function getLeavePageModal() {
                return bootstrap.Modal.getOrCreateInstance(leavePageModalEl);
            }

            function isAnyBoxEditing() {
                return Object.values(window.editors || {}).some(Boolean);
            }

            // Intercept all link clicks on the page
            document.addEventListener('click', function (event) {
                const link = event.target.closest('a[href]');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href === '#') return;

                if (!isAnyBoxEditing()) return;

                event.preventDefault();
                event.stopPropagation();
                clickedLink = link;
                getLeavePageModal().show();
            }, true);

            document.getElementById('leave-continue-editing').addEventListener('click', function () {
                getLeavePageModal().hide();
                clickedLink.focus();
                clickedLink = null;
            });

            document.getElementById('leave-without-saving').addEventListener('click', function () {
                getLeavePageModal().hide();
                clickedLink.focus();
                const url = clickedLink.href;
                clickedLink = null;
                if (url) window.location.href = url;
            });

            saveAndQuitBtn.addEventListener('click', async function () {
                const btn = this;
                const spinner = document.getElementById('leave-save-spinner');
                btn.disabled = true;
                spinner.classList.remove('d-none');

                const savers = window.boxSavers || {};
                const editingBoxes = Object.entries(window.editors || {})
                    .filter(([, editing]) => editing)
                    .map(([ref]) => ref);

                try {
                    await Promise.all(editingBoxes.map(ref => savers[ref] ? savers[ref]() : Promise.resolve()));
                    getLeavePageModal().hide();
                    clickedLink.focus();
                    const url = clickedLink.href;
                    clickedLink = null;
                    if (url) window.location.href = url;
                } catch (error) {
                    console.error('Save failed', error);
                    btn.disabled = false;
                    spinner.classList.add('d-none');
                }
            });
        }());
    </script>
@endsection
