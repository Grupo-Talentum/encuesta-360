<?php

namespace App\Http\Controllers;

use App\Models\NpsResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NpsResponseController extends Controller
{
    public function score(Request $request, string $token): View
    {
        $response = NpsResponse::where('token', $token)->firstOrFail();

        if (! $response->answered_at) {
            $score = (int) $request->query('score');
            abort_unless($request->query('score') !== null && $score >= 0 && $score <= 10, 404);

            $response->update(['score' => $score, 'answered_at' => now()]);
        }

        return view('nps.thanks', ['response' => $response]);
    }

    public function storeComment(Request $request, string $token): View
    {
        $response = NpsResponse::where('token', $token)->firstOrFail();

        abort_unless($response->answered_at, 404);

        $validated = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);

        $response->update(['comment' => $validated['comment'] ?? '']);

        return view('nps.thanks', ['response' => $response]);
    }
}
