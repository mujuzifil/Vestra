<?php

namespace App\Services;

use App\Enums\ContactStatus;
use App\Events\Notification\ContactMessageSubmitted;
use App\Mail\ContactAdminNotificationMail;
use App\Mail\ContactReceivedMail;
use App\Models\ContactMessage;
use App\Repositories\ContactRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function __construct(private readonly ContactRepository $repository) {}

    public function submit(array $data): ContactMessage
    {
        $attachments = $data['attachments'] ?? null;
        unset($data['attachments']);

        $message = $this->repository->create([
            ...$data,
            'status' => ContactStatus::NEW->value,
            'source' => $data['source'] ?? 'website',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if (is_array($attachments) && count($attachments) > 0) {
            $stored = [];
            /** @var UploadedFile $file */
            foreach ($attachments as $file) {
                $stored[] = $file->store("contact_attachments/{$message->id}", 'public');
            }
            $message->attachments = $stored;
            $message->save();
        }

        $message->refresh();

        Mail::to($message->email)->send(new ContactReceivedMail($message));

        $adminAddress = config('mail.admin_address') ?? config('mail.from.address');
        if ($adminAddress) {
            Mail::to($adminAddress)->send(new ContactAdminNotificationMail($message));
        }

        event(new ContactMessageSubmitted($message));

        return $message;
    }
}
