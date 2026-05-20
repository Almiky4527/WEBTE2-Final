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


    /*
    |--------------------------------------------------------------------------
    | Animation statistics logging timeframe
    |--------------------------------------------------------------------------
    |
    | Time in minutes to ignore logging of the same incoming animation request
    | as a new request.
    |
    */

    'stats_timeframe_m' => env('OCTAVE_STATS_TIMEFRAME_M', 10),

];