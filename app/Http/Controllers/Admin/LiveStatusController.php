<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminLiveStatusService;
use Illuminate\Http\JsonResponse;

class LiveStatusController extends Controller
{
    public function __invoke(AdminLiveStatusService $status): JsonResponse
    {
        return response()->json($status->snapshot());
    }
}
