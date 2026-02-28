<?php

return [
    'routes' => [
        // Main page
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Document API proxy
        ['name' => 'api#documents', 'url' => '/api/documents', 'verb' => 'GET'],
        ['name' => 'api#thumbnail', 'url' => '/api/documents/{id}/thumb', 'verb' => 'GET'],
        ['name' => 'api#preview', 'url' => '/api/documents/{id}/preview', 'verb' => 'GET'],
        ['name' => 'api#download', 'url' => '/api/documents/{id}/download', 'verb' => 'GET'],

        // Metadata API proxy
        ['name' => 'api#tags', 'url' => '/api/tags', 'verb' => 'GET'],
        ['name' => 'api#correspondents', 'url' => '/api/correspondents', 'verb' => 'GET'],
        ['name' => 'api#documentTypes', 'url' => '/api/document_types', 'verb' => 'GET'],

        // Search
        ['name' => 'api#search', 'url' => '/api/search', 'verb' => 'GET'],

        // Admin settings
        ['name' => 'api#getSettings', 'url' => '/api/settings', 'verb' => 'GET'],
        ['name' => 'api#saveSettings', 'url' => '/api/settings', 'verb' => 'POST'],
    ],
];
