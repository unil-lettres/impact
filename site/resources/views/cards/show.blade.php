@extends('layouts.app-base')

@section('content')
    <div id="card">
        @can('view', $card)
            @section('title')
                {{ $card->title }}
            @endsection

            @section('actions')
                @can('update', $card)
                    <a href="{{ route('cards.edit', $card->id) }}"
                       class="btn btn-primary float-right">
                        {{ trans('cards.configure') }}
                    </a>
                @endcan
            @endsection
            <hr>
            <div>
                <div class="row">
                    <div class="col-xl-5 col-lg-5 col-md-5 col-sm-12">
                        @include('cards.modules.source')
                        @include('cards.modules.theory')
                        @include('cards.modules.documents')
                    </div>
                    <div class="col-xl-7 col-lg-7 col-md-7 col-sm-12">
                        @include('cards.modules.transcript')
                        @include('cards.modules.exemplification')
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@section('scripts-head')
    <script type="text/javascript" src="{{ asset('js/vendor/@ckeditor/translations/en.js') }}"></script>
@endsection
