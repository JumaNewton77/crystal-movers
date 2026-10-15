<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        // Private files (e.g. customer quote attachments). Never web-accessible;
        // served to admins only through an authorised controller.
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => true,
            'report' => false,
        ],

        // Public media managed from the admin area (gallery, logo, service images).
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => true,
            'report' => false,
        ],

        // To move media to S3 later: composer require league/flysystem-aws-s3-v3
        // and add an "s3" disk here. No application code needs to change.

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
