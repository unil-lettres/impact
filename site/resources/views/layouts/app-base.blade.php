<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('includes.head')

        @yield('styles')
        @livewireStyles
        @yield('scripts-head')
    </head>
    <body>
        <div id='app'>
            @include('includes.header.header')
            @if(View::hasSection('menu'))
                <div class="mb-3 border-bottom">
                    <div class="container">
                        @yield('menu')
                    </div>
                </div>
            @endif

            <div id="content" class="container mt-4">
                @if(false
                    || View::hasSection('title')
                    || View::hasSection('actions')
                    || View::hasSection('sub-title')
                )
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="flex-fill">
                                <div class="h2 mb-0">@yield('title')</div>
                                @hasSection('sub-title')
                                    {{-- Using hasSection to avoid adding a margin (mt-1) if there is no content. --}}
                                    <div class="text-muted mt-1">@yield('sub-title')</div>
                                @endif
                            </div>
                            <div class="align-self-end flex-wrap d-flex gap-2">
                                @yield('actions')
                            </div>
                        </div>

                        <hr>
                @endif

                <div class="messages-content">
                    @include('includes.messages')
                </div>

                <div class="main-content">
                    @yield('content')
                </div>

                <div class="mt-6">
                    <footer>
                            @include('includes.footer')
                    </footer>
                </div>
            </div>
        </div>

        <!-- Adding manually Livewire's js assets -->
        @livewireScripts

        <!-- Adding custom js scripts globally -->
        @include('layouts.app-js')

        <!-- Adding custom js scripts on a view basis -->
        @yield('scripts-footer')
    </body>
</html>
