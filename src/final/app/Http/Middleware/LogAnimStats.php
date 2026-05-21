<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AnimUsageStat;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LogAnimStats
{
    private const COOKIE_NAME = 'anim_uid';

    private function anim_name(Request $request): string
    {
        return last($request->segments());
    }

    private function should_log(string $uuid, string $anim): bool
    {
        $minutes = (int) config('octave.stats_timeframe_m', 10);

        return !AnimUsageStat::where('token', $uuid)
            ->where('anim', $anim)
            ->where('created_at', '>=', Carbon::now()->subMinutes($minutes))
            ->exists();
    }

    private function record(string $uuid, string $anim, Request $request): void
    {
        $location = geoip($request->ip());

        AnimUsageStat::create([
            'token'   => $uuid,
            'anim'    => $anim,
            'country' => $location->iso_code ?? '??',
            'city'    => $location->city ?? '',
        ]);
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return $response;
        }

        $uuid = $request->cookie(self::COOKIE_NAME);
        $issuedNew = false;
        if (!$uuid) {
            $uuid = (string) Str::uuid();
            $issuedNew = true;
        }

        $anim = $this->anim_name($request);

        if ($this->should_log($uuid, $anim)) {
            $this->record($uuid, $anim, $request);
        }

        if ($issuedNew) {
            $response->headers->setCookie(cookie(
                self::COOKIE_NAME,
                $uuid,
                525600,
                '/',
                null,
                false,
                true,
                false,
                'Lax'
            ));
        }

        return $response;
    }
}
