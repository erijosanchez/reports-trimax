<?php

return [
    'application_name' => env('GOOGLE_APPLICATION_NAME', 'Reports Trimax'),
    'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),
    'lead_time_spreadsheet_id' => env('GOOGLE_LEAD_TIME_SPREADSHEET_ID', ''),
    'service_account_file' => storage_path('app/google/service-account.json'),
];