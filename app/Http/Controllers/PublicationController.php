<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublicationController extends Controller
{
    public function getPublication(Request $request)
    {
        $doi = $request->query('doi');
        if (!$doi) {
            return response()->json(['error' => 'DOI is required'], 400);
        }

        // Check local cache for full or partial DOI match
        $publications = Publication::where('doi', 'like', "{$doi}%")->get();
        if ($publications->isNotEmpty()) {

            $data = $publications->pluck('data');
            return response()->json(['source' => 'cache', 'data' => $data], 200);
        }

        // Query CrossRef
        $response = Http::get("https://api.crossref.org/works/{$doi}");
        if ($response->successful()) {
            $publicationData = $response->json();

            // Store in cache
            Publication::create([
                'doi' => $doi,
                'data' => $publicationData
            ]);

            return response()->json(['source' => 'crossref', 'data' => $publicationData], 200);
        } elseif ($response->status() == 404) {
            return response()->json(['error' => 'Publication not found'], 404);
        } else {
            Log::error('Failed to retrieve publication from CrossRef', [
                'doi' => $doi,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json(['error' => 'Failed to retrieve publication'], 500);
        }
    }
}
