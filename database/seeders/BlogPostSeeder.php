<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Support\Content;
use Illuminate\Database\Seeder;

/**
 * Starter articles, saved as DRAFTS. They are general guidance written for this project, not company claims.
 * The owner must read, edit and publish them in Admin > Blog. Re-running never overwrites edits.
 */
class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Content::seed('blog_posts') as $index => $item) {
            BlogPost::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'body' => $item['body'],
                    'is_published' => false,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
