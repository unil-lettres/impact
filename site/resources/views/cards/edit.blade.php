@extends('layouts.app-base')

<!-- TODO: translate -->

@section('content')
    <div id="configure-card">
        @section('title')
            Configurer "{{ $card->title }}"
        @endsection
        <hr>
    </div>

    <form method="post"
          action="{{ route('cards.update', $card->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        Rédacteurs
                    </div>
                    <div class="card-body">
                        <p>Vous pouvez choisir les rédacteurs de la fiche parmi les utilisateurs suivants.</p>

                        @if ($students->isNotEmpty())
                            <div class="form-group">
                                <input id="editors" name="editors" type="hidden" value="[]">
                                <div id="rct-multi-user-select"
                                     data='{{ json_encode(['select' => $students, 'default' => $editors]) }}'
                                     ref='editors'
                                ></div>
                            </div>
                        @else
                            <p class="text-secondary">
                                Aucun rédacteur disponible
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        Emails
                    </div>
                    <div class="card-body">
                        <p>Par défaut, un email est envoyé à chaque changement d'état d'une fiche.</p>
                        <!-- TODO: add emails option -->
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            Mettre à jour
        </button>
    </form>
@endsection
