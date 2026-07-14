@props(['question', 'name'])

@php
use App\Enums\QuestionType;

$isMultiple = $question->type === QuestionType::MultipleChoice;
@endphp

<div class="flex flex-col gap-2">
    @foreach ($question->options ?? [] as $option)
        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-slate-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
            <input
                type="{{ $isMultiple ? 'checkbox' : 'radio' }}"
                wire:model="{{ $name }}"
                value="{{ $option }}"
                class="h-4 w-4 shrink-0 {{ $isMultiple ? 'rounded' : 'rounded-full' }} border-slate-300 text-indigo-600 focus:ring-indigo-500"
            >
            <span class="text-sm text-slate-700">{{ $option }}</span>
        </label>
    @endforeach
</div>
