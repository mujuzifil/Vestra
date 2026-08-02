<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity';

    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = ['lg' => 1];

    public function getActivities(): array
    {
        return Cache::remember('admin.dashboard.recent_activity', 300, function (): array {
            $logs = AuditLog::query()
                ->with('user')
                ->whereNotIn('action', ['password_change.required', 'password_changed', 'password_change.bypass_attempt'])
                ->where('action', 'not like', '%login%')
                ->latest()
                ->limit(8)
                ->get();

            return $logs->map(function (AuditLog $log): array {
                return [
                    'id' => $log->id,
                    'icon' => $this->actionIcon($log->action),
                    'color' => $this->actionColor($log->action),
                    'title' => $this->actionTitle($log),
                    'subtitle' => $this->actionSubtitle($log),
                    'time' => $log->created_at?->diffForHumans() ?? '',
                    'url' => $this->actionUrl($log),
                ];
            })->toArray();
        });
    }

    private function actionTitle(AuditLog $log): string
    {
        $action = $log->action;
        $subject = $this->subjectName($log);

        return match (true) {
            str_contains($action, 'quote_request') => "New quote request from {$subject}",
            str_contains($action, 'distributor_request') => "Distributor application submitted by {$subject}",
            str_contains($action, 'contact_message') => "New contact message from {$subject}",
            str_contains($action, 'customer') => "New customer {$subject}",
            str_contains($action, 'support_ticket') => "Support ticket {$subject} updated",
            str_contains($action, 'blog_post') => "Blog post \"{$subject}\" published",
            str_contains($action, 'product') => "Product {$subject} updated",
            str_contains($action, 'user') => "User {$subject} updated",
            default => ucfirst(str_replace(['.', '_'], ' ', $action)),
        };
    }

    private function actionSubtitle(AuditLog $log): string
    {
        $action = $log->action;
        $subject = $log->subject;
        $identifier = $this->subjectIdentifier($log);
        $actor = $log->user?->name ?? 'System';

        return match (true) {
            str_contains($action, 'quote_request') => "Quote #{$identifier}",
            str_contains($action, 'distributor_request') => "Application #{$identifier}",
            str_contains($action, 'contact_message') => $subject?->subject ? "Subject: {$subject->subject}" : "From {$actor}",
            str_contains($action, 'support_ticket') => "by {$actor}",
            str_contains($action, 'blog_post') => "by {$actor}",
            default => "by {$actor}",
        };
    }

    private function subjectName(AuditLog $log): string
    {
        $subject = $log->subject;

        if (! $subject) {
            return '#' . $log->subject_id;
        }

        return $subject->company_name
            ?? $subject->full_name
            ?? $subject->name
            ?? $subject->title
            ?? $subject->reference_number
            ?? ('#' . $log->subject_id);
    }

    private function subjectIdentifier(AuditLog $log): string
    {
        $subject = $log->subject;

        return $subject?->reference_number
            ?? $subject?->invoice_number
            ?? (string) $log->subject_id;
    }

    private function actionUrl(AuditLog $log): ?string
    {
        $subject = $log->subject;

        if (! $subject) {
            return null;
        }

        $resource = match (class_basename($log->subject_type)) {
            'QuoteRequest' => 'quote-requests',
            'DistributorRequest' => 'distributor-requests',
            'ContactMessage' => 'contact-messages',
            'CustomerFeedback' => 'customer-feedback',
            'BlogPost' => 'blog-posts',
            'Product' => 'products',
            'User' => 'users',
            default => null,
        };

        return $resource ? url("/admin/{$resource}/{$log->subject_id}") : null;
    }

    private function actionIcon(string $action): string
    {
        return match (true) {
            str_contains($action, 'quote_request') => 'heroicon-o-document-text',
            str_contains($action, 'distributor_request') => 'heroicon-o-user-group',
            str_contains($action, 'contact_message') => 'heroicon-o-envelope',
            str_contains($action, 'customer') => 'heroicon-o-users',
            str_contains($action, 'support_ticket') => 'heroicon-o-ticket',
            str_contains($action, 'blog_post') => 'heroicon-o-newspaper',
            str_contains($action, 'product') => 'heroicon-o-cube',
            str_contains($action, 'order') => 'heroicon-o-shopping-cart',
            str_contains($action, 'setting') => 'heroicon-o-cog-6-tooth',
            default => 'heroicon-o-bolt',
        };
    }

    private function actionColor(string $action): string
    {
        return match (true) {
            str_contains($action, 'delete') => 'danger',
            str_contains($action, 'create') || str_contains($action, 'submitted') => 'success',
            str_contains($action, 'update') || str_contains($action, 'approved') => 'primary',
            str_contains($action, 'login') => 'info',
            default => 'gray',
        };
    }
}
