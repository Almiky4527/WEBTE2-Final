<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OctaveLog;

class LogOctaveApi
{
    private function get_response_content($response)
    {
        // Avoid breaking binary/streamed responses
        $content = $response->getContent();

        // Try to decode JSON if possible
        $decoded = json_decode($content, true);

        return $decoded ?? $content;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $fields = $request->validate([
            'code'      => 'required|string|max:5000',
            'workspace' => 'nullable|string|max:1048576',
        ]);
        $code = $fields['code'];
        
        $response = $next($request);

        if ( $response->getStatusCode() === 200 ) {
            $content = $this->get_response_content($response);

            OctaveLog::create([
                'code' => mb_substr($code, 0, 5000),
                'success' => $content['success'],
                'error' => mb_substr((string) ($content['stderr'] ?? ''), 0, 250),
            ]);
        }

        return $response;
    }
}
