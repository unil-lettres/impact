@extends('layouts.app-base')

@section('content')
    <div id="configure-card">
        @can('update', $card)
            @section('title')
                {{ trans('cards.configure') }}
            @endsection
            <hr>

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
                  action="{{ route('cards.update', $card->id) }}">
                @csrf
                @method('PUT')


                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                        @include('cards.edit.box1', ['reference' => 'box1'])
                        @include('cards.edit.box2', ['reference' => 'box2'])
                        @include('cards.edit.box3', ['reference' => 'box3'])
                        @include('cards.edit.box4', ['reference' => 'box4'])
                        @include('cards.edit.box5', ['reference' => 'box5'])
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                        @include('cards.edit.editors')
                        @include('cards.edit.settings')
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ trans('cards.update.configuration') }}
                </button>
            </form>
        @endcan
    </div>
@endsection
