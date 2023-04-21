@extends('layouts.app-admin')

@section('admin.content')
    <div id="create-course">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div><br />
        @endif
        <form method="post"
              action="{{ route('admin.courses.store') }}">
            @csrf
            <div class="col-12 mb-3">
                <label for="name" class="control-label form-label">{{ trans('courses.name') }}</label>
                <div>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control"
                           autofocus
                    >
                </div>
            </div>

            <div class="col-12 mb-3">
                <label for="description" class="form-label">{{ trans('courses.description') }}</label>
                <textarea class="form-control"
                          name="description"
                          id="description"
                          rows="3"></textarea>
            </div>

            <div class="mb-3">
                <a data-bs-toggle="collapse"
                   href="#collapseExternalId"
                   role="button"
                   aria-expanded="false"
                   aria-controls="collapseExternalId">
                    {{ trans('courses.create_from_moodle') }}
                </a>
                <i class="far fa-question-circle"
                   data-bs-toggle="tooltip"
                   data-placement="top"
                   title="{{ trans('courses.external.help') }}">
                </i>
            </div>
            <div class="col-12 mb-3 collapse" id="collapseExternalId">
                <input type="number"
                       name="external_id"
                       id="external_id"
                       class="form-control col-md-3"
                >
            </div>

            <button type="submit"
                    class="btn btn-primary">
                {{ trans('courses.create') }}
            </button>
        </form>
    </div>
@endsection

@section('scripts-footer')
    <script type="text/javascript">
        $('#external_id').on('input', function() {
            $('#description').prop('disabled', this.value.length);
            $('#name').prop('disabled', this.value.length);
        });
    </script>
@endsection
