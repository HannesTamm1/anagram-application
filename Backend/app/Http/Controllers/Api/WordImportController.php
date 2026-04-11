<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WordImportRequest;
use App\Services\WordImportService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;

class WordImportController extends Controller
{
    public function __invoke(WordImportRequest $request, WordImportService $wordImportService): JsonResponse
    {
        $validated = $request->validated();

        try {
            $count = $wordImportService->importFromUrl($validated['url']);
        } catch (ConnectionException | \RuntimeException $exception) {
            return response()->json([
                'message' => 'Could not import the remote word list.',
            ], 502);
        }

        return response()->json([
            'message' => 'Wordbase imported successfully.',
            'inserted' => $count,
        ], 201);
    }
}
