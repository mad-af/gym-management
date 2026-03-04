<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        // 1. Upload File
        $media = $this->mediaService->upload(
            $request->file('file'),
            null, // Don't attach to model immediately via API for security/simplicity
            $request->input('collection', 'default')
        );

        // 2. Return Response
        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => [
                'id' => $media->id,
                'url' => $media->url,
                'placeholder' => $media->placeholder,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'collection' => $media->collection,
            ],
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $media): JsonResponse
    {
        $this->mediaService->delete($media);

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }
}
