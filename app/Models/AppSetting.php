<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory, HasMedia, HasUuids;

    protected $fillable = [
        'type',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function getValue(?string $key = null): mixed
    {
        $data = $this->data;
        if (! is_array($data)) {
            return null;
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? null;
    }

    public function setValue(mixed $value, ?string $key = null): void
    {
        if ($key === null) {
            $this->data = is_array($value) ? $value : ['value' => $value];

            return;
        }

        $data = is_array($this->data) ? $this->data : [];
        $data[$key] = $value;
        $this->data = $data;
    }

    public function getLogo(): ?array
    {
        $media = $this->media->where('collection', 'logo')->sortByDesc('id')->first();
        if (! $media) {
            return null;
        }

        return [
            'url' => $media->url,
            'placeholder' => $media->placeholder,
            'mime_type' => $media->mime_type,
            'disk' => $media->disk,
            'path' => $media->path,
        ];
    }

    public function getSmallLogo(): ?array
    {
        $media = $this->media->where('collection', 'logo_small')->sortByDesc('id')->first();
        if (! $media) {
            return null;
        }

        return [
            'url' => $media->url,
            'placeholder' => $media->placeholder,
            'mime_type' => $media->mime_type,
            'disk' => $media->disk,
            'path' => $media->path,
        ];
    }

    public static function findByType(string $type): ?self
    {
        return self::query()->where('type', $type)->first();
    }

    public static function findOrCreateType(string $type, array $data = []): self
    {
        $setting = self::findByType($type);
        if ($setting) {
            return $setting;
        }

        return self::query()->create([
            'type' => $type,
            'data' => $data,
        ]);
    }

    protected $hidden = [
        'media',
    ];
}
