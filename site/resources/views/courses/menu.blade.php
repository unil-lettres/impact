<div class="course-configuration-menu">
    <ul class="nav justify-content-center">
        @can('editConfiguration', $course)
            <li class="nav-item {{ Route::is('courses.configure') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('courses.configure', $course->id) }}">{{ trans('courses.configuration') }}</a>
            </li>
        @endcan

        @can('viewAny', [\App\User::class, $course])
            <li class="nav-item {{ Route::is('courses.configure.registrations*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('courses.configure.registrations', $course->id) }}">{{ trans('users.registrations') }}</a>
            </li>
        @endcan

        @can('viewAny', [\App\File::class, $course])
            <li class="nav-item {{ Route::is('courses.configure.files*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('courses.configure.files', $course->id) }}">{{ trans('files.files') }}</a>
            </li>
        @endcan

        @can('viewAny', [\App\State::class, $course])
            <li class="nav-item {{ Route::is('courses.configure.states*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('courses.configure.states', $course->id) }}">{{ trans('states.states') }}</a>
            </li>
        @endcan
    </ul>
</div>
