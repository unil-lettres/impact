<div class="course-configuration-menu">
    <ul class="nav justify-content-center">
        <li class="nav-item {{ Route::is('courses.configure') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure', $course->id) }}">Réglages</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.files*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure.files', $course->id) }}">Médias</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.status*') ? 'active' : '' }}">
            <a class="nav-link disabled" href="#">Status</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.users*') ? 'active' : '' }}">
            <a class="nav-link disabled" href="#">Users</a>
        </li>
    </ul>
</div>
