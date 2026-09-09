<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Inertia looks for page components under "js/pages" by default; this app
    | keeps them in "js/Pages" alongside Components and Layouts. Pointing the
    | finder at the right directory is what lets assertInertia() catch a
    | controller that renders a component nobody ever wrote.
    |
    */

    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => [
            resource_path('js/Pages'),
        ],

        'extensions' => ['js', 'jsx'],

    ],

    'testing' => [

        'ensure_pages_exist' => true,

    ],

];
