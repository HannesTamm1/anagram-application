<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WordIndexRequest;
use App\Models\Word;
use Illuminate\Http\JsonResponse;

class WordController extends Controller
{
    public function __invoke(WordIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = Word::query()->orderBy('word');

        if (! empty($validated['search'])) {
            $query->where('word', 'like', "%{$validated['search']}%");
        }

        $words = $query
            ->paginate($validated['per_page'] ?? 50)
            ->appends($request->query());

        return response()->json($words);
    }
}
