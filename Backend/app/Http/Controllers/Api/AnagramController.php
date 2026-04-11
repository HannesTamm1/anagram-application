<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnagramLookupRequest;
use App\Services\AnagramService;
use Illuminate\Http\JsonResponse;

class AnagramController extends Controller
{
    public function __invoke(AnagramLookupRequest $request, AnagramService $anagramService): JsonResponse
    {
        $validated = $request->validated();

        $anagrams = $anagramService->findAnagrams($validated['word']);

        return response()->json([
            'word' => $validated['word'],
            'anagrams' => $anagrams->values(),
            'count' => $anagrams->count(),
        ]);
    }
}
