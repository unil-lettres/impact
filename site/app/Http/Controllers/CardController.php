<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Enums\StateType;
use App\Folder;
use App\Http\Requests\CreateCard;
use App\Http\Requests\CreateCardExport;
use App\Http\Requests\DestroyCard;
use App\Http\Requests\StoreCard;
use App\Http\Requests\UpdateCard;
use App\Http\Requests\UpdateCardEditor;
use App\Http\Requests\UpdateCardTranscription;
use App\Services\ExportCardBox;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use PhpOffice\PhpWord\Exception\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     *
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('viewAny', Card::class);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  CreateCard  $request
     * @return RedirectResponse|Renderable
     *
     * @throws AuthorizationException
     */
    public function create(CreateCard $request)
    {
        // Retrieve the course of the card
        $course = Course::findOrFail($request->input('course'));

        $this->authorize('create', [
            Card::class,
            $course,
        ]);

        return view('cards.create', [
            'course' => $course,
            'state' => $course
                ->states
                ->where(
                    'type', StateType::Private
                )->first(),
            'breadcrumbs' => $course
                ->breadcrumbs(true),
            'folders' => $course
                ->folders()
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCard  $request
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(StoreCard $request)
    {
        $course = Course::findOrFail($request->input('course_id'));

        $this->authorize('create', [
            Card::class,
            $course,
        ]);

        // Check also folder select policy if a folder is selected
        if ($request->input('folder_id')) {
            $this->authorize('select', [
                Folder::class,
                $course,
                Folder::findOrFail($request->input('folder_id')),
            ]);
        }

        // Create new card
        $card = new Card($request->all());
        $card->save();

        return redirect()
            ->route('courses.show', $request->input('course_id'))
            ->with('success', trans('messages.card.created', ['title' => $card->title]));
    }

    /**
     * Display the specified resource.
     *
     * @param  Card  $card
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function show(Card $card)
    {
        $this->authorize('view', $card);

        return view('cards.show', [
            'card' => $card,
            'breadcrumbs' => $card
                ->breadcrumbs(),
            'course' => $card->course,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Card  $card
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function edit(Card $card)
    {
        $this->authorize('update', $card);

        return view('cards.edit', [
            'card' => $card,
            'breadcrumbs' => $card
                ->breadcrumbs(true),
            'editors' => $card
                ->editors(),
            'students' => $card->course
                ->students(),
            'files' => $card->course
                ->files,
            'states' => $card->course
                ->states,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCard  $request
     * @param  int  $id
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(UpdateCard $request, int $id)
    {
        $card = Card::find($id);

        $this->authorize('update', $card);

        $options = $card->options ?? json_decode(Card::OPTIONS);
        $options['box1']['hidden'] = (bool) $request->get('box1-hidden');
        $options['box1']['link'] = $request->get('box1-link');
        $options['box1']['start'] = (int) $request->get('box1-start');
        $options['box1']['end'] = (int) $request->get('box1-end');
        $options['box2']['hidden'] = (bool) $request->get('box2-hidden');
        $options['box2']['sync'] = (bool) $request->get('box2-sync');
        $options['box3']['hidden'] = (bool) $request->get('box3-hidden');
        $options['box3']['title'] = $request->get('box3-title');
        $options['box4']['hidden'] = (bool) $request->get('box4-hidden');
        $options['box4']['title'] = $request->get('box4-title');
        $options['box5']['hidden'] = (bool) $request->get('box5-hidden');
        $options['emails'] = (bool) $request->get('emails');

        $card->update([
            'title' => $request->get('title'),
            'file_id' => $request->get('box1-file'),
            'state_id' => $request->get('state'),
            'options' => $options,
        ]);
        $card->save();

        return redirect()
            ->route('cards.show', $card->id)
            ->with('success', trans('messages.card.configuration.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DestroyCard  $request
     * @param  int  $id
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function destroy(DestroyCard $request, int $id)
    {
        $card = Card::find($id);
        $course = $card->course;

        $this->authorize('forceDelete', $card);

        $card->forceDelete();

        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', trans('messages.card.deleted'));
    }

    /**
     * Unlink file from the specified resource.
     *
     * @param  Card  $card
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function unlinkFile(Card $card)
    {
        $this->authorize('unlinkFile', $card);

        $card->update([
            'file_id' => null,
        ]);
        $card->save();

        return redirect()
            ->back()
            ->with('success', trans('messages.card.unlinked'));
    }

    /**
     * Update the editor html from the specified resource.
     *
     * @param  UpdateCardEditor  $request
     * @param  int  $id
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function editor(UpdateCardEditor $request, int $id)
    {
        $card = Card::find($id);

        $this->authorize('editor', $card);

        $html = $request->get('html');
        $box = $request->get('box');

        $card->update([
            $box => $html,
        ]);
        $card->save();

        return response()->json([
            'success' => $id,
        ], 200);
    }

    /**
     * Update the transcription from the specified resource.
     *
     * @param  UpdateCardTranscription  $request
     * @param  int  $id
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function transcription(UpdateCardTranscription $request, int $id)
    {
        $card = Card::find($id);

        $this->authorize('transcription', $card);

        $box = $request->get('box');

        $box2 = $card->box2 ?? json_decode(Card::TRANSCRIPTION);
        $box2['data'] = $request->get('transcription') ? $request->get('transcription') : [];

        $card->update([
            $box => $box2,
        ]);
        $card->save();

        return response()->json([
            'success' => $id,
        ], 200);
    }

    /**
     * Create an export of a box from the specified resource.
     *
     * @param  CreateCardExport  $request
     * @param  int  $id
     * @return BinaryFileResponse
     *
     * @throws AuthorizationException|Exception
     */
    public function export(CreateCardExport $request, int $id)
    {
        $card = Card::find($id);

        $this->authorize('export', $card);

        $format = $request->get('format');
        $box = $request->get('box');

        $service = new ExportCardBox($card, $box, $format);

        return response()
            ->download(
                $service->export()
            )
            ->deleteFileAfterSend();
    }
}
