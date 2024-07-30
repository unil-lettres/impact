<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <span class="navbar-brand"><a class="nav-link" href="{{ route('home') }}">Impact</a></span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-coll" aria-controls="navbar-coll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar-coll">
            @auth
                <div class="me-auto">
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
                        <div>
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

<div class="container">
    @yield('admin.menu')
</div>
