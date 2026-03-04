<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisposalItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset' => [
                'id' => $this->asset?->id,
                'asset_code' => $this->asset?->asset_code,
                'name' => $this->asset?->name,
                'status' => $this->asset?->status,
                'condition' => $this->asset?->condition,
                'opd' => $this->asset?->opd?->only(['id', 'name']),
                'room' => $this->asset?->room?->only(['id', 'name']),
            ],
            'reason' => $this->reason,
            'condition_at_disposal' => $this->condition_at_disposal,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
