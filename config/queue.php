<?php

return [

    // "sync" keeps Phase 1 free of queue workers. Emails are sent inline and
    // failures are caught/logged by the submission handlers (added in Step 3).
    // Switch to "database" later by adding the jobs tables and running a worker.
    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'null'),
    ],

];
