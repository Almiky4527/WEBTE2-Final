<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function validate_token($token): bool
    {
        $configuredToken = config('octave.api_token');
        
        return $token === $configuredToken;
    }
}
