<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;


class OctaveController extends Controller
{
    protected function validate_token($token): bool
    {
        $expected = config('octave.api_token');
        if (!is_string($expected) || $expected === '') {
            return false;
        }
        return is_string($token) && hash_equals($expected, $token);
    }

    /**
     * Validate user-supplied unlock password and flip the session flag.
     * The real api_token never leaves the server.
     */
    public function unlock(Request $request)
    {
        $fields = $request->validate([
            'password' => 'required|string|max:128',
        ]);

        $expected = config('octave.unlock_password');
        if (!is_string($expected) || $expected === '' || !hash_equals($expected, $fields['password'])) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid password',
            ], 401);
        }

        $request->session()->put('octave_unlocked', true);

        return response()->json(['success' => true]);
    }

    /**
     * Clear the unlock flag so the next eval/anim call requires the password again.
     */
    public function lock(Request $request)
    {
        $request->session()->forget('octave_unlocked');

        return response()->json(['success' => true]);
    }

    /**
     * Return the current unlock state so the UI can sync after a fresh load.
     */
    public function status(Request $request)
    {
        return response()->json([
            'unlocked' => (bool) $request->session()->get('octave_unlocked', false),
        ]);
    }

    private function validate_code($code): bool
    {
        // Optional: basic security filtering
        $blocked = [
            'system',
            'unix',
            'popen',
            'fork',
            'fopen',
            'delete',
            'rmdir',
            'mkdir',
            'rename',
        ];

        foreach ($blocked as $word)
            if ( stripos($code, $word) !== false )
                return false;

        return true;
    }

    private function remove_octave_shutdown_error($error): string
    {
        // Filter out lines that are noise from the headless gnuplot pipeline:
        //  - "ignoring const execution_exception&" on shutdown
        //  - "Fontconfig error: No writable cache directories" (www-data has no $HOME)
        //  - "iconv failed to convert degree sign" (gnuplot locale)
        $lines = preg_split('/\R/', $error);
        $kept = array_filter($lines, function ($line) {
            if (str_contains($line, 'ignoring const execution_exception')) return false;
            if (str_contains($line, 'Fontconfig error')) return false;
            if (str_contains($line, 'iconv failed to convert')) return false;
            if (str_contains($line, 'multiplot>')) return false;
            if (str_contains($line, 'warning: deprecated command')) return false;
            if (preg_match('/^\s*\^\s*$/', $line)) return false;
            return true;
        });
        return implode("\n", $kept);
    }

    /**
     * Evaluate octave statements and send back output
     */
    public function evaluate(Request $request)
    {
        if (!$request->session()->get('octave_unlocked')) {
            return response()->json([
                'code' => 401,
                'success' => false,
                'error' => 'Console is locked',
            ], 401);
        }

        $fields = $request->validate([
            'code'      => 'required|string|max:5000',
            'workspace' => 'nullable|string|max:1048576',
        ]);

        $workspace = $fields['workspace'] ?? null;

        if ( !$this->validate_code($fields['code']) )
            return response()->json([
                'code' => 403,
                'success' => false,
                'error' => 'Forbidden command detected'
            ], 403);

        if ($workspace !== null && !str_starts_with(ltrim($workspace), '# Created by Octave')) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'error' => 'Invalid workspace format',
            ], 400);
        }

        $wsIn   = $workspace !== null ? tempnam(sys_get_temp_dir(), 'ws_in_')  : null;
        $wsOut  = tempnam(sys_get_temp_dir(), 'ws_out_');
        $figOut = tempnam(sys_get_temp_dir(), 'fig_') . '.png';

        if ($wsIn !== null) {
            // Laravel TrimStrings middleware strips trailing newlines from input.
            // Octave 'load -text' needs trailing newline to terminate the last value.
            file_put_contents($wsIn, $workspace . "\n");
        }

        $prelude  = "pkg load control;";
        $prelude .= " warning('off', 'all');";
        $prelude .= " graphics_toolkit('gnuplot');";
        $prelude .= " set(0, 'defaultfigurevisible', 'off');";
        $prelude .= $wsIn !== null ? " load('-text', '{$wsIn}');" : "";
        $postlude  = " if !isempty(findall(0,'type','figure'));";
        $postlude .= " print('{$figOut}', '-dpng', '-S800,500'); close all;";
        $postlude .= " end;";
        $postlude .= " save('-text', '{$wsOut}', '*'); exit(0);";

        $fullCode = $prelude . " " . $fields['code'] . "; " . $postlude;

        $process = new Process([
            'octave',
            '--quiet',
            '--no-gui',
            '--eval',
            $fullCode,
        ]);
        $process->setTimeout(30);

        usleep((int) config('octave.sleep_us', 0));

        try {
            $process->run();

            $output    = trim($process->getOutput());
            $stderr    = $this->remove_octave_shutdown_error($process->getErrorOutput());
            $wsContent = file_exists($wsOut) ? file_get_contents($wsOut) : '';
            $figBase64 = (file_exists($figOut) && filesize($figOut) > 0)
                ? base64_encode(file_get_contents($figOut))
                : '';

            return response()->json([
                'code'      => 200,
                'success'   => true,
                'output'    => $output,
                'stderr'    => trim($stderr),
                'workspace' => $wsContent,
                'figure'    => $figBase64,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);

        } finally {
            if ($wsIn !== null && file_exists($wsIn))  { @unlink($wsIn); }
            if (file_exists($wsOut))                    { @unlink($wsOut); }
            if (file_exists($figOut))                   { @unlink($figOut); }
        }
    }
}