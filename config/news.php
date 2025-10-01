<?php

return [
    'timezone' => env('APP_TIMEZONE', 'Asia/Dubai'),

    'sources' => [
        'newsdata' => [
            'slug' => 'newsdata',
            'name' => 'NewsData.io',
            'base_url' => 'https://newsdata.io/api/1/latest',
            'api_key' => env('NEWSDATA_API_KEY'),
            'default_params' => [
                'q' => 'US blogs',
                'prioritydomain' => 'top',
            ],
        ],
        'newsapi' => [
            'slug' => 'newsapi',
            'name' => 'NewsAPI.org',
            'base_url' => 'https://newsapi.org/v2/everything',
            'api_key' => env('NEWSAPI_API_KEY'),
            'default_params' => [
                'q' => 'blogs',
            ],
        ],
        'guardian' => [
            'slug'  => 'guardian',
            'name'  => 'The Guardian',
            'base_url' => 'https://content.guardianapis.com/search',
            'api_key'  => env('GUARDIAN_API_KEY'),
            'default_params' => [
                'q' => 'apple',
                'page_size' => 50,
            ],
        ],
    ],
];
