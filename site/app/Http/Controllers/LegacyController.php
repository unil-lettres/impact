<?php

namespace App\Http\Controllers;

use App\Card;
use App\Course;
use App\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class LegacyController extends Controller
{
    /**
     * Read legacy query params and redirect to the new URL based on the
     * legacy ID.
     */
    public function redirect(Request $request): Redirector|RedirectResponse
    {
        $cardLegacyId = $request->input('fiche');
        $folderLegacyId = $request->input('dossier');
        $courseLegacyId = $request->input('cours');

        if (! is_null($cardLegacyId)) {
            $id = Card::where('legacy_id', $cardLegacyId)->first()?->id;
            if (! is_null($id)) {
                return redirect("cards/$id");
            }
        }
        if (! is_null($folderLegacyId)) {
            $id = Folder::where('legacy_id', $folderLegacyId)->first()?->id;
            if (! is_null($id)) {
                return redirect("folders/$id");
            }
        }
        if (! is_null($courseLegacyId)) {
            $id = Course::where('legacy_id', $courseLegacyId)->first()?->id;
            if (! is_null($id)) {
                return redirect("courses/$id");
            }
        }

        return redirect('/');
    }
}
