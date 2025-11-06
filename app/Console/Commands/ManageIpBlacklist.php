<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IpBlacklist;

class ManageIpBlacklist extends Command
{
    protected $signature = 'ip:manage 
                            {action : block, unblock, list, cleanup}
                            {--ip= : IP address}
                            {--reason= : Reason for blocking}
                            {--duration= : Duration in minutes}';

    protected $description = 'Manage IP blacklist';

    public function handle()
    {
        match ($this->argument('action')) {
            'block' => $this->blockIp(),
            'unblock' => $this->unblockIp(),
            'list' => $this->listBlocked(),
            'cleanup' => $this->cleanupExpired(),
            default => $this->error('Invalid action'),
        };
    }

    protected function blockIp()
    {
        $ip = $this->option('ip');
        if (!$ip) {
            $this->error('IP required');
            return;
        }

        IpBlacklist::blockIp(
            $ip,
            $this->option('reason') ?? 'Blocked manually',
            $this->option('duration')
        );

        $this->info("IP {$ip} blocked");
    }

    protected function unblockIp()
    {
        $ip = $this->option('ip');
        if (!$ip) {
            $this->error('IP required');
            return;
        }

        IpBlacklist::unblockIp($ip);
        $this->info("IP {$ip} unblocked");
    }

    protected function listBlocked()
    {
        $blocked = IpBlacklist::active()->get();

        if ($blocked->isEmpty()) {
            $this->info('No blocked IPs');
            return;
        }

        $this->table(
            ['IP', 'Reason', 'Until', 'Created'],
            $blocked->map(fn($i) => [
                $i->ip_address,
                $i->reason,
                $i->blocked_until?->format('Y-m-d H:i') ?? 'Permanent',
                $i->created_at->format('Y-m-d H:i'),
            ])
        );
    }

    protected function cleanupExpired()
    {
        $deleted = IpBlacklist::where('blocked_until', '<', now())
            ->whereNotNull('blocked_until')
            ->delete();

        $this->info("Cleaned up {$deleted} expired blocks");
    }
}
