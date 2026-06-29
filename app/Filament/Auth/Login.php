<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function getHeading(): string | Htmlable
    {
        return 'Bienvenido de nuevo';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Ingresa tus credenciales para acceder al panel.';
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Correo electrónico')
            ->placeholder('tu@correo.com')
            ->prefixIcon('heroicon-o-envelope')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Contraseña')
            ->placeholder('••••••••')
            ->prefixIcon('heroicon-o-lock-closed')
            ->hint(
                filament()->hasPasswordReset()
                    ? new HtmlString(Blade::render(
                        '<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="-1">¿Olvidaste tu contraseña?</x-filament::link>'
                    ))
                    : null
            )
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required();
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Mantenerme conectado');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Ingresar al panel')
            ->submit('authenticate');
    }
}
