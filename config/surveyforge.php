<?php

// config for Surveyforge/Surveyforge
return [
    'redirect_route' => null,
    'servers' => [
        'default' => [
            'id'=> env('SURVEYFORGE_SERVER_ID'),
            'url' => env('SURVEYFORGE_SERVER_URL'),
            'token' => env('SURVEYFORGE_AUTH_TOKEN'),
        ]
    ]
];
