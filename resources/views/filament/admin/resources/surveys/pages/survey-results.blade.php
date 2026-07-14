@php
    use App\Enums\QuestionType;

    // x-filament::badge entiende los nombres semanticos de Filament (success/warning/danger).
    $badgeColor = function (float $value, float $max) {
        $fraction = $max > 0 ? $value / $max : 0;

        return match (true) {
            $fraction < 0.4 => 'danger',
            $fraction < 0.7 => 'warning',
            default => 'success',
        };
    };

    // El panel de Filament no compila la paleta completa de Tailwind, asi que para
    // elementos propios (no componentes de Filament) usamos colores reales via app.css.
    $barColor = function (float $value, float $max) {
        $fraction = $max > 0 ? $value / $max : 0;

        return match (true) {
            $fraction < 0.4 => 'bg-red-500',
            $fraction < 0.7 => 'bg-amber-500',
            default => 'bg-emerald-500',
        };
    };

    $formatAnswer = function ($answer) {
        $value = $answer->value;

        return match ($answer->question->type) {
            QuestionType::YesNo => ((int) $value === 1) ? 'Sí' : 'No',
            QuestionType::MultipleChoice => is_array($value) ? implode(', ', $value) : $value,
            default => $value,
        };
    };
@endphp

@vite(['resources/css/app.css'])

<x-filament-panels::page>
    <div x-data="{ tab: 'summary', search: '' }">
        <x-filament::tabs label="Secciones de resultados">
            <x-filament::tabs.item
                alpine-active="tab === 'summary'"
                x-on:click="tab = 'summary'"
            >
                Resumen
            </x-filament::tabs.item>
            <x-filament::tabs.item
                alpine-active="tab === 'questions'"
                x-on:click="tab = 'questions'"
            >
                Por pregunta
            </x-filament::tabs.item>
            <x-filament::tabs.item
                alpine-active="tab === 'people'"
                x-on:click="tab = 'people'"
            >
                Por persona
            </x-filament::tabs.item>
            <x-filament::tabs.item
                alpine-active="tab === 'comments'"
                x-on:click="tab = 'comments'"
                :badge="$results['comments']->count()"
            >
                Comentarios
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Resumen --}}
        <div x-show="tab === 'summary'" class="mt-6 space-y-6">
            <x-filament::section heading="Participación">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total evaluaciones</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $results['total'] }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Completadas</p>
                        <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $results['completed'] }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Pendientes</p>
                        <p class="mt-1 text-3xl font-bold text-amber-700">{{ $results['pending'] }}</p>
                    </div>
                    <div class="rounded-xl bg-indigo-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">Participación</p>
                        <p class="mt-1 text-3xl font-bold text-indigo-700">{{ $results['participation'] }}%</p>
                    </div>
                </div>
            </x-filament::section>

            @if ($results['npsScore'] !== null)
                <x-filament::section heading="Resultado NPS">
                    <div class="flex items-center gap-4">
                        <p class="text-4xl font-bold {{ $results['npsScore'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $results['npsScore'] }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Promotores menos detractores, sobre las respuestas de la pregunta NPS.
                        </p>
                    </div>
                </x-filament::section>
            @endif
        </div>

        {{-- Por pregunta --}}
        <div x-show="tab === 'questions'" x-cloak class="mt-6">
            <x-filament::section heading="Promedios por pregunta">
                @if ($results['questionAverages']->isEmpty())
                    <p class="text-sm text-gray-500">Todavía no hay respuestas.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($results['questionAverages'] as $row)
                            @php $max = $row['question']->type === QuestionType::Nps ? 10 : ($row['question']->type === QuestionType::Rating5 ? 5 : 10); @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="pr-4 text-gray-700">{{ $row['question']->title }}</span>
                                    <x-filament::badge :color="$badgeColor($row['average'], $max)">
                                        {{ $row['average'] }}
                                    </x-filament::badge>
                                </div>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full {{ $barColor($row['average'], $max) }}" style="width: {{ min($row['average'] / $max * 100, 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Por persona --}}
        <div x-show="tab === 'people'" x-cloak class="mt-6 space-y-6">
            <x-filament::section heading="Promedios por participante evaluado">
                @if ($results['employeeAverages']->isEmpty())
                    <p class="text-sm text-gray-500">Todavía no hay respuestas.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($results['employeeAverages'] as $row)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $row['employee']->name }}</span>
                                    <x-filament::badge :color="$badgeColor($row['average'], 10)">
                                        {{ $row['average'] }}
                                    </x-filament::badge>
                                </div>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full {{ $barColor($row['average'], 10) }}" style="width: {{ min($row['average'] / 10 * 100, 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>

            <x-filament::section heading="Detalle por participante evaluado" description="Qué respondió cada evaluador sobre cada persona.">
                @if ($results['evaluationsByEvaluatee']->isEmpty())
                    <p class="text-sm text-gray-500">Todavía no hay evaluaciones.</p>
                @else
                    <div class="mb-4">
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Buscar participante..."
                            class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                        >
                    </div>

                    <div class="space-y-3">
                        @foreach ($results['evaluationsByEvaluatee'] as $group)
                            <div x-show="search === '' || {{ Illuminate\Support\Js::from(strtolower($group['employee']->name)) }}.includes(search.toLowerCase())">
                                <details class="group rounded-xl border border-gray-200" open>
                                    <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3">
                                        <span class="font-semibold text-gray-900">{{ $group['employee']->name }}</span>
                                        <span class="flex items-center gap-2">
                                            <x-filament::badge color="gray">
                                                {{ $group['evaluations']->count() }} {{ $group['evaluations']->count() === 1 ? 'evaluación' : 'evaluaciones' }}
                                            </x-filament::badge>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-gray-400 transition group-open:rotate-180">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </summary>

                                    <div class="space-y-3 border-t border-gray-100 px-4 py-3">
                                        @foreach ($group['evaluations'] as $evaluation)
                                            <div class="rounded-lg bg-gray-50 p-3">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-medium text-gray-900">Evaluado por {{ $evaluation->evaluator->name }}</p>
                                                    <x-filament::badge :color="$evaluation->status->value === 'completed' ? 'success' : 'warning'">
                                                        {{ $evaluation->status->value === 'completed' ? 'Completada' : 'Pendiente' }}
                                                    </x-filament::badge>
                                                </div>

                                                @if ($evaluation->answers->isEmpty())
                                                    <p class="mt-2 text-sm text-gray-400">Sin respuestas todavía.</p>
                                                @else
                                                    <dl class="mt-3 space-y-2">
                                                        @foreach ($evaluation->answers as $answer)
                                                            <div class="text-sm">
                                                                <dt class="text-gray-500">{{ $answer->question->title }}</dt>
                                                                <dd class="mt-0.5 font-medium text-gray-900">{{ $formatAnswer($answer) }}</dd>
                                                            </div>
                                                        @endforeach
                                                    </dl>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Comentarios --}}
        <div x-show="tab === 'comments'" x-cloak class="mt-6">
            <x-filament::section heading="Comentarios">
                @if ($results['comments']->isEmpty())
                    <p class="text-sm text-gray-500">Todavía no hay comentarios.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($results['comments'] as $comment)
                            <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                                <p class="text-xs text-gray-500">
                                    {{ $comment['evaluator']->name }} → {{ $comment['evaluatee']->name }} · {{ $comment['question']->title }}
                                </p>
                                <p class="mt-1 text-sm text-gray-700">{{ $comment['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
