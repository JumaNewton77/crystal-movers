<?php

namespace Tests;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function admin(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => 'correct-password-123',
            'role' => 'admin',
            'is_active' => true,
        ], $attributes));
    }

    /** Creates a category and a published, active service in it (moving = needs from/to locations). */
    protected function makeService(string $categorySlug = 'moving', array $overrides = []): Service
    {
        $category = ServiceCategory::firstOrCreate(
            ['slug' => $categorySlug],
            ['name' => ucfirst($categorySlug), 'is_published' => true, 'sort_order' => 1],
        );

        return Service::create(array_merge([
            'service_category_id' => $category->id,
            'slug' => $categorySlug.'-service-'.(Service::count() + 1),
            'title' => ucfirst($categorySlug).' service '.(Service::count() + 1),
            'short_description' => 'Test service.',
            'status' => 'active',
            'is_published' => true,
            'sort_order' => Service::count() + 1,
        ], $overrides));
    }

    /** Valid public quote payload for a (lazily created) published moving service. */
    protected function quotePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Wanjiku',
            'phone' => '+254 712 345 678',
            'email' => 'jane@example.com',
            'service_id' => Service::query()->where('is_published', true)->where('status', 'active')
                ->whereHas('category', fn ($q) => $q->where('slug', 'moving'))->value('id') ?? $this->makeService('moving')->id,
            'contact_consent' => '1',
            'from_location' => 'Kilimani, Nairobi',
            'to_location' => 'Nyali, Mombasa',
            'preferred_date' => now('Africa/Nairobi')->addDays(10)->toDateString(),
            'message' => 'Two bedroom house.',
        ], $overrides);
    }
}
