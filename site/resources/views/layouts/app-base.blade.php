<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('includes.head')
        @yield('styles')
    </head>
    <body>
        <div id='app'>
            @include('includes.header.header')
            <div id="content" class="container">
                <div class="container title-content">
                    <div class="page-title">
                        <h1>@yield('title')</h1>
                    </div>
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
        @yield('scripts')
    </body>
</html>
