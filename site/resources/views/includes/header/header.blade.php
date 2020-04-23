<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <span class="navbar-brand"><a class="nav-link" href="{{ route('home') }}">Impact</a></span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-coll" aria-controls="navbar-coll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar-coll">
            @auth
                <!-- Navbar left side -->
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <span class="breadcrumbs">
                            @isset($breadcrumbs)
                                {!! \App\Helpers\Helpers::breadcrumbsHtml($breadcrumbs) !!}
                            @endisset
                        </span>
                    </li>
                </ul>
            @endauth

            <!-- Navbar right side -->
            <ul class="navbar-nav ml-auto">
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
