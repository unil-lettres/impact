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
                       class="btn btn-primary float-end">
                        {{ trans('cards.configure') }}
                    </a>
                @endcan
                @can('hide', $card)
                    <button type="submit"
                            id="btn-hide-boxes"
                            class="btn btn-danger float-end me-1"
                            data-bs-toggle="tooltip"
                            data-placement="top"
                            title="{{ trans('cards.hide_boxes') }}">
                        <i class="far fa-eye-slash"></i>
                    </button>
                @endcan
            @endsection
            <hr>
            <div>
                <div class="row">
                    <div class="col-xl-5 col-lg-5 col-md-5 col-sm-12">
                        @include('cards.show.box1', ['reference' => 'box1'])
                        @include('cards.show.box3', ['reference' => 'box3'])
                        @include('cards.show.box5', ['reference' => 'box5'])
                    </div>
                    <div class="col-xl-7 col-lg-7 col-md-7 col-sm-12">
                        @include('cards.show.box2', ['reference' => 'box2'])
                        @include('cards.show.box4', ['reference' => 'box4'])
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@section('scripts-head')
    <script type="text/javascript" src="{{ asset('js/vendor/@ckeditor/translations/en.js') }}"></script>
@endsection

@section('scripts-footer')
    <script type="text/javascript">
        // Hide or show boxes on button click
        $('#btn-hide-boxes').on('click', function() {
            $(this).toggleClass(['btn-danger', 'btn-success']);
            $(this).find('i').toggleClass(['fa-eye-slash', 'fa-eye']);
            $('.hidden').toggle();
        });
    </script>
@endsection
