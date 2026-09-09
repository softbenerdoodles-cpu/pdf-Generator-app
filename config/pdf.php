<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Disk and directory that rendered PDF files are written to. The default
    | "local" disk keeps documents out of the public web root; they are only
    | reachable through the download route.
    |
    */

    'disk' => env('PDF_DISK', 'local'),

    'directory' => env('PDF_DIRECTORY', 'documents'),

    /*
    |--------------------------------------------------------------------------
    | Default paper
    |--------------------------------------------------------------------------
    |
    | Used when a template does not declare its own paper size or orientation.
    |
    */

    'paper' => [
        'size' => env('PDF_PAPER_SIZE', 'a4'),
        'orientation' => env('PDF_PAPER_ORIENTATION', 'portrait'),
    ],

];
