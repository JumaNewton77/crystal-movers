<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class PrerenderSite extends Command
{
    protected $signature = 'site:prerender';

    protected $description = 'Export database content and prerender the public pages to static HTML (requires `npm run build` first)';

    public function handle(): int
    {
        if (! is_file(base_path('bootstrap/ssr/entry-server.js'))) {
            $this->error('The SSR bundle is missing. Run `npm run build` first.');

            return self::FAILURE;
        }

        $this->call('site:export');

        $result = Process::path(base_path())->timeout(300)->run(['node', 'scripts/prerender.mjs']);
        $this->line(trim($result->output()));

        if ($result->failed()) {
            $this->error(trim($result->errorOutput()) ?: 'Prerender failed.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
