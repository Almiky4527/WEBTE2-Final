<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnimUsageStat;
use App\Enums\AnimType;

class StatsController extends Controller
{
    /**
     * Return aggregated usage counts per animation type.
     */
    public function summary()
    {
        $counts = AnimUsageStat::query()
            ->selectRaw('anim, COUNT(*) as total')
            ->groupBy('anim')
            ->pluck('total', 'anim');

        $items = [];
        foreach (AnimType::cases() as $case) {
            $items[] = [
                'anim'  => $case->value,
                'count' => (int) ($counts[$case->value] ?? 0),
            ];
        }

        return response()->json(['items' => $items]);
    }

    /**
     * Return per-record detail for the given animation type.
     */
    public function detail(string $anim)
    {
        $valid = array_column(AnimType::cases(), 'value');
        if (!in_array($anim, $valid, true)) {
            return response()->json([
                'success' => false,
                'error'   => 'Unknown animation type',
            ], 404);
        }

        $rows = AnimUsageStat::query()
            ->where('anim', $anim)
            ->orderByDesc('created_at')
            ->limit(500)
            ->get(['created_at', 'city', 'country'])
            ->map(function ($row) {
                return [
                    'created_at' => optional($row->created_at)->toIso8601String(),
                    'city'       => $row->city,
                    'country'    => $row->country,
                ];
            });

        return response()->json([
            'anim' => $anim,
            'rows' => $rows,
        ]);
    }
}
