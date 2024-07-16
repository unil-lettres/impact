<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Enums\FileStatus;
use App\Folder;
use App\Http\Requests\CreateCardExport;
use App\Http\Requests\DestroyCard;
use App\Http\Requests\UpdateCard;
use App\Services\ExportBoxService;
use App\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
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
     * Display the specified resource.
     *
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
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function edit(Card $card)
    {
        $this->authorize('update', $card);

        // If the user is not a manager or an admin, only show states with the limited scope
        $states = match (Auth::user()->isManager($card->course)) {
            true => State::where('course_id', $card->course->id),
            default => State::limited($card)->where('course_id', $card->course->id),
        };

        // Only show files with the ready status
        $files = $card->course
            ->files
            ->where('status', FileStatus::Ready);

        return view('cards.edit', [
            'card' => $card,
            'breadcrumbs' => $card
                ->breadcrumbs(true),
            'holders' => $card
                ->holders(),
            'users' => $card->course
                ->users(),
            'files' => $files,
            'states' => $states
                ->ordered()
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(UpdateCard $request, int $id)
    {
        $card = Card::find($id);

        $this->authorize('update', $card);

        $options = $card->options ?? json_decode(Card::OPTIONS, true);
        $options['box1']['hidden'] = (bool) $request->get('box1-hidden');
        $options['box1']['link'] = $request->get('box1-link');
        $options['box1']['start'] = (int) $request->get('box1-start');
        $options['box1']['end'] = (int) $request->get('box1-end');
        $options['box2']['hidden'] = (bool) $request->get('box2-hidden');
        $options['box2']['sync'] = (bool) $request->get('box2-sync');
        $options['box3']['hidden'] = (bool) $request->get('box3-hidden');
        $options['box3']['title'] = $request->get('box3-title');
        $options['box3']['fixed'] = $request->get('box3-fixed');
        $options['box4']['hidden'] = (bool) $request->get('box4-hidden');
        $options['box4']['title'] = $request->get('box4-title');
        $options['box4']['fixed'] = $request->get('box4-fixed');
        $options['box5']['hidden'] = (bool) $request->get('box5-hidden');
        $options['no_emails'] = (bool) $request->get('no_emails');
        $options['presentation_date'] = $request->get('presentation_date');

        $card->update([
            'title' => $request->get('title'),
            'file_id' => $request->get('box1-file'),
            'state_id' => $request->get('state'),
            'options' => $options,
        ]);

        return redirect()
            ->route('cards.show', $card->id)
            ->with('success', trans('messages.card.configuration.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
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

        return redirect()
            ->back()
            ->with('success', trans('messages.card.unlinked'));
    }

    /**
     * Create an export of a box from the specified resource.
     *
     * @return BinaryFileResponse
     *
     * @throws AuthorizationException|Exception
     */
    public function export(CreateCardExport $request, int $id)
    {
        $card = Card::find($id);
        $box = $request->get('box');

        $this->authorize('box', [
            Card::class,
            $card,
            $box,
        ]);

        $format = $request->get('format');

        $service = new ExportBoxService($card, $box, $format);

        return response()
            ->download(
                $service->export()
            )
            ->deleteFileAfterSend();
    }

    public function print(Request $request): View
    {
        // Authorizations are done in the view.

        $course = Course::find($request->get('course'));
        $folder = Folder::find($request->get('folder'));
        $cards = Card::findMany($request->get('cards'));
        $cards = $cards->count() > 0 ? $cards : null;

        // Check that only one of the three parameters is set.
        if (count(array_filter([$course, $folder, $cards])) !== 1) {
            throw new InvalidArgumentException('Invalid parameters');
        }

        [$header, $printedCards] = match (true) {
            ! is_null($course) => [$course->name, $course->cards],
            ! is_null($folder) => [$folder->course->name, $folder->getCardsRecursive()],
            ! is_null($cards) => [$cards->first()->course->name, $cards],
            default => throw new InvalidArgumentException('Invalid parameters'),
        };

        // Check that all cards belongs to the same course.
        if ($printedCards->pluck('course_id')->unique()->count() !== 1) {
            throw new InvalidArgumentException('Invalid parameters');
        }

        // Don't print the introduction page if there is only one card.
        if ($printedCards->count() === 1) {
            $header = null;
        }

        return view('cards.print', [
            'header' => $header,
            'cards' => $printedCards->sortBy('title'),
        ]);
    }
}
