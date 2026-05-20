<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function get_token(): string
    {
        return config('octave.api_token');
    }

    protected function validate_token($token): bool
    {
        $configuredToken = config('octave.api_token');
        
        return $token === $configuredToken;
    }
}
