<?php

namespace App\Services;

use App\Enums\WorkflowStatus;
use App\Models\AutomatedWorkflow;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

class WorkflowService
{
    /**
     * Trigger all active workflows for a given event.
     */
    public function trigger(string $event, array $payload): void
    {
        $workflows = AutomatedWorkflow::active()
            ->where('event', $event)
            ->get();

        foreach ($workflows as $workflow) {
            if (! $this->conditionsMatch($workflow->conditions ?? [], $payload)) {
                continue;
            }

            $this->executeAction($workflow, $payload);
            $workflow->recordRun();
        }
    }

    /**
     * Evaluate workflow conditions against the event payload.
     */
    protected function conditionsMatch(array $conditions, array $payload): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $key = $condition['key'] ?? null;
            $operator = $condition['operator'] ?? 'equals';
            $expected = $condition['value'] ?? null;

            if (empty($key)) {
                continue;
            }

            $actual = Arr::get($payload, $key);

            if (! $this->evaluateCondition($operator, $actual, $expected)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single condition.
     */
    protected function evaluateCondition(string $operator, mixed $actual, mixed $expected): bool
    {
        return match ($operator) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'greater_than' => is_numeric($actual) && is_numeric($expected) && $actual > $expected,
            'less_than' => is_numeric($actual) && is_numeric($expected) && $actual < $expected,
            'contains' => is_string($actual) && str_contains($actual, (string) $expected),
            'exists' => $actual !== null,
            default => $actual == $expected,
        };
    }

    /**
     * Execute the workflow's configured action.
     */
    protected function executeAction(AutomatedWorkflow $workflow, array $payload): void
    {
        $action = $workflow->action;
        $config = $this->parseKeyValueConfig($workflow->action_config ?? []);

        match ($action) {
            'notification' => $this->executeNotification($workflow, $config, $payload),
            'email' => $this->executeEmail($workflow, $config, $payload),
            'status_change' => $this->executeStatusChange($workflow, $config, $payload),
            default => null,
        };
    }

    /**
     * Send a Filament notification to the configured recipient.
     */
    protected function executeNotification(AutomatedWorkflow $workflow, array $config, array $payload): void
    {
        $recipientId = $config['user_id'] ?? $payload['user_id'] ?? null;
        $title = $config['title'] ?? $workflow->name;
        $body = $config['body'] ?? "Workflow '{$workflow->name}' was triggered for {$workflow->event}.";

        if (! $recipientId) {
            AuditService::log(
                null,
                'workflow.notification_skipped',
                $workflow,
                ['reason' => 'No recipient configured']
            );

            return;
        }

        $user = User::find($recipientId);

        if (! $user) {
            AuditService::log(
                null,
                'workflow.notification_skipped',
                $workflow,
                ['reason' => 'Recipient not found', 'user_id' => $recipientId]
            );

            return;
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->success()
            ->sendToDatabase($user);

        AuditService::log(
            null,
            'workflow.notification_sent',
            $workflow,
            ['recipient_id' => $recipientId, 'title' => $title]
        );
    }

    /**
     * Placeholder for email action integration.
     */
    protected function executeEmail(AutomatedWorkflow $workflow, array $config, array $payload): void
    {
        AuditService::log(
            null,
            'workflow.email_placeholder',
            $workflow,
            [
                'to' => $config['to'] ?? $payload['email'] ?? null,
                'subject' => $config['subject'] ?? $workflow->name,
            ]
        );
    }

    /**
     * Placeholder for status change action integration.
     */
    protected function executeStatusChange(AutomatedWorkflow $workflow, array $config, array $payload): void
    {
        $targetType = $config['target_type'] ?? $payload['subject_type'] ?? null;
        $targetId = $config['target_id'] ?? $payload['subject_id'] ?? null;
        $newStatus = $config['status'] ?? null;

        AuditService::log(
            null,
            'workflow.status_change_placeholder',
            $workflow,
            [
                'target_type' => $targetType,
                'target_id' => $targetId,
                'new_status' => $newStatus,
            ]
        );
    }

    /**
     * Convert a key-value repeater array into an associative array.
     */
    protected function parseKeyValueConfig(array $config): array
    {
        $parsed = [];

        foreach ($config as $item) {
            $key = $item['key'] ?? null;
            $value = $item['value'] ?? null;

            if ($key !== null) {
                $parsed[$key] = $value;
            }
        }

        return $parsed;
    }
}
