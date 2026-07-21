<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Enums\SettingGroup;
use App\Models\Setting;
use App\Services\SettingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class EditEmailSettings extends EditGroupSettings
{
    public function getGroup(): SettingGroup
    {
        return SettingGroup::EMAIL;
    }

    protected function getFormActions(): array
    {
        return array_merge(parent::getFormActions(), [
            Action::make('sendTestEmail')
                ->label('Send test email')
                ->icon('heroicon-m-paper-airplane')
                ->color('gray')
                ->outlined()
                ->requiresConfirmation()
                ->modalHeading('Send test email')
                ->modalDescription('This will send a test message to the support email address using the configured SMTP settings.')
                ->action('sendTestEmail'),
        ]);
    }

    public function sendTestEmail(): void
    {
        $state = $this->form->getState();

        $settings = $this->settings ?? app(SettingService::class)->group($this->getGroup());

        $resolve = fn (string $key, mixed $stateValue): mixed => $stateValue === Setting::ENCRYPTED_PLACEHOLDER
            ? $settings->firstWhere('key', $key)?->value
            : $stateValue;

        $host = $state['smtp_host'] ?? '';
        $port = (int) ($state['smtp_port'] ?? 587);
        $encryption = $state['smtp_encryption'] ?? 'tls';
        $username = (string) $resolve('smtp_username', $state['smtp_username'] ?? '');
        $password = (string) $resolve('smtp_password', $state['smtp_password'] ?? '');
        $fromAddress = (string) $resolve('sender_email', $state['sender_email'] ?? 'noreply@vestra.com');
        $fromName = $state['sender_name'] ?? 'VESTRA';
        $recipient = $state['support_email'] ?? $fromAddress;

        if (blank($host)) {
            Notification::make()
                ->title('SMTP host required')
                ->body('Please enter an SMTP host before sending a test email.')
                ->warning()
                ->send();

            return;
        }

        try {
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);

            Mail::raw('This is a test email from the VESTRA Administration Platform.', function ($message) use ($recipient, $fromAddress, $fromName): void {
                $message->to($recipient)
                    ->from($fromAddress, $fromName)
                    ->subject('VESTRA Test Email');
            });

            Notification::make()
                ->title('Test email sent')
                ->body("A test email was sent to {$recipient}.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Test email failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
