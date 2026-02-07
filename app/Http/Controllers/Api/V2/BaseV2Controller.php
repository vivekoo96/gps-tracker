<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class BaseV2Controller extends Controller
{
    /**
     * Standard success response.
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Standard error response.
     */
    protected function error(string $message, string $errorCode = 'ERROR', int $code = 400, $details = null): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'details' => $details
            ]
        ], $code);
    }

    /**
     * Apply common query filters (pagination, sorting).
     */
    protected function applyQueryFilters(Request $request, Builder $query): Builder
    {
        // Sorting
        if ($request->has('sort')) {
            $sorts = explode(',', $request->get('sort'));
            foreach ($sorts as $sort) {
                $parts = explode(':', $sort);
                $field = $parts[0];
                $direction = (isset($parts[1]) && strtolower($parts[1]) === 'desc') ? 'desc' : 'asc';
                $query->orderBy($field, $direction);
            }
        }

        // Field selection
        if ($request->has('fields')) {
            $fields = explode(',', $request->get('fields'));
            $query->select($fields);
        }

        // Relationships
        if ($request->has('include')) {
            $includes = explode(',', $request->get('include'));
            $query->with($includes);
        }

        return $query;
    }

    /**
     * Paginated response helper.
     */
    protected function paginate(Request $request, Builder $query): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        $paginated = $this->applyQueryFilters($request, $query)->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ]
        ]);
    }
}
