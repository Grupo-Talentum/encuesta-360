<?php

namespace App\Http\Controllers;

use App\Models\NpsResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NpsResponseController extends Controller
{
    public function show(string $token): View
    {
        $response = NpsResponse::where('token', $token)->firstOrFail();

        if ($response->answered_at) {
            return view('nps.thanks', ['response' => $response]);
        }

        return view('nps.choose', ['response' => $response]);
    }

    public function score(Request $request, string $token): View
    {
        $response = NpsResponse::where('token', $token)->firstOrFail();

        if (! $response->answered_at) {
            $score = (int) $request->query('score');
            abort_unless($request->query('score') !== null && $score >= 0 && $score <= 10, 404);

            //$response->update(['score' => $score, 'answered_at' => now()]);
        }

        return view('nps.thanks', ['response' => $response]);
    }

    public function storeComment(Request $request, string $token): View
    {
        $response = NpsResponse::where('token', $token)->firstOrFail();

        if($response->answered_at != null) abort(404);

        $requiresComment = $request->filled('score_new') && $request->input('score_new') <= 6;

        $validated = $request->validate([
            'score_new' => ['required', 'integer', 'between:0,10'],
            'comment' => [$requiresComment ? 'required' : 'nullable', 'string', 'max:2000'],
        ], [
            'comment.required' => 'Tu comentario es imprescindible para continuar.',
            'score_new.required' => 'Tu puntuación es imprescindible para continuar.',
        ]);

        $response->update(['comment' => $validated['comment'] ?? '', 'score' => $validated['score_new'], 'answered_at' => now()]);

        return view('nps.thanks', ['response' => $response]);
    }
}
