<?php

namespace App\Http\Controllers;

use App\Enums\FileStatus;
use App\File;
use App\Jobs\ProcessFile;
use App\Services\FileUploadProcessor;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Display a listing of the resource in the admin panel.
     *
     * @return Renderable
     */
    public function manage()
    {
        // TODO: add policy

        $files = File::orderBy('created_at', 'desc')
            ->paginate(config('const.pagination.per'));

        return view('files.manage', [
            'files' => $files
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        // TODO: add policy
        // TODO: add logic
    }

    /**
     * File upload endpoint.
     *
     * @param Request $request
     * @param FileUploadProcessor $fileUploadProcessor
     *
     * @return JsonResponse
     */
    public function upload(Request $request, FileUploadProcessor $fileUploadProcessor)
    {
        // TODO: add policy

        // Move file to temp storage
        $path = $fileUploadProcessor
            ->moveFileToStoragePath(
                $request->file('file'),
                true
            );

        // Get file basic infos
        $mimeType = $request->file('file')->getMimeType();
        $filename = $request->file('file')->getClientOriginalName();
        $size = $request->file('file')->getSize();

        // Create file draft with basic infos
        $file = File::create([
            'name' => $fileUploadProcessor
                ->getFileName($filename),
            'filename' => $fileUploadProcessor
                ->getBaseName($path),
            'status' => FileStatus::Processing,
            'type' => $fileUploadProcessor
                ->fileType($mimeType),
            'size' => $size
        ]);

        // Dispatch created file for async processing
        ProcessFile::dispatch($file);

        return response()->json([
            'success' => $file->id
        ], 200);
    }
}
