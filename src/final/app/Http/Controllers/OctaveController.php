<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OctaveController extends Controller
{
    private function validate_token($token): bool
    {
        $configuredToken = config('octave.api_token');
        
        return $token === $configuredToken;
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
        return str_replace(
            'error: ignoring const execution_exception& while preparing to exit',
            '',
            $error
        );
    }

    /**
     * Evaluate octave statements and send back output
     */
    public function evaluate(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:256',
            'code' => 'required|string|max:5000',
        ]);

        $token = $request->input('token');
        $code = $request->input('code') . "\nexit(0);\n";

        if ( !$this->validate_token($token) )
            return response()->json([
                'code' => 401,
                'success' => false,
                'error' => 'Invalid authentication token'
            ], 401);

        if ( !$this->validate_code($code) )
            return response()->json([
                'code' => 403,
                'success' => false,
                'error' => 'Forbidden command detected'
            ], 403);

        // Create temporary script
        // $tempFile = tempnam(sys_get_temp_dir(), 'oct_') . '.m';
        // file_put_contents($tempFile, $code);

        $process = new Process([
            'octave',
            '--quiet',
            '--no-gui',
            '--eval',
            $code,
            // $tempFile
        ]);

        $process->setTimeout(10);

        try {
            $process->run();

            $output = $process->getOutput();
            $error = $process->getErrorOutput();
            $error = $this->remove_octave_shutdown_error($error);

            // unlink($tempFile);

            return response()->json([
                'code' => 200,
                'success' => true,
                'output' => trim($output),
                'stderr' => trim($error),
            ]);

        } catch (\Exception $e) {

            // if (file_exists($tempFile)) {
            //     unlink($tempFile);
            // }

            return response()->json([
                'code' => 500,
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}