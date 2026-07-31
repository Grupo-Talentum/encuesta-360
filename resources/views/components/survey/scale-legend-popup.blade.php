@props(['legend'])

@if (count($legend))
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
                @foreach ($legend as $item)
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
