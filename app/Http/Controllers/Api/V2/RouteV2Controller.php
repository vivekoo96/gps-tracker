<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteV2Controller extends BaseV2Controller
{
    /**
     * Display a listing of routes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Route::query();
        
        return $this->paginate($request, $query);
    }

    /**
     * Display the specified route.
     */
    public function show(string $id): JsonResponse
    {
        $route = Route::find($id);

        if (!$route) {
            return $this->error('Route not found', 'NOT_FOUND', 404);
        }

        return $this->success($route);
    }
}
