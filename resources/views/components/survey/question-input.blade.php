@props(['question', 'name'])

@php
use App\Enums\QuestionType;

$component = match ($question->type) {
    QuestionType::Rating5, QuestionType::Rating10, QuestionType::Nps => 'survey.questions.scale',
    QuestionType::ShortText, QuestionType::LongText => 'survey.questions.text',
    QuestionType::YesNo => 'survey.questions.yes-no',
    QuestionType::SingleChoice, QuestionType::MultipleChoice => 'survey.questions.choice',
};
@endphp

<div class="mb-7 border-b border-slate-100 pb-7 last:mb-0 last:border-0 last:pb-0">
    <label class="block font-medium text-slate-900">
        {{ $question->title }}
        @if ($question->is_required)
            <span class="text-indigo-500">*</span>
        @endif
    </label>

    @if ($question->description)
        <p class="mt-0.5 text-sm text-slate-500">{{ $question->description }}</p>
    @endif

    <div class="mt-3">
        <x-dynamic-component :component="$component" :question="$question" :name="$name" />
    </div>

    @error($name)
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
