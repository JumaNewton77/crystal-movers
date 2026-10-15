<?php

namespace App\Console\Commands;

use App\Models\Faq;
use App\Models\Service;
use App\Models\Setting;
use App\Models\WebsiteContent;
use App\Services\SiteDataService;
use App\Support\Content;
use App\Support\CopyDefaults;
use Database\Seeders\BlogPostSeeder;
use Database\Seeders\GalleryCategorySeeder;
use Illuminate\Console\Command;

/**
 * One-time helper for an EXISTING database. Seeders only insert missing rows, so they never change copy that is
 * already stored. This command deliberately overwrites the official Crystal Movers copy and business defaults
 * (it replaces any edits you made to those particular texts), so it asks for confirmation first.
 *
 *   php artisan site:apply-copy             # asks before changing anything
 *   php artisan site:apply-copy --force     # no prompt
 *   php artisan site:apply-copy --retire-old-faqs   # also hides FAQs that are not in the official list
 */
class ApplySiteCopy extends Command
{
    protected $signature = 'site:apply-copy {--force : Do not ask for confirmation} {--retire-old-faqs : Unpublish FAQs that are not in the official list}';

    protected $description = 'Apply the official Crystal Movers website copy, tagline, phone and hours to the existing database';

    public function handle(SiteDataService $site): int
    {
        if (! $this->option('force') && ! $this->confirm('This overwrites the homepage/About/Why Choose/CTA/Safety copy, the tagline, phone, address, working hours and Safety heading with the official text. Continue?')) {
            $this->warn('Nothing changed.');

            return self::FAILURE;
        }

        foreach (CopyDefaults::content() as $group => $items) {
            foreach ($items as $key => $value) {
                WebsiteContent::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            }
        }

        foreach (CopyDefaults::settings() as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            }
        }

        foreach (CopyDefaults::SERVICE_SHORT as $slug => $text) {
            Service::query()->where('slug', $slug)->update(['short_description' => $text]);
        }

        // Earlier builds shipped invented placeholder addresses. Never leave them in place as if they were real.
        foreach (['email', 'notification_email'] as $key) {
            $row = Setting::query()->where('key', $key)->first();
            if ($row && in_array(strtolower(trim((string) $row->value)), CopyDefaults::PLACEHOLDER_EMAILS, true)) {
                $row->update(['value' => null]);
                $this->warn("Cleared placeholder value of '{$key}'. Enter the confirmed address in Admin > Settings.");
            }
        }

        $official = [];
        foreach (Content::seed('faqs') as $index => $item) {
            $official[] = $item['question'];
            Faq::updateOrCreate(
                ['question' => $item['question']],
                ['answer' => $item['answer'], 'is_published' => true, 'sort_order' => $index + 1],
            );
        }

        if ($this->option('retire-old-faqs')) {
            $count = Faq::query()->whereNotIn('question', $official)->update(['is_published' => false]);
            $this->info("Unpublished {$count} FAQ(s) that are not in the official list (still in Admin > FAQs).");
        }

        // Missing gallery categories and the starter blog drafts (insert only, never overwrites).
        (new GalleryCategorySeeder)->run();
        (new BlogPostSeeder)->run();

        $site->forget();
        $this->info('Official copy applied. If you use the prerender build, run: php artisan site:prerender');
        $this->line('Still to confirm in Admin > Settings: business email, WhatsApp number, social links, Google Business Profile link.');

        return self::SUCCESS;
    }
}
