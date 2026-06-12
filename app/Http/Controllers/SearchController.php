<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SearchController extends Controller
{
    /**
     * Perform a live search via Jikan API V4.
     */
    public function searchLive(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        // Hit Jikan API V4
        $response = Http::get('https://api.jikan.moe/v4/characters', [
            'q' => $query,
            'limit' => 8,
            'order_by' => 'favorites',
            'sort' => 'desc'
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(
            ['error' => 'Failed to fetch data from Jikan API'], 
            $response->status()
        );
    }
}
