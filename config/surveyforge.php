<?php

// config for Surveyforge/Surveyforge
return [
    'servers' => [
        'default' => [
            'url' => env('SURVEYFORGE_SERVER_URL'),
            'token' => env('SURVEYFORGE_AUTH_TOKEN'),
        ]
    ]
];
