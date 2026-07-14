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
            $fraction = ($value - $min) / max($max - $min, 1);
            $selectedClasses = match (true) {
                $fraction < 0.4 => 'peer-checked:border-red-500 peer-checked:bg-red-500',
                $fraction < 0.7 => 'peer-checked:border-amber-500 peer-checked:bg-amber-500',
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
