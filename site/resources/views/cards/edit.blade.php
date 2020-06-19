@extends('layouts.app-base')

@section('content')
    <div id="configure-card">
        @can('update', $card)
            @section('title')
                {{ trans('cards.configure') }}
            @endsection
            <hr>
            <div class="row">
                <div class="col-md-12 col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                Emails
                            </div>
                            <div class="card-body">
                                <form method="post"
                                      action="{{ route('cards.update', $card->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <p>{{ trans('cards.send_mails') }}</p>
                                    <p>
                                        <!-- TODO: add emails option & translate -->
                                        <i>[Add email option radio button here]</i>
                                    </p>

                                    <hr>
                                    <button type="submit" class="btn btn-primary">
                                        {{ trans('cards.update.configuration') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                </div>

                <div class="col-md-12 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            {{ trans('cards.editors') }}
                        </div>
                        <div class="card-body">
                            <p>{{ trans('cards.choose_editors') }}</p>

                            @if ($students->isNotEmpty())
                                <div class="form-group">
                                    <div id="rct-multi-editor-select"
                                         data='{{ json_encode(['record' => $card, 'options' => $students, 'defaults' => $editors]) }}'
                                    ></div>
                                </div>
                            @else
                                <p class="text-secondary">
                                    {{ trans('cards.editors.not_found') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection
