<?php

namespace App\Traits;

trait AlertModal
{
    /**
     * Lanza un modal de éxito.
     */
    public function success(string $message, string $title = 'Éxito', array $options = []): void
    {
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $title,
            'text' => $message,
        ]);
    }

    /**
     * Lanza un modal de error.
     */
    public function error(string $message, string $title = 'Error', array $options = []): void
    {
        $this->dispatch('swal', [
            'icon' => 'error',
            'title' => $title,
            'text' => $message,
        ]);
    }

    /**
     * Lanza un modal de advertencia.
     */
    public function warning(string $message, string $title = 'Atención', array $options = []): void
    {
        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => $title,
            'text' => $message,
        ]);
    }

    /**
     * Lanza un modal de información.
     */
    public function info(string $message, string $title = 'Información', array $options = []): void
    {
        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => $title,
            'text' => $message,
        ]);
    }
}
