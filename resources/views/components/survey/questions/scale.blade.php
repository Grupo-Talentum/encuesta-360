@props(['question', 'name'])

@php
use App\Enums\QuestionType;

[$min, $max, $isNps] = match ($question->type) {
    QuestionType::Rating5 => [1, 5, false],
    QuestionType::Rating10 => [1, 10, false],
    QuestionType::Nps => [0, 10, true],
};
@endphp

<div class="flex flex-wrap gap-2">
    @foreach (range($min, $max) as $value)
        @php
            // Mismos cortes que la leyenda de la escala (1-3 rojo, 4-6 ambar, 7-8 indigo, 9-10 verde).
            $position = ($value - $min + 1) / ($max - $min + 1);
            $selectedClasses = match (true) {
                $position <= 0.3 => 'peer-checked:border-red-500 peer-checked:bg-red-500',
                $position <= 0.6 => 'peer-checked:border-amber-500 peer-checked:bg-amber-500',
                $position <= 0.8 => 'peer-checked:border-indigo-500 peer-checked:bg-indigo-500',
                default => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-500',
            };
        @endphp
        <label class="cursor-pointer">
            <input type="radio" wire:model="{{ $name }}" value="{{ $value }}" class="peer sr-only">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 transition peer-checked:text-white peer-checked:shadow-md {{ $selectedClasses }} hover:border-slate-300">
                {{ $value }}
            </span>
        </label>
    @endforeach
</div>

@if ($isNps)
    <div class="mt-2 flex justify-between text-xs text-slate-400">
        <span>Nada probable</span>
        <span>Muy probable</span>
    </div>
@endif
