<?php

namespace App\Http\Controllers;

use App\Folder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;

class FolderController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return Renderable
     *
     * @throws AuthorizationException
     */
    public function show(Folder $folder)
    {
        $this->authorize('view', $folder);

        return view('folders.show', [
            'folder' => $folder,
            'breadcrumbs' => $folder->breadcrumbs(),
        ]);
    }
}
