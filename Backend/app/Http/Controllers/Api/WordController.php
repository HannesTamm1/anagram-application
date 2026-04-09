<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WordController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Word::query()->orderBy('word');

        if (! empty($validated['search'])) {
            $search = mb_strtolower(trim($validated['search']), 'UTF-8');
            $query->where('word', 'like', "%{$search}%");
        }

        $words = $query
            ->paginate($validated['per_page'] ?? 50)
            ->appends($request->query());

        return response()->json($words);
    }
}
