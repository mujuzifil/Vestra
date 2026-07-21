<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity';

    protected int | string | array $columnSpan = ['lg' => 2];

    public function getActivities(): array
    {
        return Cache::remember('admin.dashboard.recent_activity', 300, function (): array {
            $logs = AuditLog::query()
                ->with('user')
                ->whereNotIn('action', ['password_change.required', 'password_changed', 'password_change.bypass_attempt'])
                ->where('action', 'not like', '%login%')
                ->latest()
                ->limit(15)
                ->get();

            return $logs->map(function (AuditLog $log): array {
                return [
                    'id' => $log->id,
                    'icon' => $this->actionIcon($log->action),
                    'color' => $this->actionColor($log->action),
                    'actor' => $log->user?->name ?? 'System',
                    'action' => $this->actionLabel($log->action),
                    'subject' => $this->subjectLabel($log),
                    'time' => $log->created_at?->diffForHumans() ?? '',
                ];
            })->toArray();
        });
    }

    private function actionIcon(string $action): string
    {
        return match (true) {
            str_contains($action, 'password') => 'heroicon-o-key',
            str_contains($action, 'login') => 'heroicon-o-arrow-right-end-on-rectangle',
            str_contains($action, 'order') => 'heroicon-o-shopping-cart',
            str_contains($action, 'product') => 'heroicon-o-shopping-bag',
            str_contains($action, 'customer') => 'heroicon-o-users',
            str_contains($action, 'review') => 'heroicon-o-star',
            str_contains($action, 'setting') => 'heroicon-o-cog-6-tooth',
            str_contains($action, 'contact') => 'heroicon-o-envelope',
            default => 'heroicon-o-bolt',
        };
    }

    private function actionColor(string $action): string
    {
        return match (true) {
            str_contains($action, 'delete') => 'danger',
            str_contains($action, 'create') => 'success',
            str_contains($action, 'update') => 'info',
            str_contains($action, 'login') => 'primary',
            str_contains($action, 'password') => 'warning',
            default => 'gray',
        };
    }

    private function actionLabel(string $action): string
    {
        return str_replace(['.', '_'], ' ', $action);
    }

    private function subjectLabel(AuditLog $log): string
    {
        $subject = $log->subject;

        if (! $subject) {
            return '';
        }

        if (method_exists($subject, 'getAttribute') && $subject->getAttribute('name')) {
            return $subject->name;
        }

        if (method_exists($subject, 'getAttribute') && $subject->getAttribute('invoice_number')) {
            return $subject->invoice_number;
        }

        return class_basename($log->subject_type) . ' #' . $log->subject_id;
    }
}
