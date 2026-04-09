<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WordImportService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WordImportController extends Controller
{
    public function __invoke(Request $request, WordImportService $wordImportService): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url:http,https'],
        ]);

        try {
            $count = $wordImportService->importFromUrl($validated['url']);
        } catch (ConnectionException | \RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        }

        return response()->json([
            'message' => 'Wordbase imported successfully.',
            'inserted' => $count,
        ], 201);
    }
}
