<?php

namespace App\Services;

use App\Enums\QuoteStatus;
use App\Models\QuoteAttachment;
use App\Models\QuoteRequest;
use App\Models\QuoteStatusHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class QuoteRequestService
{
    public function __construct(
        private QuoteReferenceGenerator $references,
        private Notifier $notifier,
    ) {}

    /**
     * Stores a customer quote request (plus optional files) and sends the emails.
     * Email problems never fail the submission: the request is already saved.
     *
     * @param  array<string, mixed>  $data  validated fields
     * @param  array<int, UploadedFile>  $files
     */
    public function create(array $data, array $files, ?string $ip): QuoteRequest
    {
        $directory = null;

        try {
            $quote = DB::transaction(function () use ($data, $files, $ip, &$directory) {
                $quote = QuoteRequest::create([
                    'reference_number' => $this->references->next(),
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'] ?? null,
                    'service_id' => $data['service_id'],
                    'from_location' => $data['from_location'] ?? null,
                    'to_location' => $data['to_location'] ?? null,
                    'location' => $data['location'] ?? null,
                    'preferred_date' => $data['preferred_date'] ?? null,
                    'preferred_time' => $data['preferred_time'] ?? null,
                    'message' => $data['message'] ?? null,
                    'details' => $data['details'] ?? null,
                    'contact_consent' => true,
                    'status' => QuoteStatus::New,
                    'ip_address' => $ip,
                ]);

                QuoteStatusHistory::create([
                    'quote_request_id' => $quote->id,
                    'from_status' => null,
                    'to_status' => QuoteStatus::New->value,
                    'note' => 'Quote request received.',
                ]);

                $directory = 'quotes/'.$quote->reference_number;
                foreach ($files as $file) {
                    $this->storeAttachment($quote, $file, $directory);
                }

                return $quote;
            });
        } catch (Throwable $e) {
            // The DB rolled back; do not leave orphaned files behind.
            if ($directory !== null) {
                Storage::disk('local')->deleteDirectory($directory);
            }

            throw $e;
        }

        $quote->load(['attachments', 'service.category']);
        $this->notifier->quoteReceived($quote);

        return $quote;
    }

    /** Changes status and records who did it (no-op when unchanged). */
    public function changeStatus(QuoteRequest $quote, QuoteStatus $to, ?User $by, ?string $note = null): void
    {
        if ($quote->status === $to) {
            return;
        }

        DB::transaction(function () use ($quote, $to, $by, $note) {
            QuoteStatusHistory::create([
                'quote_request_id' => $quote->id,
                'from_status' => $quote->status->value,
                'to_status' => $to->value,
                'changed_by' => $by?->id,
                'note' => $note,
            ]);

            $quote->update(['status' => $to]);
        });
    }

    /** Files go to the PRIVATE disk under a generated name; the customer's file name is display-only. */
    private function storeAttachment(QuoteRequest $quote, UploadedFile $file, string $directory): void
    {
        $mime = (string) $file->getMimeType();
        $size = (int) $file->getSize();
        $original = $file->getClientOriginalName();
        $extension = $file->guessExtension() ?: 'bin';

        $path = Storage::disk('local')->putFileAs($directory, $file, Str::uuid()->toString().'.'.$extension);

        QuoteAttachment::create([
            'quote_request_id' => $quote->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => Str::limit((string) preg_replace('/[^\w.\- ]+/u', '_', $original), 180, ''),
            'mime_type' => $mime,
            'size_bytes' => $size,
        ]);
    }
}
