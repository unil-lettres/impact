@extends('layouts.app-base')

@section('title')
    {{ trans('cards.configure') }}
@endsection

@section('content')
    <div id="configure-card">
        @can('update', $card)
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
                        @include('cards.edit.settings')
                        @include('cards.edit.editors')
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ trans('cards.update.configuration') }}
                </button>
            </form>
        @endcan
    </div>
@endsection

@section('scripts-footer')
    <script type="text/javascript">
        const externalLinkField = $('#box1-link');
        const externalLinkFieldValue = externalLinkField.val();

        if(externalLinkFieldValue.trim().length) {
            // Disable some fields if a media external link is present
            disableFields(externalLinkFieldValue);
        }

        externalLinkField.on('input', function() {
            // Disable some fields if a media external link is present
            disableFields(this.value);
        });

        function disableFields(element) {
            $('#box1-start').prop('disabled', element.trim().length);
            $('#box1-end').prop('disabled', element.trim().length);
            $('#box2-sync').prop('disabled', element.trim().length);
        }
    </script>
@endsection
