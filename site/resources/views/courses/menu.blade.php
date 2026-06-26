<ul class="nav">
    @can('editConfiguration', $course)
        <x-sub-menu-item href="{{ route('courses.configure', $course->id) }}" :active="Route::is('courses.configure')">
            {{ trans('courses.configuration') }}
        </x-sub-menu-item>
    @endcan

    @can('viewAny', [\App\Tag::class, $course])
        <x-sub-menu-item href="{{ route('courses.configure.tags', $course->id) }}" :active="Route::is('courses.configure.tags*')">
            {{ trans('courses.tags') }}
        </x-sub-menu-item>
    @endcan

    @can('viewAny', [\App\User::class, $course])
        <x-sub-menu-item href="{{ route('courses.configure.registrations', $course->id) }}" :active="Route::is('courses.configure.registrations*')">
            {{ trans('users.registrations') }}
        </x-sub-menu-item>
    @endcan

    @can('viewAny', [\App\File::class, $course])
        <x-sub-menu-item href="{{ route('courses.configure.files', $course->id) }}" :active="Route::is('courses.configure.files*')">
            {{ trans('files.files') }}
        </x-sub-menu-item>
    @endcan

    @can('viewAny', [\App\State::class, $course])
        <x-sub-menu-item href="{{ route('courses.configure.states', $course->id) }}" :active="Route::is('courses.configure.states*')">
            {{ trans('states.states') }}
        </x-sub-menu-item>
    @endcan
</ul>
