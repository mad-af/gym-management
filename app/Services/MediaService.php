<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    /**
     * Upload and attach media to a model.
     *
     * @param  Model|null  $model  (optional)
     */
    public function upload(
        UploadedFile $file,
        ?Model $model = null,
        string $collection = 'default',
        string $disk = 'public'
    ): Media {
        return DB::transaction(function () use ($file, $model, $collection, $disk) {
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = "uploads/{$collection}/".date('Y/m/d');
            $fullPath = "{$path}/{$filename}";

            $mimeType = $file->getMimeType();
            $isImage = str_starts_with($mimeType, 'image/');

            $placeholder = null;
            $fileSize = $file->getSize();
            $finalContent = $file->get();

            // Process image if applicable
            if ($isImage) {
                try {
                    $image = Image::read($file);

                    // 1. Generate Placeholder (Tiny base64)
                    // Clone because subsequent operations modify the image
                    $placeholderImage = clone $image;
                    $placeholder = $placeholderImage->scale(width: 20)
                        ->blur(5) // Slight blur for effect
                        ->toPng()
                        ->toDataUri();

                    // 2. Compress & Resize Main Image
                    // Max width 1200px, quality 80%
                    $encoded = $image->scaleDown(width: 1200)
                        ->encode(new AutoEncoder(quality: 80));

                    $finalContent = $encoded->toString();
                    $fileSize = strlen($finalContent);
                } catch (\Exception $e) {
                    // Fallback to original file if processing fails
                    // Log error if needed: Log::error($e->getMessage());
                }
            }

            // Store file
            Storage::disk($disk)->put($fullPath, $finalContent);

            // Create DB Record
            $media = new Media([
                'disk' => $disk,
                'path' => $fullPath,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'collection' => $collection,
                'placeholder' => $placeholder,
                'uploaded_by' => Auth::id(),
            ]);

            if ($model) {
                $media->mediable()->associate($model);
            }

            $media->save();

            return $media;
        });
    }

    /**
     * Delete media (Soft Delete).
     */
    public function delete(Media $media): bool
    {
        return $media->delete();
    }

    /**
     * Force delete media (Remove file and DB record).
     */
    public function forceDelete(Media $media): bool
    {
        // Delete file from storage
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        return $media->forceDelete();
    }
}
