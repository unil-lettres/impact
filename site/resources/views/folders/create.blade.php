@extends('layouts.app-base')

@section('content')
    <div id="create-invitation">
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
              action="{{ route('folders.store') }}">
            @csrf
            <div class="row">
                <div class="form-group col-md-12 col-lg-7">
                    <!-- // TODO: add translation -->
                    <label for="title" class="control-label">Titre</label>
                    <div>
                        <input id="title"
                               type="text"
                               class="form-control"
                               name="title"
                               required autofocus
                        >
                    </div>
                </div>

                <div class="form-group col-md-12 col-lg-5">
                    <!-- // TODO: add translation -->
                    <label for="parent_id" class="control-label">Emplacement</label>
                    <input id="parent_id" name="parent_id" type="hidden" value="">
                    <div id="rct-single-parent-select"
                         reference="parent_id"
                         data='{{ json_encode(['options' => $folders]) }}'
                    ></div>
                </div>
            </div>

            <input type="hidden" name="course_id" value="{{ $course->id }}" >

            <button type="submit"
                    class="btn btn-primary">
                <!-- // TODO: add translation -->
                Créer un dossier
            </button>
        </form>
    </div>
@endsection
