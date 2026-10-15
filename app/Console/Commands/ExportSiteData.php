<?php

namespace App\Console\Commands;

use App\Services\SiteDataService;
use Illuminate\Console\Command;

class ExportSiteData extends Command
{
    protected $signature = 'site:export';

    protected $description = 'Export the public website data from PostgreSQL to storage/app/site-data.json (input for the prerender build)';

    public function handle(SiteDataService $site): int
    {
        $site->forget(); // always export fresh data

        $path = storage_path('app/site-data.json');
        $json = json_encode(
            ['hash' => $site->hash(), 'data' => $site->payload()],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        );

        file_put_contents($path, $json);
        $this->info('Exported site data to storage/app/site-data.json');

        return self::SUCCESS;
    }
}
