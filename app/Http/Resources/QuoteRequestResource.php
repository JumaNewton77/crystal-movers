<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\QuoteRequest */
class QuoteRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference_number,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'service' => $this->service ? ['id' => $this->service->id, 'title' => $this->service->title, 'slug' => $this->service->slug] : null,
            'category' => $this->service?->category ? ['id' => $this->service->category->id, 'name' => $this->service->category->name] : null,
            // Legacy moving quotes keep their move type; new enquiries use the service instead.
            'move_type' => $this->move_type?->value,
            'move_type_label' => $this->service?->title ?? $this->move_type?->label() ?? 'Enquiry',
            'from_location' => $this->from_location,
            'to_location' => $this->to_location,
            'location' => $this->location,
            'preferred_date' => $this->preferred_date?->toDateString(),
            'preferred_time' => $this->preferred_time,
            'details' => $this->details ?: null,
            'contact_consent' => (bool) $this->contact_consent,
            'message' => $this->message,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'admin_notes' => $this->admin_notes, // internal: only ever returned by admin endpoints
            'created_at' => $this->created_at?->toIso8601String(),
            'attachments_count' => $this->whenCounted('attachments'),
            'attachments' => QuoteAttachmentResource::collection($this->whenLoaded('attachments')),
            'history' => $this->whenLoaded('statusHistory', fn () => $this->statusHistory->map(fn ($entry) => [
                'from' => $entry->from_status ? \App\Enums\QuoteStatus::from($entry->from_status)->label() : null,
                'to' => \App\Enums\QuoteStatus::from($entry->to_status)->label(),
                'by' => $entry->changedBy?->name,
                'note' => $entry->note,
                'created_at' => $entry->created_at?->toIso8601String(),
            ])->values()->all()),
        ];
    }
}
