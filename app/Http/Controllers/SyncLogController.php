<?php

namespace App\Http\Controllers;

use App\Models\SyncLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = SyncLog::with('entries')
            ->orderByDesc('started_at')
            ->paginate($request->input('per_page', 15));

        return response()->json($logs);
    }

    public function show(SyncLog $syncLog): JsonResponse
    {
        $syncLog->load('entries');

        return response()->json($syncLog);
    }
}
