@extends('layouts.app-base')

@section('menu')
    @include('courses.menu')
@endsection

@section('content')
    <div id="configure-course">
        @section('title')
            {{ trans('courses.configure') }}
        @endsection
        @section('actions')
            <div class="float-end">
                @can('archive', $course)
                    <span class="me-1">
                        <form class="with-archive-confirm d-inline"
                            method="post"
                            action="{{ route('courses.archive', $course->id) }}">
                            @method('PUT')
                            @csrf
                            <button type="submit"
                                    class="btn btn-secondary"
                                    data-bs-toggle="tooltip"
                                    data-placement="top"
                                    title="{{ trans('messages.course.archive.info') }}">
                                <i class="far fa-folder-open"></i>
                                {{ trans('courses.archive') }}
                            </button>
                        </form>
                    </span>
                @endcan
                @can('disable', $course)
                    <span>
                        <form class="with-delete-confirm d-inline"
                            method="post"
                            action="{{ route('courses.disable', $course->id) }}">
                            @method('DELETE')
                            @csrf
                            <input id="redirect" name="redirect" type="hidden" value="home">
                            <button type="submit"
                                    class="btn btn-danger"
                                    data-bs-toggle="tooltip"
                                    data-placement="top"
                                    title="{{ trans('messages.course.delete.info') }}">
                                <i class="far fa-trash-alt"></i>
                                {{ trans('courses.delete') }}
                            </button>
                        </form>
                    </span>
                @endcan
            </div>
        @endsection
        <div class="clearfix"></div>
        <hr>

        @if ($errors->any())
            <div class="row">
                <div class="col">
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <span class="title">{{ trans('enrollments.enrollments') }}</span>
                    </div>
                    <div class="card-body">
                        {{ trans('enrollments.as_teacher') }}
                        <div id="rct-multi-user-teacher-select"
                             data='{{ json_encode(['record' => $course, 'role' => $teacherRole, 'options' => $users, 'defaults' => $usersAsTeacher, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                        ></div>

                        <hr>

                        {{ trans('enrollments.as_student') }}
                        <div id="rct-multi-user-student-select"
                             data='{{ json_encode(['record' => $course, 'role' => $studentRole, 'options' => $users, 'defaults' => $usersAsStudent, 'isDisabled' => Helpers::isCourseExternal($course)]) }}'
                        ></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="title">
                            {{ trans('courses.transcription.type') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p>{{ trans('courses.transcription.type.help') }}</p>
                        <!-- TODO: add transcription type -->
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <span class="title">
                            {{ trans('courses.tags') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p>{{ trans('courses.tags.help') }}</p>
                        @can('create', [\App\Tag::class, $course])
                            <div class="row row-cols-lg-auto g-2 mt-1">
                                <div class="col-12">
                                    <button class="btn btn-primary w-100"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseCloneTags"
                                            aria-expanded="false"
                                            aria-controls="collapseCloneTags">
                                        {{ trans('tags.clone_tags_label') }}
                                    </button>
                                </div>
                                <div class="col-12">
                                    <div class="collapse" id="collapseCloneTags">
                                        <div class="card card-body mt-1">
                                            <p>
                                                {{ trans('tags.clone_tags_help') }}
                                            </p>
                                            <form method="post"
                                                  action="{{ route('courses.clone.tags', [$course->id]) }}"
                                                  class="row row-cols-md-auto g-2">
                                                @method('POST')
                                                @csrf
                                                <div class="col-12 flex-fill">
                                                    <select name="course_id" class="form-select" aria-label="Courses tags source">
                                                        <option value="" selected>
                                                            {{ trans('tags.clone_tags_courses_list') }}
                                                        </option>
                                                        @foreach ($allCourses as $fromCourse)
                                                            @continue($course->id === $fromCourse->id)
                                                            <option value="{{ $fromCourse->id }}">
                                                                {{ $fromCourse->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit"
                                                            class="btn btn-primary w-100"
                                                            disabled>
                                                        {{ trans('tags.clone_tags_action') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endcan
                        <hr />
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <a href="{{route('courses.configure', ['course' => $course, 'tag_order' => 'name', 'tag_direction' => $tagColumns['name']])}}"
                                            class="icon-link link-dark link-underline-opacity-0">
                                            {{ trans("tags.name") }}
                                            <i class="fa-solid fa-sort-{{['asc' => 'down', 'desc' => 'up'][$tagColumns['name']]}}"></i>
                                        </a>
                                    </th>
                                    <th scope="col" class="text-end">
                                        <a href="{{route('courses.configure', ['course' => $course, 'tag_order' => 'cards_count', 'tag_direction' => $tagColumns['cards_count']])}}"
                                            class="icon-link link-dark link-underline-opacity-0">
                                            {{ trans("tags.cards_count") }}
                                            <i class="fa-solid fa-sort-{{['asc' => 'down', 'desc' => 'up'][$tagColumns['cards_count']]}}"></i>
                                        </a>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tags as $tag)
                                    <tr>
                                        <td class="align-middle">{{ $tag->name }}</td>
                                        <td class="text-end align-middle">{{ $tag->cards_count }}</td>
                                        <td class="align-middle">
                                            <div class="d-flex justify-content-end gap-1">
                                                @can('update', $tag)
                                                    <span data-bs-toggle="tooltip"
                                                            title="{{ trans('tags.edit') }}"
                                                            data-placement="top">
                                                        <button type="button"
                                                                class="btn btn-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-name="{{ $tag->name }}"
                                                                data-bs-action="{{ route('tags.update', ['tag' => $tag->id]) }}"
                                                                data-bs-target="#editTagModal">
                                                            <i class="far fa-edit"></i>
                                                        </button>
                                                    </span>
                                                @endcan
                                                @can('delete', $tag)
                                                    <span>
                                                        <form class="with-delete-confirm"
                                                                method="post"
                                                                action="{{ route('tags.destroy', ['tag' => $tag->id]) }}">
                                                            @method('DELETE')
                                                            @csrf
                                                            <button type="submit"
                                                                    class="btn btn-danger"
                                                                    data-bs-toggle="tooltip"
                                                                    data-placement="top"
                                                                    title="{{ trans('tags.delete') }}">
                                                                <i class="far fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </span>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="3">{{ trans('tags.empty') }}</td>
                                </tr>
                                @endforelse
                                @can('create', [\App\Tag::class, $course])
                                <tr>
                                    <td colspan=3>
                                        <form method="post"
                                                action="{{ route('tags.store') }}"
                                                class="row row-cols-md-auto g-2">
                                            @method('POST')
                                            @csrf
                                            <input type="hidden" name="course_id" value="{{ $course->id }}" >
                                            <div class="col-12 flex-fill">
                                                <input
                                                    name="name"
                                                    type="text"
                                                    class="form-control"
                                                    autocomplete="off"
                                                    placeholder="{{ trans('tags.create_placeholder') }}">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit"
                                                        class="btn btn-primary w-100">
                                                    {{ trans('tags.create') }}
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endcan
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editTagModal" tabindex="-1" aria-labelledby="editTagModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editTagModalLabel">{{ trans('tags.edit_modal_title') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <form method="post">
                    @method('PUT')
                    @csrf
                    <div class="modal-body">
                        <input
                            name="name"
                            type="text"
                            class="form-control"
                            autocomplete="off">
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            {{ trans('tags.edit_modal_close') }}
                        </button>
                        <button type="submit"
                                class="btn btn-primary"
                                disabled>
                            {{ trans('tags.edit_modal_apply') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @once
        <script>
            (function() {
                const editTagModal = document.getElementById('editTagModal');
                const editTagModalForm = editTagModal.querySelector('form');
                const editTagModalInput = editTagModal.querySelector('.modal-body input[name="name"]');
                const editTagModalSubmit = editTagModal.querySelector('.modal-footer .btn-primary');
                const collapseCloneTags = document.getElementById('collapseCloneTags');
                const collapseCloneTagsSelect = collapseCloneTags.querySelector('select');
                const collapseCloneTagsSubmit = collapseCloneTags.querySelector('.btn-primary');

                editTagModal.addEventListener('shown.bs.modal', () => {
                    editTagModalInput.select();
                });

                editTagModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const name = button.getAttribute('data-bs-name');
                    const formAction = button.getAttribute('data-bs-action');

                    editTagModalInput.placeholder = name;
                    editTagModalInput.value = name;
                    editTagModalForm.action = formAction;
                });

                editTagModalInput.addEventListener('input', event => {
                    const name = event.target.value;
                    const placeholder = event.target.placeholder;

                    editTagModalSubmit.disabled = !name || name.toLowerCase() === placeholder.toLowerCase();
                });

                collapseCloneTagsSelect.addEventListener('input', event => {
                    collapseCloneTagsSubmit.disabled = !event.target.value;
                });
            })();
        </script>
    @endonce
@endsection

