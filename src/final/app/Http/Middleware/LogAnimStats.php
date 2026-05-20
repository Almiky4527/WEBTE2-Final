<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AnimUsageStat;
use App\Enums\AnimType;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LogAnimStats
{
    /**
     * Test if the incoming request has UUID in cookies and
     * if it was logged within a specified time.
     */
    private function is_new(Request $request): bool
    {
        $anim_name = last( $request->segments() );
        $timeframe = config('octave.stats_timeframe_m');
        $uuid = $request->cookie('anim_' . $anim_name . '_uuid');

        if ( !$uuid ) return true;

        $anim_stat = AnimUsageStat::where('token', $uuid)
            ->where( 'created_at', '>=', Carbon::now()->subMinutes(10) )
            ->where('anim', $anim_name)
            ->first();

        if ( !$anim_stat ) return true;

        return false;
    }

    private function log_anim_run(Request $request): string
    {
        $ip = $request->ip();
        $location = geoip($ip);
        $country = $location->iso_code;
        $city = $location->city;

        $uuid = (string) Str::uuid();
        $anim_name = last( $request->segments() );

        AnimUsageStat::create([
            'token' => $uuid,
            'anim' => $anim_name,
            'country' => $country,
            'city' => $city,
        ]);

        return $uuid;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ( $this->is_new($request) ) {
            $anim_name = last( $request->segments() );
            $uuid = $this->log_anim_run($request);

            $response->headers->setCookie(
                cookie(
                    'anim_' . $anim_name . '_uuid',
                    $uuid,
                    525600,
                    '/api/octave/' . $anim_name,
                    null,
                    false,
                    true,
                    false,
                    'Lax'
                )
            );
        }

        return $response;
    }
}
