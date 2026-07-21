<?php

namespace App\Filament\Pages\Administration;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SystemHealth extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'System Health';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.administration.system-health';

    public function getTitle(): string|Htmlable
    {
        return 'System Health';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getHealthChecks(): array
    {
        return [
            $this->checkDatabase(),
            $this->checkCache(),
            $this->checkQueue(),
            $this->checkStorage(),
            $this->checkMail(),
            $this->checkScheduler(),
        ];
    }

    public function getEnvironmentInfo(): array
    {
        return [
            'Laravel Version' => app()->version(),
            'PHP Version' => phpversion(),
            'Environment' => app()->environment(),
            'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'Timezone' => config('app.timezone'),
            'Locale' => config('app.locale'),
        ];
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'name' => 'Database',
                'status' => 'healthy',
                'message' => 'Connected to '.DB::connection()->getDriverName(),
                'icon' => 'heroicon-o-circle-stack',
            ];
        } catch (\Throwable $e) {
            return [
                'name' => 'Database',
                'status' => 'critical',
                'message' => $e->getMessage(),
                'icon' => 'heroicon-o-circle-stack',
            ];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = 'health-check-'.now()->timestamp;
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value === 'ok') {
                return [
                    'name' => 'Cache',
                    'status' => 'healthy',
                    'message' => 'Cache store ('.config('cache.default').') is writable and readable.',
                    'icon' => 'heroicon-o-bolt',
                ];
            }

            return [
                'name' => 'Cache',
                'status' => 'warning',
                'message' => 'Cache read/write mismatch.',
                'icon' => 'heroicon-o-bolt',
            ];
        } catch (\Throwable $e) {
            return [
                'name' => 'Cache',
                'status' => 'critical',
                'message' => $e->getMessage(),
                'icon' => 'heroicon-o-bolt',
            ];
        }
    }

    protected function checkQueue(): array
    {
        $connection = config('queue.default');

        return [
            'name' => 'Queue',
            'status' => 'healthy',
            'message' => 'Default queue connection is '.$connection.'.',
            'icon' => 'heroicon-o-queue-list',
        ];
    }

    protected function checkStorage(): array
    {
        $disk = Storage::disk(config('filesystems.default'));

        try {
            $path = 'health-check-'.now()->timestamp.'.txt';
            $disk->put($path, 'ok');
            $value = $disk->get($path);
            $disk->delete($path);

            if ($value === 'ok') {
                return [
                    'name' => 'Storage',
                    'status' => 'healthy',
                    'message' => 'Filesystem disk ('.config('filesystems.default').') is writable.',
                    'icon' => 'heroicon-o-folder',
                ];
            }

            return [
                'name' => 'Storage',
                'status' => 'warning',
                'message' => 'Storage read/write mismatch.',
                'icon' => 'heroicon-o-folder',
            ];
        } catch (\Throwable $e) {
            return [
                'name' => 'Storage',
                'status' => 'critical',
                'message' => $e->getMessage(),
                'icon' => 'heroicon-o-folder',
            ];
        }
    }

    protected function checkMail(): array
    {
        $mailer = config('mail.default');

        if ($mailer === 'log') {
            return [
                'name' => 'Mail',
                'status' => 'warning',
                'message' => 'Mailer is set to log; emails are not being delivered.',
                'icon' => 'heroicon-o-envelope',
            ];
        }

        return [
            'name' => 'Mail',
            'status' => 'healthy',
            'message' => 'Mailer ('.$mailer.') is configured.',
            'icon' => 'heroicon-o-envelope',
        ];
    }

    protected function checkScheduler(): array
    {
        return [
            'name' => 'Scheduler',
            'status' => 'warning',
            'message' => 'Scheduler status requires a heartbeat or monitoring integration.',
            'icon' => 'heroicon-o-clock',
        ];
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'healthy' => 'success',
            'warning' => 'warning',
            'critical' => 'danger',
            default => 'gray',
        };
    }
}
