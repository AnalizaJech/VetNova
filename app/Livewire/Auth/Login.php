<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Componente de login con rate limiting.
 * 5 intentos → bloqueo 15 minutos (por IP + email).
 */
#[Layout('components.layouts.guest')]
#[Title('Iniciar Sesión — VetNova')]
class Login extends Component
{
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required|min:6')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        $throttleKey = Str::transliterate(
            Str::lower($this->email) . '|' . request()->ip()
        );

        // Rate limiting: 5 intentos, bloqueo 15 minutos
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Demasiados intentos. Intenta en {$seconds} segundos.");
            return;
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 900); // 15 minutos
            $this->addError('email', 'Las credenciales no coinciden con nuestros registros.');
            return;
        }

        // Verificar que el usuario esté activo
        if (!Auth::user()->activo) {
            Auth::logout();
            $this->addError('email', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
