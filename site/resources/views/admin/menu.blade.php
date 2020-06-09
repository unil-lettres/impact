<div class="admin-menu">
    <ul class="nav justify-content-center">
        <li class="nav-item {{ Route::is('admin.users*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.users.manage') }}">{{ trans('admin.users') }}</a>
        </li>
        <li class="nav-item {{ Route::is('admin.invitations*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.invitations.manage') }}">{{ trans('admin.invitations') }}</a>
        </li>
        <li class="nav-item {{ Route::is('admin.courses*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.courses.manage') }}">{{ trans('admin.spaces') }}</a>
        </li>
        <li class="nav-item {{ Route::is('admin.files*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.files.manage') }}">{{ trans('admin.files') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" href="#">{{ trans('admin.mail_managers') }}</a>
        </li>
    </ul>
</div>
