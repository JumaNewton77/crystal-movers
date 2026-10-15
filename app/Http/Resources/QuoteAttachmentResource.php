<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\QuoteAttachment */
class QuoteAttachmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'is_image' => str_starts_with($this->mime_type, 'image/'),
            // Served only through the authenticated admin API; the files themselves are on the private disk.
            'url' => "/api/admin/quotes/{$this->quote_request_id}/attachments/{$this->id}",
        ];
    }
}
