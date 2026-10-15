<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin inbox for contact-form messages. */
class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(ContactMessageStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = ContactMessage::query()->latest('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // The default inbox hides archived messages.
            $query->where('status', '!=', ContactMessageStatus::Archived->value);
        }

        if (! empty($filters['search'])) {
            $term = '%'.addcslashes($filters['search'], '%_\\').'%';
            $query->where(function ($q) use ($term) {
                foreach (['name', 'email', 'subject', 'message'] as $column) {
                    $q->orWhere($column, 'ilike', $term);
                }
            });
        }

        return $this->ok(ContactMessageResource::collection($query->limit(200)->get())->resolve());
    }

    public function update(Request $request, ContactMessage $message): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(ContactMessageStatus::class)],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        $message->update($data);

        return $this->ok((new ContactMessageResource($message->refresh()))->resolve(), 'Saved.');
    }

    public function destroy(ContactMessage $message): JsonResponse
    {
        $message->delete();

        return $this->ok(null, 'Deleted.');
    }
}
