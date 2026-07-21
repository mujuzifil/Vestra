<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function log(
        ?User $user,
        string $action,
        ?Model $subject = null,
        ?array $details = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'details' => $details,
            'ip_address' => $ip ?? request()?->ip(),
            'user_agent' => $userAgent ?? request()?->userAgent(),
        ]);
    }

    public static function logAuth(?User $user, string $action, ?string $ip = null, ?string $userAgent = null): AuditLog
    {
        return static::log($user, $action, null, null, $ip, $userAgent);
    }
}
