<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarGenerator
{
    /**
     * Generate avatar using DiceBear (via Node.js) and save to storage.
     *
     * @param  string  $seed  The seed string (e.g., username or name)
     * @param  string  $format  'svg' or 'png'
     * @param  Model|null  $model  Optional model to attach the media to
     * @return Media|null The created Media model or null on failure
     */
    public function generateAndSave(string $seed, string $format = 'svg', ?Model $model = null): ?Media
    {
        $extension = $format === 'png' ? 'png' : 'svg';
        $filename = Str::uuid().'.'.$extension;
        $collection = 'avatar';
        $path = "uploads/{$collection}/".date('Y/m/d')."/{$filename}";

        // Define temporary file path
        $tempDir = storage_path('app/temp/avatars');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempFile = "{$tempDir}/{$filename}";

        // Path to the node script
        $scriptPath = base_path('app/Services/AvatarGenerator/generate.js');
        $nodePath = env('NODE_PATH', 'node');

        // Execute Node script: node script.js [seed] [outputPath] [format]
        $command = sprintf('%s %s %s %s %s',
            $nodePath,
            escapeshellarg($scriptPath),
            escapeshellarg($seed),
            escapeshellarg($tempFile),
            escapeshellarg($format)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            Log::error('Avatar generation failed: '.$result->errorOutput());

            return null;
        }

        // Check if file was actually created
        if (! file_exists($tempFile)) {
            Log::error("Avatar file not found at: {$tempFile}");

            return null;
        }

        // Read file content
        $fileContent = file_get_contents($tempFile);
        $disk = 'public'; // or config('filesystems.default')

        // Save to Laravel Storage
        Storage::disk($disk)->put($path, $fileContent);

        // Clean up temp file
        unlink($tempFile);

        // File metadata
        $size = strlen($fileContent);
        $mimeType = $format === 'png' ? 'image/png' : 'image/svg+xml';

        $media = new Media([
            'disk' => $disk,
            'path' => $path,
            'file_name' => "avatar-{$seed}.{$extension}",
            'mime_type' => $mimeType,
            'size' => $size,
            'collection' => $collection,
            'placeholder' => null,
            'uploaded_by' => Auth::id(),
        ]);

        if ($model) {
            $media->mediable()->associate($model);
        }

        $media->save();

        return $media;
    }

    /**
     * Generate base64 string directly (no file storage).
     */
    public function generateBase64(string $seed, string $format = 'svg'): string
    {
        $scriptPath = base_path('app/Services/AvatarGenerator/generate.js');
        $nodePath = env('NODE_PATH', 'node');

        // node script.js [seed] [outputPath=empty] [format=base64]
        $command = sprintf('%s %s %s "" base64',
            $nodePath,
            escapeshellarg($scriptPath),
            escapeshellarg($seed)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            Log::error('Avatar base64 generation failed: '.$result->errorOutput());

            return '';
        }

        return trim($result->output());
    }
}
