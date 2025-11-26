<?php

return [
    'application_name' => env('GOOGLE_APPLICATION_NAME', 'Reports Trimax'),
    'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),
    'service_account_file' => storage_path('app/google/service-account.json'),
];