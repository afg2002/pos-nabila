<?php

namespace App\Shared\Services;

class AlertService
{
    /**
     * Flash a success message
     */
    public static function success(string $message): void
    {
        session()->flash('alert', [
            'type' => 'success',
            'message' => $message,
            'timer' => 5000, // 5 seconds
            'dismissible' => true
        ]);
    }

    /**
     * Flash an error message
     */
    public static function error(string $message): void
    {
        session()->flash('alert', [
            'type' => 'error',
            'message' => $message,
            'timer' => 8000, // 8 seconds for errors
            'dismissible' => true
        ]);
    }

    /**
     * Flash an info message
     */
    public static function info(string $message): void
    {
        session()->flash('alert', [
            'type' => 'info',
            'message' => $message,
            'timer' => 4000, // 4 seconds
            'dismissible' => true
        ]);
    }

    /**
     * Flash a warning message
     */
    public static function warning(string $message): void
    {
        session()->flash('alert', [
            'type' => 'warning',
            'message' => $message,
            'timer' => 6000, // 6 seconds
            'dismissible' => true
        ]);
    }

    /**
     * Flash a message (backward compatibility)
     */
    public static function message(string $message): void
    {
        self::success($message);
    }
}