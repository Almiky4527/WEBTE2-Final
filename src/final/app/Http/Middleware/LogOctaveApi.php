<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OctaveLog;

class LogOctaveApi
{
    private function decode_response_body(Response $response): array
    {
        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return [];
        }
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
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

        $data = $this->decode_response_body($response);
        $success = (bool) ($data['success'] ?? false);

        $errorMessage = '';
        if (!$success) {
            $errorMessage = (string) ($data['error'] ?? ('HTTP ' . $response->getStatusCode()));
        } elseif (!empty($data['stderr'])) {
            $errorMessage = (string) $data['stderr'];
        }

        OctaveLog::create([
            'code'    => mb_substr($code, 0, 5000),
            'success' => $success,
            'error'   => $errorMessage !== '' ? mb_substr($errorMessage, 0, 250) : null,
        ]);

        return $response;
    }
}
