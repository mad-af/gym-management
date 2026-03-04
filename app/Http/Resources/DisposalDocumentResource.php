<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisposalDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'disposal_number' => $this->disposal_number,
            'opd' => $this->opd?->only(['id', 'name']),
            'disposal_type' => $this->disposal_type,
            'disposal_date' => $this->disposal_date,
            'created_by' => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
                'avatar' => $this->creator->avatar,
            ] : null,
            'notes' => $this->notes,
            'document_path' => $this->document_path,
            'items' => DisposalItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
