<div class="course-configuration-menu">
    <ul class="nav justify-content-center">
        <li class="nav-item {{ Route::is('courses.configure') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure', $course->id) }}">{{ trans('courses.configuration') }}</a>
        </li>
        @can('viewAny', [\App\Tag::class, $course])
        <li class="nav-item {{ Route::is('courses.configure.tags*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure.tags', $course->id) }}">{{ trans('courses.tags') }}</a>
        </li>
        @endcan
        <li class="nav-item {{ Route::is('courses.configure.files*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure.files', $course->id) }}">{{ trans('files.files') }}</a>
        </li>
        <li class="nav-item {{ Route::is('courses.configure.states*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('courses.configure.states', $course->id) }}">{{ trans('states.states') }}</a>
        </li>
    </ul>
</div>
