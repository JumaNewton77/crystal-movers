<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MoveType;
use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;

/** Small read-only lists used to fill dropdowns in the admin forms. */
class LookupController extends Controller
{
    public function serviceCategories(): JsonResponse
    {
        return $this->ok(ServiceCategory::orderBy('sort_order')->get(['id', 'name']));
    }

    /** All services (any status) with their category, for the enquiry filters. */
    public function services(): JsonResponse
    {
        return $this->ok(
            Service::query()->orderBy('sort_order')->get(['id', 'title', 'service_category_id'])
                ->map(fn (Service $s) => ['id' => $s->id, 'name' => $s->title, 'category_id' => $s->service_category_id])->values()
        );
    }

    public function galleryCategories(): JsonResponse
    {
        return $this->ok(GalleryCategory::orderBy('sort_order')->get(['id', 'name']));
    }

    public function quoteOptions(): JsonResponse
    {
        return $this->ok([
            'statuses' => collect(QuoteStatus::cases())->map(fn (QuoteStatus $s) => ['value' => $s->value, 'label' => $s->label()])->all(),
            'move_types' => collect(MoveType::cases())->map(fn (MoveType $m) => ['value' => $m->value, 'label' => $m->label()])->all(),
        ]);
    }
}
