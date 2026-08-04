<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .nps-score-options {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 4px;
            margin: 6px auto;
        }

        .nps-score-label {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 24px;
            height: 24px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border-width: 2px;
            border-style: solid;
            border-color: transparent;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .nps-score-label:has(input:checked) {
            border-color: currentColor;
        }

        @media (min-width: 480px) {
            .nps-score-label {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
        }

        @media (min-width: 768px) {
            .nps-score-label {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900 antialiased">
    <div class="mx-auto max-w-xl px-4 py-16">
         <img src="{{ asset('images/talentum_voice.png') }}" alt="{{ config('app.name') }}" class="mx-auto mb-12 block h-12 mb-4">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-2 bg-indigo-600"></div>
            <div class="p-8 text-center sm:p-10">
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>

                @if ($response->score === null)
                    <h1 class="mt-5 text-xl font-semibold text-slate-900">¡Gracias por dedicar unos segundos a compartir
                        tu opinión!</h1>
                    <p class="mt-2 text-slate-500 mt-5">{{ $response->npsSurvey->question }}</p>
                    <form method="POST" action="{{ route('nps.comment', $response->token) }}" class="mt-6 text-center">
                        @php $preselectedScore = old('score', request()->query('score')); @endphp
                        <div class="nps-score-options">
                            @foreach (range(0, 10) as $value)
                                <label class="nps-score-label"
                                    style="background-color:{{ $value <= 6 ? '#fee2e2' : ($value <= 8 ? '#fef3c7' : '#d1fae5') }}; color:{{ $value <= 6 ? '#b91c1c' : ($value <= 8 ? '#92400e' : '#065f46') }};">
                                    <input type="radio" name="score"
                                        value="{{ $value }}"
                                        {{ $preselectedScore !== null && $preselectedScore == $value ? 'checked' : '' }}
                                        style="display:none;">
                                    {{ $value }}
                                </label>
                            @endforeach
                        </div>
                        @error('score')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @csrf
                        <p class="mt-2 text-slate-500 mt-8"><b>Escuchar también significa comprender.</b></p>
                        <p class="mt-2 text-slate-500">Cuéntanos brevemente el motivo de tu valoración. Cada comentario
                            nos ayuda a entender mejor tu experiencia y a convertirla en una oportunidad de mejora.</p>
                       
                        <label class="block text-sm font-medium text-slate-700 mt-2">Comentario <b><span id="comment-requirement-hint"></span></b>

                        </label>
                        <textarea name="comment" rows="4"
                            class="mt-2 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"></textarea>
                        @error('comment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-4 flex justify-end">
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Enviar</button>
                        </div>
                    </form>
                    @if ($response->score > 6)
                        <form method="POST" action="{{ route('nps.comment', $response->token) }}" class="text-right">
                            @csrf
                            <input type="hidden" name="score" value="{{ $response->score }}">
                            <input type="hidden" name="comment" value="">
                            <button type="submit" class="text-sm text-slate-400 hover:text-slate-600">Omitir</button>
                        </form>
                    @endif
                @else
                    <h1 class="mt-5 text-xl font-semibold text-slate-900">Gracias por compartir tu experiencia.</h1>
                    <p class="mt-2 text-slate-500">Tu opinión ya forma parte de <b>Talentum Voice</b>.
                         <p class="mt-2 text-slate-500">Cada respuesta nos ayuda a comprender mejor la experiencia de quienes trabajan con nosotros y a
                        convertir ese conocimiento en acciones de mejora.</p>
                        <p class="mt-2 text-slate-500"><b>Gracias por ayudarnos a seguir evolucionando.</b></p>

                        <p class="mt-2 text-slate-500"><b>Talentum Voice</b></p>
                        <p class="mt-2 text-slate-500">Escuchamos para mejorar.</p>
                    
                @endif
            </div>
        </div>
    </div>
    <script>
        
        const hint = document.getElementById('comment-requirement-hint');

        function updateCommentHint(radio) {
            hint.textContent = Number(radio.value) <= 6 ?
                '(obligatorio).' : '';
        }

        document.querySelectorAll('input[name="score"]').forEach((radio) => {
            radio.addEventListener('change', () => updateCommentHint(radio));
        });

        const preselectedScoreRadio = document.querySelector('input[name="score"]:checked');
        if (preselectedScoreRadio) {
            updateCommentHint(preselectedScoreRadio);
        }
    </script>
</body>

</html>
