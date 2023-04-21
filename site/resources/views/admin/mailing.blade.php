@extends('layouts.app-admin')

@section('admin.content')
    <div id="mailing">
        @section('title')
            {{ trans('admin.mailing.title') }}
        @endsection
        <hr>

        <p>{{ trans('admin.mailing.description') }}</p>

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
              action="{{ route('admin.mailing.send') }}">
            @csrf
            <div class="col-12 mb-3">
                <label for="subject" class="control-label form-label">{{ trans('admin.mailing.subject') }}</label>
                <div>
                    <input type="text"
                           id="subject"
                           name="subject"
                           class="form-control col-md-6"
                           autofocus
                           value="{{ $subject }}"
                    >
                </div>
            </div>

            <div class="col-12 mb-3">
                <label for="content" class="form-label">{{ trans('admin.mailing.content') }}</label>
                <textarea class="form-control col-md-9"
                          name="content"
                          id="content"
                          rows="15">{{ $content }}</textarea>
            </div>

            <button type="submit"
                    class="btn btn-primary">
                {{ trans('admin.send') }}
            </button>
        </form>
    </div>
@endsection
