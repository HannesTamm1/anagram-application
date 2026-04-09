<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnagramService;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

class AnagramController extends Controller
{
    public function __invoke(Request $request, AnagramService $anagramService): JsonResponse
    {
        $validated = $request->validate([
            'word' => ['required', 'string'],
        ]);

        $anagrams = $anagramService->findAnagrams($validated['word']);

        return response()->json([
            'word' => $validated['word'],
            'anagrams' => $anagrams->values(),
            'count' => $anagrams->count(),
        ]);
    }
}
