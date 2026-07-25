<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UploadAvatarRequest;
use App\Http\Resources\V1\CustomerResource;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AvatarController extends Controller
{
    use RespondsWithJson;

    private const AVATAR_DIRECTORY = 'avatars';

    public function store(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('avatar');

        $this->deleteExistingAvatar($user);

        $directory = public_path(self::AVATAR_DIRECTORY);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $user->update([
            'avatar_path' => self::AVATAR_DIRECTORY . '/' . $filename,
        ]);

        AuditService::log(
            $user,
            'avatar_uploaded',
            $user,
            ['source' => 'api'],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new CustomerResource($user->fresh()->load('preferences')),
            'Avatar uploaded successfully.'
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->deleteExistingAvatar($user);

        $user->update([
            'avatar_path' => null,
        ]);

        AuditService::log(
            $user,
            'avatar_removed',
            $user,
            ['source' => 'api'],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new CustomerResource($user->fresh()->load('preferences')),
            'Avatar removed successfully.'
        );
    }

    private function deleteExistingAvatar($user): void
    {
        if (empty($user->avatar_path)) {
            return;
        }

        $path = public_path($user->avatar_path);

        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
