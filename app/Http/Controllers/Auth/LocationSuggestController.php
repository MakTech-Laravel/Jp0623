<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PhotonLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class LocationSuggestController extends Controller
{
    public function __construct(private PhotonLocationService $photon) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'q' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Enter at least 3 characters to search.',
                'errors' => $validator->errors(),
                'suggestions' => [],
            ], 422);
        }

        $query = trim((string) $validator->validated()['q']);

        try {
            $suggestions = $this->photon->suggest($query);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'suggestions' => [],
            ], 503);
        }

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }
}
