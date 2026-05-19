<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OctaveLog;

class LogExportController extends Controller
{
    /**
     * Export logs of octave REST API usage to CSV
     */
    public function export_logs(Request $request)
    {
        $fields = $request->validate([
            'token' => 'required|string|max:256',
        ]);

        $token = $fields['token'];

        if ( !$this->validate_token($token) )
            // Token validation failed
            return response()->json([
                'code' => 401,
                'success' => false,
                'error' => 'Invalid authentication token'
            ], 401);

        $fileName = 'logs.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // CSV header row
            fputcsv($file, [
                'ID',
                'Code',
                'Success',
                'Error',
                'Created'
            ]);

            // Data rows
            OctaveLog::chunk(1000, function ($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->code,
                        $log->success,
                        $log->error,
                        $log->created_at,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
