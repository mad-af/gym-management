<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarGenerator
{
    private function buildMedia(string $disk, string $path, string $fileName, string $mimeType, int $size, string $collection, ?Model $model): Media
    {
        $media = new Media([
            'disk' => $disk,
            'path' => $path,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'size' => $size,
            'collection' => $collection,
            'placeholder' => null,
            'uploaded_by' => Auth::id(),
        ]);

        if ($model) {
            $media->mediable()->associate($model);
        }

        return $media;
    }

    private function saveContentAsMedia(string $disk, string $path, string $fileName, string $mimeType, string $collection, string $content, ?Model $model): ?Media
    {
        Storage::disk($disk)->put($path, $content);

        $media = $this->buildMedia(
            $disk,
            $path,
            $fileName,
            $mimeType,
            strlen($content),
            $collection,
            $model
        );

        $media->save();

        return $media;
    }

    private function generateInitialsSvg(string $seed): string
    {
        $name = trim($seed);
        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $initials .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        $initials = $initials !== '' ? $initials : 'U';

        $hash = substr(md5($seed), 0, 6);
        $bg = '#'.$hash;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200" role="img" aria-label="Avatar">
  <rect width="200" height="200" rx="40" fill="{$bg}"/>
  <text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" font-family="ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial" font-size="72" font-weight="700" fill="#ffffff">{$initials}</text>
</svg>
SVG;
    }

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

        try {
            $result = Process::run($command);
        } catch (\Throwable $e) {
            Log::error('Avatar generation process failed: '.$e->getMessage());
            $result = null;
        }

        if (! $result || $result->failed()) {
            if ($result) {
                Log::error('Avatar generation failed: '.$result->errorOutput());
            }

            try {
                $url = 'https://api.dicebear.com/9.x/thumbs/svg';
                $response = Http::timeout(5)->get($url, ['seed' => $seed]);
                if ($response->successful() && $response->body() !== '') {
                    $disk = 'public';
                    $mimeType = 'image/svg+xml';

                    return $this->saveContentAsMedia(
                        $disk,
                        $path,
                        "avatar-{$seed}.svg",
                        $mimeType,
                        $collection,
                        $response->body(),
                        $model
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Avatar generation fallback failed: '.$e->getMessage());
            }

            $disk = 'public';
            $mimeType = 'image/svg+xml';
            $svg = $this->generateInitialsSvg($seed);

            return $this->saveContentAsMedia(
                $disk,
                $path,
                "avatar-{$seed}.svg",
                $mimeType,
                $collection,
                $svg,
                $model
            );
        }

        // Check if file was actually created
        if (! file_exists($tempFile)) {
            Log::error("Avatar file not found at: {$tempFile}");

            $disk = 'public';
            $mimeType = 'image/svg+xml';
            $svg = $this->generateInitialsSvg($seed);

            return $this->saveContentAsMedia(
                $disk,
                $path,
                "avatar-{$seed}.svg",
                $mimeType,
                $collection,
                $svg,
                $model
            );
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

        $media = $this->buildMedia(
            $disk,
            $path,
            "avatar-{$seed}.{$extension}",
            $mimeType,
            $size,
            $collection,
            $model
        );

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
