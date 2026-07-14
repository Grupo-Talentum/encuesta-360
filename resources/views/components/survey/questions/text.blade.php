@props(['question', 'name'])

@php
use App\Enums\QuestionType;

$isLong = $question->type === QuestionType::LongText;
@endphp

@if ($isLong)
    <textarea
        wire:model="{{ $name }}"
        rows="4"
        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-50"
    ></textarea>
@else
    <input
        type="text"
        wire:model="{{ $name }}"
        class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-50"
    >
@endif
