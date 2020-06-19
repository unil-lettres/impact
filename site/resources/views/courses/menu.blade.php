<div class="course-configuration-menu">
    <ul class="nav justify-content-center">
        <li class="nav-item {{ Route::is('courses.configure') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure', $course->id) }}">{{ trans('courses.configuration') }}</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.files*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure.files', $course->id) }}">{{ trans('files.files') }}</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.status*') ? 'active' : '' }}">
            <a class="nav-link disabled" href="#">{{ trans('files.status') }}</a>
        </li>
    </ul>
</div>
