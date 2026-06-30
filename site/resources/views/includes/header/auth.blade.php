@if (Auth::user()->admin && !Route::is('admin*'))
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.index') }}">
            <i class="fas fa-screwdriver-wrench"></i>
            {{ trans('login.admin') }}
        </a>
    </li>
@endif
<li class="nav-item dropdown auth">
    <a id="navbarDropdownAuth" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
        <i class="fas fa-circle-user"></i>
        {{ Auth::user()->name }} <span class="caret"></span>
    </a>

    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownAuth">
        <a class="dropdown-item profile" href="{{ route('users.profile', Auth::user()->id) }}">
            <i class="fas fa-user-pen me-1"></i>
            {{ trans('auth.profile') }}
        </a>

        @can('viewAny', \App\Invitation::class)
            <a class="dropdown-item invitations" href="{{ route('invitations.index') }}">
                <i class="fa-solid fa-paper-plane me-1"></i>
                {{ trans('invitations.manage') }}
            </a>
        @endcan

        <a class="dropdown-item logout" href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa-solid fa-right-from-bracket me-1"></i>
            {{ trans('login.logout') }}
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</li>
