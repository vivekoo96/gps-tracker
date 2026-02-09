<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Landmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandmarkV2Controller extends BaseV2Controller
{
    /**
     * Display a listing of landmarks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Landmark::query();
        
        return $this->paginate($request, $query);
    }

    /**
     * Display the specified landmark.
     */
    public function show(string $id): JsonResponse
    {
        $landmark = Landmark::find($id);

        if (!$landmark) {
            return $this->error('Landmark not found', 'NOT_FOUND', 404);
        }

        return $this->success($landmark);
    }
}
