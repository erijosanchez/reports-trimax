<?php

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('format_duration')) {
    function format_duration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        } else {
            return sprintf('%ds', $secs);
        }
    }
}

if (!function_exists('is_online')) {
    function is_online(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return false;
        }

        $user = \App\Models\User::find($userId);
        return $user ? $user->isOnline() : false;
    }
}

if (!function_exists('log_activity')) {
    function log_activity(
        string $action,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?string $description = null
    ): void {
        if (!auth()->check()) {
            return;
        }

        \App\Services\ActivityLogService::log(
            auth()->id(),
            $action,
            $resourceType,
            $resourceId,
            $description
        );
    }
}

if (!function_exists('get_user_ip')) {
    function get_user_ip(): string
    {
        return request()->ip();
    }
}