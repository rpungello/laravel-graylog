<?php

return [
    'https' => env('GRAYLOG_API_HTTPS', false),
    'host' => env('GRAYLOG_API_HOST'),
    'port' => env('GRAYLOG_API_PORT', 9000),
    'token' => env('GRAYLOG_API_TOKEN'),
];
