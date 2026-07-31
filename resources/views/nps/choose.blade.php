<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900 antialiased">
    <div class="mx-auto max-w-lg px-4 py-16">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-2 bg-indigo-600"></div>
            <div class="p-8 sm:p-10">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Talentum Voice</p>
                <h1 class="mt-4 text-lg font-bold text-slate-900">{{ $response->npsSurvey->question }}</h1>

                <div class="mt-6 flex gap-1 overflow-x-auto">
                    @foreach (range(0, 10) as $score)
                        <a
                            href="{{ route('nps.respond', ['token' => $response->token, 'score' => $score]) }}"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-semibold transition {{ $score <= 6 ? 'bg-red-100 text-red-600 hover:bg-red-500 hover:text-white' : ($score <= 8 ? 'bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white') }}"
                        >
                            {{ $score }}
                        </a>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                    <span>Nada probable</span>
                    <span>Muy probable</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
