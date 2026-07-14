@props(['question', 'name'])

<div class="flex gap-3">
    @foreach (['1' => 'Sí', '0' => 'No'] as $value => $label)
        <label class="cursor-pointer">
            <input type="radio" wire:model="{{ $name }}" value="{{ $value }}" class="peer sr-only">
            <span class="inline-flex min-w-[6rem] items-center justify-center rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-md hover:border-slate-300">
                {{ $label }}
            </span>
        </label>
    @endforeach
</div>
