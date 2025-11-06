<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IpBlacklist;

class ManageIpBlacklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ip:manage 
                            {action : block, unblock, list, cleanup}
                            {--ip= : IP address to block/unblock}
                            {--reason= : Reason for blocking}
                            {--duration= : Block duration in minutes (null = permanent)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage IP blacklist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'block':
                $this->blockIp();
                break;
            case 'unblock':
                $this->unblockIp();
                break;
            case 'list':
                $this->listBlocked();
                break;
            case 'cleanup':
                $this->cleanupExpired();
                break;
            default:
                $this->error('Invalid action. Use: block, unblock, list, or cleanup');
        }
    }

    protected function blockIp()
    {
        $ip = $this->option('ip');
        $reason = $this->option('reason') ?? 'Blocked manually';
        $duration = $this->option('duration');

        if (!$ip) {
            $this->error('IP address is required. Use --ip=x.x.x.x');
            return;
        }

        IpBlacklist::blockIp($ip, $reason, $duration);

        if ($duration) {
            $this->info("IP {$ip} blocked for {$duration} minutes");
        } else {
            $this->info("IP {$ip} blocked permanently");
        }
    }

    protected function unblockIp()
    {
        $ip = $this->option('ip');

        if (!$ip) {
            $this->error('IP address is required. Use --ip=x.x.x.x');
            return;
        }

        if (IpBlacklist::unblockIp($ip)) {
            $this->info("IP {$ip} unblocked successfully");
        } else {
            $this->warn("IP {$ip} was not found in blacklist");
        }
    }

    protected function listBlocked()
    {
        $blocked = IpBlacklist::active()->get();

        if ($blocked->isEmpty()) {
            $this->info('No IPs are currently blocked');
            return;
        }

        $this->table(
            ['IP Address', 'Reason', 'Blocked Until', 'Created At'],
            $blocked->map(function ($item) {
                return [
                    $item->ip_address,
                    $item->reason,
                    $item->blocked_until ? $item->blocked_until->format('Y-m-d H:i:s') : 'Permanent',
                    $item->created_at->format('Y-m-d H:i:s'),
                ];
            })
        );
    }

    protected function cleanupExpired()
    {
        $deleted = IpBlacklist::where('blocked_until', '<', now())
            ->whereNotNull('blocked_until')
            ->delete();

        $this->info("Cleaned up {$deleted} expired IP blocks");
    }
}
