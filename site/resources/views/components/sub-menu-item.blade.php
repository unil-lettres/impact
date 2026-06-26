@props(['href', 'active' => false])

<li class="nav-item me-4 mt-3 {{ $active ? 'border-bottom border-primary border-3' : '' }}">
    <a class="nav-link pb-1 p-0" href="{{ $href }}">{{ $slot }}</a>
</li>
