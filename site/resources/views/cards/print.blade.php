@use('App\Enums\TranscriptionType')

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('includes.head')
    </head>
    <body>
        <!--
            Small hack to force the icon to be loaded before triggering the
            print dialog (it won't be loaded because of the d-none).
            Otherwise it won't show up.
        -->
        <i class="far fa-eye-slash" style="height: 0; overflow: hidden;"></i>

        <div class="d-print-none d-flex align-items-center justify-content-center vh-100 text-center">
            <div class="fs-4 ">
                <div>{{ trans('cards.print.page.head')}}</div>
                <div>{!! trans('cards.print.page.button', ['click_here' => '<a href="javascript:print()">'.trans('cards.print.page.click_here').'</a>'])!!}</div>
            </div>
        </div>
        <div id='print-card' class="d-none d-print-block">
            @if ($header)
            <div class="m-4 break-page">
                <h1 class="fw-normal fs-3 text-center">{{ $header }}</h1>
                <h2 class="fw-normal fs-4">{{ trans('cards.print.title')}}</h2>
                <ul>
                    @foreach($cards as $card) @can('index', $card)
                        <li>
                            {{ $card->title }}
                            @cannot('view', $card)
                                <i class="far fa-eye-slash"></i>
                            @endcan
                        </li>
                    @endcan @endforeach
                </ul>
            </div>
            @endif
        @foreach ($cards as $card) @can('view', $card)
            <div class="m-4 @if (!$loop->last) break-page @endif">
                <h1 class="fw-normal fs-4">{{ $card->title }}</h1>
                <div class="d-flex gap-4">
                    <div><span class="fw-bold">{{trans('cards.state')}}:</span> {{ $card->state->name }}</div>
                    <div><span class="fw-bold">{{trans('cards.date')}}:</span> {{ $card->options['presentation_date'] ?? '-' }}</div>
                </div>

                @if (!$card->options['box3']['hidden'] && $card->box3)
                    <h2 class="fw-bold fs-5 mt-3">{{ $card->options['box3']['title'] }}</h2>
                    <div>{!! $card->box3 !!}</div>
                @endif

                @if (!$card->options['box2']['hidden'])
                    <h2 class="fw-bold fs-5 mt-3">{{ trans('cards.transcription') }}</h2>
                    @if($card->course->transcription === TranscriptionType::Icor && $card->box2[TranscriptionType::Icor])
                        <table class="box2">
                            <tbody>
                                @foreach($card->box2[TranscriptionType::Icor] as $line)
                                <tr class="transcription-row">
                                    <td class="line-number align-top">{{ $line['number'] }}</td>
                                    <td class="speaker align-top">{{ $line['speaker'] }}</td>
                                    <td class="speech align-top">{{ $line['speech'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($card->course->transcription === TranscriptionType::Text && $card->box2[TranscriptionType::Text])
                        <div class="font-transcription">{!! $card->box2[TranscriptionType::Text] !!}</div>
                    @else
                        <div class="font-transcription text-center fs-4">{{ trans('messages.card.no.transcription') }}</div>
                    @endif

                    @if (!$card->options['box4']['hidden'] && $card->box4)
                        <h2 class="fw-bold fs-5 mt-3">{{ $card->options['box4']['title'] }}</h2>
                        <div>{!! $card->box4 !!}</div>
                    @endif
                @endif
            </div>
            @endcan @endforeach
        </div>
    </body>
</html>
