<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .nps-score-label {
            border-color: transparent;
        }

        .nps-score-label:has(input:checked) {
            border-color: currentColor;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900 antialiased">
    <div class="mx-auto max-w-xl px-4 py-16">
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
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:6px auto;">
                            <tr>
                                @foreach (range(0, 10) as $value)
                                    <td style="padding:0 2px;">
                                        <table>
                                            <tr>
                                                <td width="28" height="28" align="center" valign="middle"
                                                    style="padding:2px 1px; width:28px; height:28px; border-radius:6px; background-color:{{ $value <= 6 ? '#fee2e2' : ($value <= 8 ? '#fef3c7' : '#d1fae5') }}; mso-padding-alt:0;">
                                                    <label class="nps-score-label"
                                                        style="display:block; width:28px; height:28px; line-height:24px; text-align:center; color:{{ $value <= 6 ? '#b91c1c' : ($value <= 8 ? '#92400e' : '#065f46') }}; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; border-width:2px; border-style:solid; border-radius:6px; box-sizing:border-box;">
                                                        <input type="radio" name="score_new"
                                                            value="{{ $value }}"
                                                            {{ old('score_new') == $value && old('score_new') !== null ? 'checked' : '' }}
                                                            style="display:none;">
                                                        {{ $value }}
                                                    </label>
                                                </td>

                                            </tr>
                                        </table>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                        @error('score_new')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @csrf
                        <p class="mt-2 text-slate-500 mt-8"><b>Escuchar también significa comprender.</b></p>
                        <p class="mt-2 text-slate-500">Cuéntanos brevemente el motivo de tu valoración. Cada comentario
                            nos ayuda a entender mejor tu experiencia y a convertirla en una oportunidad de mejora.</p>
                        <p class=" mt-8 text-slate-500">
                            <b><span id="comment-requirement-hint"></span></b>
                        </p>
                        <label class="block text-sm font-medium text-slate-700 mt-2">Comentario

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
                        <p class="mt-2 text-slate-500"></p>Escuchamos para mejorar.</p>
                    
                @endif
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('input[name="score_new"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                const hint = document.getElementById('comment-requirement-hint');
                hint.textContent = Number(radio.value) <= 6 ?
                    'Antes de continuar, comparte el motivo de tu valoración.' : '';
            });
        });
    </script>
</body>

</html>
