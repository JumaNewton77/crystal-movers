<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuoteRequest;
use App\Http\Resources\QuoteRequestResource;
use App\Models\QuoteAttachment;
use App\Models\QuoteRequest;
use App\Services\QuoteRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteController extends Controller
{
    private const SORTABLE = ['created_at', 'status', 'preferred_date'];

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(QuoteStatus::class)],
            'category_id' => ['nullable', 'integer'],
            'service_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(self::SORTABLE)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:5,100'],
        ]);

        $query = QuoteRequest::query()->withCount('attachments')->with('service.category');

        if (! empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('service', fn ($q) => $q->where('service_category_id', $filters['category_id']));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            // Escape LIKE wildcards so searches are literal.
            $term = '%'.addcslashes($filters['search'], '%_\\').'%';
            $query->where(function ($q) use ($term) {
                foreach (['reference_number', 'name', 'phone', 'email', 'from_location', 'to_location', 'location'] as $column) {
                    $q->orWhere($column, 'ilike', $term);
                }
            });
        }

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $query->orderBy($sort, $direction)->orderBy('id', 'desc');

        $page = $query->paginate($filters['per_page'] ?? 15);

        return $this->ok([
            'items' => QuoteRequestResource::collection($page->getCollection())->resolve(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(QuoteRequest $quote): JsonResponse
    {
        return $this->ok($this->detail($quote));
    }

    public function update(UpdateQuoteRequest $request, QuoteRequest $quote, QuoteRequestService $quotes): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            $quotes->changeStatus($quote, QuoteStatus::from($data['status']), $request->user(), $data['status_note'] ?? null);
        }

        if (array_key_exists('admin_notes', $data)) {
            $quote->update(['admin_notes' => $data['admin_notes']]);
        }

        return $this->ok($this->detail($quote->refresh()), 'Saved.');
    }

    /** Streams a customer upload from the private disk. Admin-only; never a public URL. */
    public function attachment(QuoteRequest $quote, QuoteAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->quote_request_id === $quote->id, 404);

        $disk = \Illuminate\Support\Facades\Storage::disk($attachment->disk);
        abort_unless($disk->exists($attachment->path), 404);

        $isImage = str_starts_with($attachment->mime_type, 'image/');

        // Images preview inline; everything else (videos) downloads. nosniff is added globally,
        // and the sandbox CSP stops any script from running even if a file were opened directly.
        return $disk->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ], $isImage ? 'inline' : 'attachment');
    }

    /** @return array<string, mixed> */
    private function detail(QuoteRequest $quote): array
    {
        $quote->load(['attachments', 'statusHistory.changedBy', 'service.category']);

        return (new QuoteRequestResource($quote))->resolve();
    }
}
