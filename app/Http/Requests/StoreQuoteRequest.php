<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

/**
 * Public quote form. Plain-text fields are stripped of HTML before validation
 * (output is also escaped everywhere it is displayed: React, Blade and email views).
 * `website` is a honeypot: real visitors never see or fill it.
 */
class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Extra answers a customer may give for specific kinds of work. Anything else is dropped. */
    public const DETAIL_KEYS = [
        'property_type', 'camera_count', 'tv_size', 'wall_type', 'issue_type',
        'work_type', 'bracket', 'move_size', 'special_handling', 'appliance_type',
        'existing_setup', 'furniture_type', 'quantity', 'assembly_type',
    ];

    /** Longer free-text answers; everything else is a short value. */
    private const LONG_DETAIL_KEYS = ['special_handling', 'existing_setup'];

    protected function prepareForValidation(): void
    {
        $clean = [];
        foreach (['name', 'phone', 'email', 'location', 'from_location', 'to_location', 'message', 'preferred_time'] as $field) {
            if (is_string($this->input($field))) {
                $clean[$field] = trim(strip_tags($this->input($field)));
            }
        }

        // Keep only the known "details" answers, as plain trimmed text.
        $details = [];
        foreach ((array) $this->input('details', []) as $key => $value) {
            if (in_array($key, self::DETAIL_KEYS, true) && is_string($value) && trim($value) !== '') {
                $details[$key] = mb_substr(trim(strip_tags($value)), 0, in_array($key, self::LONG_DETAIL_KEYS, true) ? 300 : 100);
            }
        }
        $clean['details'] = $details ?: null;

        $this->merge($clean);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $uploads = config('crystalmovers.uploads');
        $today = now(config('crystalmovers.timezone'))->toDateString();

        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s\-().]{7,25}$/'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            // Only published, active services can be requested. International "coming soon" is not bookable.
            'service_id' => [
                'required', 'integer',
                Rule::exists('services', 'id')->where(fn ($q) => $q->where('is_published', true)->where('status', 'active')),
            ],
            'location' => ['nullable', 'string', 'max:190'],
            'from_location' => ['nullable', 'string', 'max:190'],
            'to_location' => ['nullable', 'string', 'max:190'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:'.$today],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'message' => ['nullable', 'string', 'max:2000'],
            'details' => ['nullable', 'array'],
            'contact_consent' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:'.$uploads['max_files']],
            'attachments.*' => [
                'file',
                'mimetypes:'.implode(',', [...$uploads['image_mimes'], ...$uploads['video_mimes']]),
                'extensions:jpg,jpeg,png,webp,mp4,mov,webm',
                'max:'.$uploads['max_video_kb'],
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
            'service_id.required' => 'Please choose the service you need.',
            'service_id.exists' => 'Please choose one of the available services.',
            'preferred_date.after_or_equal' => 'The preferred date cannot be in the past.',
            'preferred_time.date_format' => 'Please enter a valid time.',
            'contact_consent.accepted' => 'Please agree to be contacted about your request.',
            'attachments.max' => 'You can upload up to '.config('crystalmovers.uploads.max_files').' files.',
            'attachments.*.mimetypes' => 'Only JPG, PNG, WebP photos or MP4, MOV, WebM videos are allowed.',
            'attachments.*.extensions' => 'Only JPG, PNG, WebP photos or MP4, MOV, WebM videos are allowed.',
            'attachments.*.max' => 'A file is too large. Videos can be up to '.round(config('crystalmovers.uploads.max_video_kb') / 1024).' MB.',
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $uploads = config('crystalmovers.uploads');

            // Moving requests need a pickup and a delivery location; other work needs the job location.
            $service = Service::query()->with('category:id,slug')->find($this->input('service_id'));
            if ($service !== null) {
                if ($service->category?->slug === 'moving') {
                    foreach (['from_location' => 'moving from', 'to_location' => 'moving to'] as $field => $label) {
                        if (! $this->filled($field)) {
                            $validator->errors()->add($field, "Please tell us where you are {$label}.");
                        }
                    }
                } elseif (! $this->filled('location')) {
                    $validator->errors()->add('location', 'Please tell us the location of the work.');
                }
            }

            foreach ((array) $this->file('attachments') as $index => $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    continue;
                }

                $isImage = str_starts_with((string) $file->getMimeType(), 'image/');

                // Photos have a smaller limit than videos.
                if ($isImage && $file->getSize() > $uploads['max_image_kb'] * 1024) {
                    $validator->errors()->add("attachments.$index", 'Photos can be up to '.round($uploads['max_image_kb'] / 1024).' MB each.');
                }

                // A real photo must decode as an image, not just carry an image MIME header.
                if ($isImage && @getimagesize($file->getRealPath()) === false) {
                    $validator->errors()->add("attachments.$index", 'One of the photos could not be read. Please upload a different file.');
                }
            }
        }];
    }
}
