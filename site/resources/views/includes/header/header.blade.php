<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <span class="navbar-brand fs-2 border border-light fw-bold px-4 py-2"><a class="nav-link" href="{{ route('home') }}">Impact</a></span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-coll" aria-controls="navbar-coll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar-coll">
            @auth
                <div class="ms-3 me-auto">
                    <!-- Navbar left side -->
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <span class="breadcrumbs">
                                @isset($breadcrumbs)
                                    {!! \App\Helpers\Helpers::breadcrumbsHtml($breadcrumbs) !!}
                                @endisset
                            </span>
                        </li>
                    </ul>
                    @hasSection('navigation')
                        <div class="mt-1">
                            @yield('navigation')
                        </div>
                    @endif
                </div>
            @endauth

            <!-- Navbar right side -->
            <ul class="navbar-nav ms-auto">
                @auth
                    <!-- Authentication links -->
                    @include('includes.header.auth')
                @endauth

                <!-- Localization dropdown -->
                @include('includes.header.lang')
            </ul>
        </div>
    </div>
</nav>
