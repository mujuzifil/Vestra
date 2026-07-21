<?php

namespace App\Filament\Pages\Settings;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class SystemInformation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'System Information';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settings.system-information';

    protected static ?string $slug = 'system-information';

    public function getTitle(): string|Htmlable
    {
        return 'System Information';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getEnvironmentInfo(): array
    {
        return [
            'Application Name' => config('app.name'),
            'Environment' => app()->environment(),
            'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'URL' => config('app.url'),
            'Timezone' => config('app.timezone'),
            'Locale' => config('app.locale'),
        ];
    }

    public function getFrameworkInfo(): array
    {
        return [
            'Laravel Version' => app()->version(),
            'PHP Version' => phpversion(),
            'PHP SAPI' => php_sapi_name(),
            'Composer Version' => $this->getComposerVersion(),
        ];
    }

    public function getDatabaseInfo(): array
    {
        $connection = DB::connection();

        return [
            'Driver' => $connection->getDriverName(),
            'Database' => $connection->getDatabaseName(),
            'Host' => $connection->getConfig('host') ?? 'N/A',
            'Port' => $connection->getConfig('port') ?? 'N/A',
        ];
    }

    public function getCacheInfo(): array
    {
        return [
            'Default Cache Store' => config('cache.default'),
            'Prefix' => config('cache.prefix'),
        ];
    }

    public function getQueueInfo(): array
    {
        return [
            'Default Queue Connection' => config('queue.default'),
        ];
    }

    public function getFilesystemInfo(): array
    {
        return [
            'Default Filesystem Disk' => config('filesystems.default'),
            'Public Disk' => config('filesystems.disks.public.driver'),
        ];
    }

    public function getMailInfo(): array
    {
        return [
            'Default Mailer' => config('mail.default'),
            'From Address' => config('mail.from.address'),
            'From Name' => config('mail.from.name'),
        ];
    }

    public function getSections(): array
    {
        return [
            [
                'heading' => 'Application',
                'icon' => 'heroicon-o-rocket-launch',
                'items' => $this->getEnvironmentInfo(),
            ],
            [
                'heading' => 'Framework & Runtime',
                'icon' => 'heroicon-o-code-bracket',
                'items' => $this->getFrameworkInfo(),
            ],
            [
                'heading' => 'Database',
                'icon' => 'heroicon-o-circle-stack',
                'items' => $this->getDatabaseInfo(),
            ],
            [
                'heading' => 'Cache',
                'icon' => 'heroicon-o-bolt',
                'items' => $this->getCacheInfo(),
            ],
            [
                'heading' => 'Queue',
                'icon' => 'heroicon-o-queue-list',
                'items' => $this->getQueueInfo(),
            ],
            [
                'heading' => 'Filesystem',
                'icon' => 'heroicon-o-folder',
                'items' => $this->getFilesystemInfo(),
            ],
            [
                'heading' => 'Mail',
                'icon' => 'heroicon-o-envelope',
                'items' => $this->getMailInfo(),
            ],
        ];
    }

    private function getComposerVersion(): string
    {
        $composerLock = base_path('composer.lock');

        if (! file_exists($composerLock)) {
            return 'N/A';
        }

        $lock = json_decode(file_get_contents($composerLock), true);

        foreach ($lock['packages'] ?? [] as $package) {
            if (($package['name'] ?? '') === 'laravel/framework') {
                return $package['version'] ?? 'N/A';
            }
        }

        return 'N/A';
    }
}
