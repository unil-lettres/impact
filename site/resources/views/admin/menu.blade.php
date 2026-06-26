<ul class="nav">
    <x-sub-menu-item href="{{ route('admin.users.manage') }}" :active="Route::is('admin.users*')">
        {{ trans('admin.users') }}
    </x-sub-menu-item>
    <x-sub-menu-item href="{{ route('admin.invitations.manage') }}" :active="Route::is('admin.invitations*')">
        {{ trans('admin.invitations') }}
    </x-sub-menu-item>
    <x-sub-menu-item href="{{ route('admin.courses.manage') }}" :active="Route::is('admin.courses*')">
        {{ trans('admin.spaces') }}
    </x-sub-menu-item>
    <x-sub-menu-item href="{{ route('admin.files.manage') }}" :active="Route::is('admin.files*')">
        {{ trans('admin.files') }}
    </x-sub-menu-item>
    <x-sub-menu-item href="{{ route('admin.mailing') }}" :active="Route::is('admin.mailing*')">
        {{ trans('admin.mailing') }}
    </x-sub-menu-item>
</ul>
