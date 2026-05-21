<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OctaveLog;

class LogExportController extends Controller
{
    private function ensureUnlocked(Request $request): bool
    {
        return (bool) $request->session()->get('octave_unlocked', false);
    }

    /**
     * Return recent logs of octave REST API usage as JSON.
     */
    public function index(Request $request)
    {
        if (!$this->ensureUnlocked($request)) {
            return response()->json([
                'success' => false,
                'error'   => 'Console is locked',
            ], 401);
        }

        $rows = OctaveLog::query()
            ->orderByDesc('id')
            ->limit(500)
            ->get(['id', 'code', 'success', 'error', 'created_at'])
            ->map(function ($log) {
                return [
                    'id'         => $log->id,
                    'code'       => $log->code,
                    'success'    => (bool) $log->success,
                    'error'      => $log->error,
                    'created_at' => optional($log->created_at)->toIso8601String(),
                ];
            });

        return response()->json([
            'rows' => $rows,
        ]);
    }

    /**
     * Export logs of octave REST API usage to CSV
     */
    public function export_logs(Request $request)
    {
        if (!$this->ensureUnlocked($request)) {
            return response()->json([
                'success' => false,
                'error'   => 'Console is locked',
            ], 401);
        }

        $fileName = 'logs.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Code',
                'Success',
                'Error',
                'Created',
            ]);

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
