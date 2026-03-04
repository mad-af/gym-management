<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasMedia
{
    /**
     * Get all media for the model.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the latest media item from a specific collection.
     */
    public function latestMedia(string $collection = 'default'): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')
            ->ofMany('id', 'max')
            ->where('collection', $collection);
    }

    /**
     * Helper to get the url of the latest media in a collection.
     */
    public function getFirstMediaUrl(string $collection = 'default'): ?string
    {
        $media = $this->media->where('collection', $collection)->sortByDesc('id')->first();

        return $media ? $media->url : null;
    }

    /**
     * Helper to get the full media object (url + placeholder) of the latest media in a collection.
     *
     * @return array{url: string, placeholder: ?string}|null
     */
    public function getFirstMedia(string $collection = 'default'): ?array
    {
        /** @var Media|null $media */
        $media = $this->media->where('collection', $collection)->sortByDesc('id')->first();

        if (! $media) {
            return null;
        }

        return [
            'url' => $media->url,
            'placeholder' => $media->placeholder,
        ];
    }
}
