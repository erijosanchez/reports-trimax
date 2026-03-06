<?php

return [
    'application_name' => env('GOOGLE_APPLICATION_NAME', 'Reports Trimax'),
    'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),
    'lead_time_spreadsheet_id' => env('GOOGLE_LEAD_TIME_SPREADSHEET_ID', ''), //Sheet Lead Time
    'venta_clientes_spreadsheet_id' => env('GOOGLE_VENTA_CLIENTES_SPREADSHEET_ID', ''), //Sheet Venta Clientes Evolutivo
    'service_account_file' => storage_path('app/google/service-account.json'),
];