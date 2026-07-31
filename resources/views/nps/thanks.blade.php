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
            <div class="p-8 text-center sm:p-10">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>

                @if ($response->comment === null)
                    <h1 class="mt-5 text-xl font-semibold text-slate-900">¡Gracias por tu respuesta!</h1>
                    <p class="mt-2 text-slate-500">Registramos tu puntaje: <span class="font-semibold text-slate-700">{{ $response->score }}</span>.</p>

                    <form method="POST" action="{{ route('nps.comment', $response->token) }}" class="mt-6 text-left">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700">¿Querés contarnos algo más? (opcional)</label>
                        <textarea name="comment" rows="4" class="mt-2 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"></textarea>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Enviar</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('nps.comment', $response->token) }}" class="text-right">
                        @csrf
                        <input type="hidden" name="comment" value="">
                        <button type="submit" class="text-sm text-slate-400 hover:text-slate-600">Omitir</button>
                    </form>
                @else
                    <h1 class="mt-5 text-xl font-semibold text-slate-900">¡Listo, gracias!</h1>
                    <p class="mt-2 text-slate-500">Tu respuesta fue registrada correctamente.</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
