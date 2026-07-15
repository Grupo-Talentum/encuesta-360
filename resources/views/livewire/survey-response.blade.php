<div
    class="mx-auto max-w-2xl px-4 py-10 sm:py-16"
    x-data
    x-on:survey-scroll-top.window="window.scrollTo({ top: 0, behavior: 'smooth' })"
>

    @if ($step === 'invalid')
        <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <h1 class="mt-5 text-xl font-semibold text-slate-900">Enlace no válido</h1>
            <p class="mt-2 text-slate-500">Este enlace de evaluación no existe o ya no está disponible.</p>
        </div>
    @elseif ($step === 'intro')
        <x-survey.step-indicator :step="$step" />

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-2 bg-gradient-to-r from-indigo-500 to-violet-500"></div>

            <div class="p-8 sm:p-10">
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600">
                    Evaluación 360 · {{ $this->totalCount }} {{ $this->totalCount === 1 ? 'evaluación' : 'evaluaciones' }}
                </span>

                <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $session->survey->title }}</h1>

                @if ($session->survey->description)
                    <p class="mt-3 text-slate-600">{{ $session->survey->description }}</p>
                @endif

                @if ($session->survey->instructions)
                    <div class="mt-6 space-y-2 rounded-xl bg-slate-50 p-4 text-sm leading-relaxed text-slate-600">
                        @foreach (explode("\n", $session->survey->instructions) as $line)
                            @continue(trim($line) === '')
                            @php
                                $scaleColor = match (true) {
                                    str_contains($line, 'muy por debajo') => 'text-red-600',
                                    str_contains($line, 'aceptable') => 'text-amber-600',
                                    str_contains($line, 'buen desempeño') => 'text-indigo-600',
                                    str_contains($line, 'excelente') => 'text-emerald-600',
                                    default => null,
                                };
                            @endphp
                            <p @class(['font-bold' => $scaleColor, $scaleColor])>{{ trim($line) }}</p>
                        @endforeach
                    </div>
                @endif

                @if ($session->survey->start_message)
                    <p class="mt-4 text-sm text-slate-500">{{ $session->survey->start_message }}</p>
                @endif

                <p class="mt-4 text-sm text-slate-500">
                    Vas a evaluar a:
                    <span class="font-medium text-slate-700">{{ $session->evaluations->pluck('evaluatee.name')->implode(', ') }}</span>
                </p>

                <button
                    type="button"
                    wire:click="start"
                    class="mt-8 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3.5 font-semibold text-white shadow-sm shadow-indigo-200 transition hover:bg-indigo-700 active:scale-[.99]"
                >
                    Comenzar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @elseif ($step === 'form')
        <x-survey.step-indicator :step="$step" />

        <form wire:submit="submit">
            <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-bold text-slate-900">{{ $session->survey->title }}</h1>
                    <span class="text-xs font-medium text-slate-400">{{ $this->resolvedCount + 1 }} de {{ $this->totalCount }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    Evaluando a <span class="font-medium text-slate-700">{{ $this->currentEvaluation->evaluatee->name }}</span>
                </p>
                <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $this->totalCount > 0 ? ($this->resolvedCount / $this->totalCount * 100) : 0 }}%"></div>
                </div>
            </div>

            @foreach ($session->survey->sections as $section)
                <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                            {{ $loop->iteration }}
                        </span>
                        <div>
                            <h2 class="font-semibold text-slate-900">{{ $section->title }}</h2>
                            @if ($section->description)
                                <p class="text-xs text-slate-500">{{ $section->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="p-6 sm:p-7">
                        @foreach ($section->questions as $question)
                            <x-survey.question-input :question="$question" :name="'answers.' . $question->id" />
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3.5 font-semibold text-white shadow-sm shadow-indigo-200 transition hover:bg-indigo-700 active:scale-[.99]"
            >
                {{ $this->resolvedCount + 1 === $this->totalCount ? 'Enviar y finalizar' : 'Siguiente evaluación' }}
            </button>

            <button
                type="button"
                wire:click="skip"
                onclick="return confirm('¿Confirmás que no trabajaste con {{ $this->currentEvaluation->evaluatee->name }} y querés omitir esta evaluación?')"
                class="mt-3 w-full text-center text-sm text-slate-500 underline decoration-slate-300 underline-offset-2 hover:text-slate-700"
            >
                No he trabajado con esta persona — omitir evaluación
            </button>
        </form>

        @if (count($this->scaleLegend))
            <div x-data="{ open: true }" class="fixed bottom-6 right-6 z-40">
                <div
                    x-show="open"
                    x-cloak
                    x-on:click.outside="open = false"
                    x-transition
                    class="absolute bottom-16 right-0 w-72 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl"
                >
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Escala de valoración</p>
                    <div class="space-y-1.5">
                        @foreach ($this->scaleLegend as $item)
                            <p class="text-sm font-bold {{ $item['color'] }}">{{ $item['text'] }}</p>
                        @endforeach
                    </div>
                </div>

                <button
                    type="button"
                    x-on:click="open = !open"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-300 transition hover:bg-indigo-700"
                    aria-label="Recordar escala de valoración"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                </button>
            </div>
        @endif
    @elseif ($step === 'done')
        <x-survey.step-indicator :step="$step" />

        <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h1 class="mt-5 text-xl font-semibold text-slate-900">¡Gracias!</h1>
            @if ($session->survey->end_message)
                <p class="mt-2 whitespace-pre-line text-slate-600">{{ $session->survey->end_message }}</p>
            @else
                <p class="mt-2 text-slate-500">Tus respuestas fueron registradas correctamente.</p>
            @endif
        </div>
    @endif

</div>
