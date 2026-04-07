<?php

namespace App\Helpers;

class Flash
{
    /**
     * Store a structured flash message in the session.
     *
     * @param string $message
     * @param string $type (success, error, warning, info)
     * @return void
     */
    public static function make(string $message, string $type = 'success'): void
    {
        session()->flash('flash_message', [
            'id' => uniqid('flash_'),
            'message' => $message,
            'type' => $type,
            'timestamp' => now()->timestamp,
        ]);
        
        // Also set legacy keys for potential fallback
        session()->flash($type, $message);
    }
}
