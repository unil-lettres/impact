<div class="course-configuration-menu">
    <ul class="nav justify-content-center">
        <li class="nav-item {{ Route::is('courses.configure') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure', $course->id) }}">{{ trans('courses.configuration') }}</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.files*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure.files', $course->id) }}">{{ trans('files.files') }}</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.state*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure.states', $course->id) }}">{{ trans('states.states') }}</a>
        </li>
    </ul>
</div>
