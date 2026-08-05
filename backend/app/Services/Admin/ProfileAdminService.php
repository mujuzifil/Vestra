<?php

namespace App\Services\Admin;

use App\Events\Notification\ProfileUpdated;
use App\Models\AdminSession;
use App\Models\AuditLog;
use App\Models\LoginActivity;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileAdminService
{
    /**
     * @return array<string, mixed>
     */
    public function getProfile(User $user): array
    {
        $user->load('roles');
        $role = $user->roles->first();

        $fields = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
            'avatar_url' => $user->avatarUrl(),
            'initials' => $user->initials(),
            'status' => $user->status ?: 'active',
            'status_label' => ($user->status ?: 'active') === 'active' ? 'Active' : 'Inactive',
            'role' => $role?->name,
            'created_at' => $user->created_at,
            'last_login_at' => $user->last_login_at,
            'password_changed_at' => $user->password_changed_at,
        ];

        // Staff-managed fields: only expose when present in the database.
        foreach (['department', 'job_title', 'employee_id'] as $optional) {
            if (filled($user->{$optional})) {
                $fields[$optional] = $user->{$optional};
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null, bool $removeAvatar = false): User
    {
        return DB::transaction(function () use ($user, $data, $avatar, $removeAvatar) {
            $old = [
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'phone' => $user->phone,
            ];

            $payload = [
                'name' => trim((string) ($data['name'] ?? $user->name)),
                'email' => trim((string) ($data['email'] ?? $user->email)),
                'username' => filled($data['username'] ?? null) ? trim((string) $data['username']) : null,
                'phone' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            ];

            $parts = preg_split('/\s+/', $payload['name'], 2) ?: [];
            $payload['first_name'] = $parts[0] ?? $user->first_name;
            $payload['last_name'] = $parts[1] ?? $user->last_name;

            $user->fill($payload);

            if ($removeAvatar && $user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
                $user->avatar_path = null;
            }

            $user->save();

            if ($avatar) {
                if ($user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                }
                $path = $avatar->store('avatars/staff', 'public');
                $user->update(['avatar_path' => $path]);
            }

            event(new ProfileUpdated($user));

            AuditService::log($user, 'profile_updated', $user, [
                'old' => $old,
                'new' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone' => $user->phone,
                ],
                'source' => 'admin_profile',
            ]);

            return $user->fresh(['roles']);
        });
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Choose a new password that is different from your current password.',
            ]);
        }

        $user->password = $newPassword;
        $user->force_password_change_at = null;
        $user->password_changed_at = now();
        $user->save();

        $user->tokens()->delete();

        AuditService::log($user, 'password_changed', $user, [
            'source' => 'admin_profile',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listSessions(User $user): array
    {
        return AdminSession::query()
            ->forUser($user->id)
            ->orderByDesc('last_activity_at')
            ->limit(25)
            ->get()
            ->map(fn (AdminSession $session) => [
                'id' => $session->id,
                'device' => $session->device ?: 'Unknown device',
                'os' => $session->os,
                'browser' => $session->browser,
                'ip_address' => $session->ip_address,
                'last_activity_at' => $session->last_activity_at,
                'created_at' => $session->created_at,
                'is_current' => $session->isCurrent(),
                'is_active' => $session->last_activity_at && $session->last_activity_at->gte(now()->subHours(24)),
            ])
            ->all();
    }

    public function terminateSession(User $user, int $sessionId): void
    {
        $session = AdminSession::query()->forUser($user->id)->findOrFail($sessionId);

        if ($session->isCurrent()) {
            throw ValidationException::withMessages([
                'session' => 'You cannot terminate your current session from this list. Use Sign Out instead.',
            ]);
        }

        $session->delete();

        AuditService::log($user, 'session.terminated', $session, [
            'session_id' => $session->session_id,
            'source' => 'admin_profile',
        ]);
    }

    public function terminateOtherSessions(User $user): int
    {
        $currentId = session()->getId();
        $query = AdminSession::query()
            ->forUser($user->id)
            ->where('session_id', '!=', $currentId);

        $count = $query->count();
        $query->delete();

        AuditService::log($user, 'session.terminated_others', $user, [
            'count' => $count,
            'source' => 'admin_profile',
        ]);

        return $count;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activityTimeline(User $user, int $limit = 40): array
    {
        $audits = AuditLog::query()
            ->where('user_id', $user->id)
            ->whereIn('action', [
                'profile_updated',
                'password_changed',
                'password_change.required',
                'session.terminated',
                'session.terminated_others',
                'login',
                'logout',
                'staff.updated',
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (AuditLog $log) => [
                'type' => 'audit',
                'action' => $log->action,
                'label' => Str::headline(str_replace(['.', '_'], ' ', $log->action)),
                'ip' => $log->ip_address,
                'device' => Str::limit((string) $log->user_agent, 80),
                'timestamp' => $log->created_at,
            ]);

        $logins = LoginActivity::query()
            ->forUser($user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (LoginActivity $activity) => [
                'type' => 'login',
                'action' => $activity->successful ? 'login.success' : 'login.failed',
                'label' => $activity->successful ? 'Successful login' : 'Failed login',
                'ip' => $activity->ip_address,
                'device' => trim(($activity->browser ?? '').' '.($activity->device ?? '')),
                'timestamp' => $activity->created_at,
            ]);

        return $audits->concat($logins)
            ->sortByDesc(fn ($row) => $row['timestamp']?->timestamp ?? 0)
            ->take($limit)
            ->values()
            ->all();
    }
}
