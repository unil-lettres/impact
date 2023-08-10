@extends('layouts.app-base')

@section('menu')
@include('courses.menu')
@endsection

@section('title')
    {{ trans('courses.tags') }} <span class="badge bg-secondary">{{ $tags->count() }}</span>
@endsection

@section('actions')
    @can('create', [\App\Tag::class, $course])
        <div class="float-end">
            <button
                type="submit"
                class="btn btn-primary "
                data-bs-toggle="modal"
                data-bs-target="#createTagModal"
            >
                {{ trans('tags.create') }}
            </button>
        </div>
        <div class="clearfix"></div>
    @endcan
    <hr>
@endsection

@section('content')
<div id="tags">
    @if ($errors->any())
        <div class="row">
            <div class="col">
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12 col-lg-{{ $clonableCourses->isEmpty() ? '12' : '8'}}">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <a href="{{route('courses.configure.tags', ['course' => $course, 'tag_order' => 'name', 'tag_direction' => $tagColumns['name']])}}" class="icon-link link-dark link-underline-opacity-0">
                                        {{ trans("tags.name") }}
                                        <i class="fa-solid fa-sort-{{['asc' => 'down', 'desc' => 'up'][$tagColumns['name']]}}"></i>
                                    </a>
                                </th>
                                <th scope="col" class="text-end">
                                    <a href="{{route('courses.configure.tags', ['course' => $course, 'tag_order' => 'cards_count', 'tag_direction' => $tagColumns['cards_count']])}}" class="icon-link link-dark link-underline-opacity-0">
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
                                        <span data-bs-toggle="tooltip" title="{{ trans('tags.edit') }}" data-placement="top">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-name="{{ $tag->name }}"
                                                data-bs-action="{{ route('tags.update', $tag->id) }}"
                                                data-bs-target="#editTagModal"
                                            >
                                                <i class="far fa-edit"></i>
                                            </button>
                                        </span>
                                        @endcan
                                        @can('delete', $tag)
                                        <span>
                                            <form class="with-delete-confirm" method="post" action="{{ route('tags.destroy', ['tag' => $tag->id]) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-danger" data-bs-toggle="tooltip" data-placement="top" title="{{ trans('tags.delete') }}">
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($clonableCourses->isNotEmpty())
        <div class="col-md-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <span class="title">
                        {{ trans('tags.clone_tags_label') }}
                    </span>
                </div>
                <div class="card-body">
                    <div>
                        <p>
                            {{ trans('tags.clone_tags_help') }}
                        </p>
                        <form method="post" action="{{ route('courses.clone.tags', [$course->id]) }}" class="row row-cols-md-auto g-2 justify-content-end">
                            @method('POST')
                            @csrf
                            <div class="col-12 flex-fill">
                                <select name="course_id" class="form-select" aria-label="Courses tags source">
                                    @foreach ($clonableCourses as $fromCourse)
                                        <option value="{{ $fromCourse->id }}">
                                            {{ $fromCourse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 align-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ trans('tags.clone_tags_action') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
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
                    <input type="hidden" name="course_id" value="{{ $course->id }}" />
                    <div class="modal-body">
                        <input name="name" type="text" class="form-control" autocomplete="off">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ trans('tags.modal_close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ trans('tags.edit_modal_apply') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="createTagModal" tabindex="-1" aria-labelledby="createTagModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="createTagModalLabel">{{ trans('tags.create') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('tags.store') }}">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}" />
                    <div class="modal-body">
                        <input name="name" type="text" class="form-control" autocomplete="off" placeholder="{{ trans('tags.name') }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ trans('tags.modal_close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ trans('tags.create_modal_apply') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts-footer')
<script>
    (function() {
        function attachShownEvent(element) {
            element.addEventListener(
                'shown.bs.modal',
                (evt) => evt.target.querySelector('input[name="name"]').select(),
            );
        }

        const editTagModal = document.getElementById('editTagModal');

        attachShownEvent(document.getElementById('createTagModal'));
        attachShownEvent(editTagModal);

        editTagModal.addEventListener('show.bs.modal', evt => {
            const { relatedTarget, target } = evt;
            const name = relatedTarget.getAttribute('data-bs-name');
            const formAction = relatedTarget.getAttribute('data-bs-action');

            Object.assign(target.querySelector('input[name="name"]'), {
                placeholder: name,
                value: name,
            });

            target.querySelector('form').action = formAction;
        });

    })();
</script>
@endsection
