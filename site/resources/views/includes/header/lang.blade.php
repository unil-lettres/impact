<!-- Translation dropdown of the navbar -->
@php $locale = session()->get('locale'); @endphp

<li class="nav-item dropdown lang">
    <a id="navbarDropdownLang" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @switch($locale)
            @case('en')
            <img src="{{ asset('images/lang/en.png') }}" alt="en"> English
            @break
            @default
            <img src="{{ asset('images/lang/fr.png') }}" alt="fr"> Français
        @endswitch
        <span class="caret"></span>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownLang">
        <a class="dropdown-item" href="/lang/fr"><img src="{{asset('images/lang/fr.png')}}" alt="fr"> Français</a>
        <a class="dropdown-item" href="/lang/en"><img src="{{asset('images/lang/en.png')}}" alt="en"> English</a>
    </div>
</li>
