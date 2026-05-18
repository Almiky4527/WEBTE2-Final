<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Octave REST API Token
    |--------------------------------------------------------------------------
    |
    | Used for validation of the incoming REST API requests.
    |
    */

    'api_token' => env('OCTAVE_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Octave Command Process Sleep Timeout
    |--------------------------------------------------------------------------
    |
    | Time in microseconds to delay octave command execution.
    |
    */
    
    'sleep_us' => env('OCTAVE_SLEEP_US'),

];