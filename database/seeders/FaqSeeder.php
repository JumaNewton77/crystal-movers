<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Support\Content;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Content::seed('faqs') as $index => $item) {
            Faq::firstOrCreate(
                ['question' => $item['question']],
                ['answer' => $item['answer'], 'is_published' => true, 'sort_order' => $index + 1],
            );
        }
    }
}
