<?php

namespace App\Filament\Pages;

use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChange extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static string $view = 'filament.pages.force-password-change';

    protected static ?string $slug = 'force-password-change';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user->mustChangePassword()) {
            $this->redirectRoute('filament.admin.pages.dashboard');
            return;
        }

        $this->form->fill();

        AuditService::log(
            $user,
            'password_change.required',
            $user,
            ['source' => 'filament_force_password_change_page'],
            request()->ip(),
            request()->userAgent()
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('current_password')
                    ->label('Current password')
                    ->password()
                    ->required()
                    ->currentPassword(),

                TextInput::make('password')
                    ->label('New password')
                    ->password()
                    ->required()
                    ->rule(Password::min(12)
                        ->mixedCase()
                        ->numbers()
                        ->symbols())
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->label('Confirm new password')
                    ->password()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function getTitle(): string
    {
        return 'Change Password Required';
    }

    public function changePassword(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $user->clearPasswordChangeRequired();

        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $user->getAuthPassword(),
            ]);
        }

        AuditService::log(
            $user,
            'password_changed',
            $user,
            ['source' => 'forced_first_login']
        );

        Notification::make()
            ->title('Password changed successfully')
            ->success()
            ->send();

        $this->redirectRoute('filament.admin.pages.dashboard');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('changePassword')
                ->label('Change Password')
                ->submit('changePassword'),
        ];
    }
}
