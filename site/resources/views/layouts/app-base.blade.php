<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('includes.head')

        @yield('styles')
        @yield('scripts-head')
    </head>
    <body>
        <div id='app'>
            @include('includes.header.header')

            <div id="content" class="container">
                @yield('menu')

                <div class="container title-content">
                    <span class="page-title h2">
                        @yield('title')
                    </span>

                    @yield('actions')
                </div>

                <div class="container messages-content">
                    @include('includes.messages')
                </div>

                <div class="container main-content">
                    @yield('content')
                </div>

                <div class="container footer-content">
                    <footer>
                            @include('includes.footer')
                    </footer>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
        @include('layouts.app-js')
        @yield('scripts-footer')
    </body>
</html>
