@props(['step'])

@php
$steps = [
    'intro' => 'Introducción',
    'form' => 'Preguntas',
    'done' => 'Confirmación',
];
$order = array_keys($steps);
$currentIndex = array_search($step, $order, true);
@endphp

<div class="mb-8 flex items-center justify-center gap-2">
    @foreach ($steps as $key => $label)
        @php $index = array_search($key, $order, true); @endphp
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold
                {{ $index < $currentIndex ? 'bg-indigo-600 text-white' : ($index === $currentIndex ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-200 text-slate-500') }}">
                @if ($index < $currentIndex)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                    </svg>
                @else
                    {{ $index + 1 }}
                @endif
            </div>
            <span class="hidden text-sm font-medium sm:inline {{ $index === $currentIndex ? 'text-indigo-600' : 'text-slate-400' }}">
                {{ $label }}
            </span>
        </div>
        @if (! $loop->last)
            <div class="h-px w-8 {{ $index < $currentIndex ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
        @endif
    @endforeach
</div>
