@vite(['resources/css/app.css'])

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section heading="Participación">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-gray-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total destinatarios</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $results['total'] }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Respondidas</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $results['answered'] }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Pendientes</p>
                    <p class="mt-1 text-3xl font-bold text-amber-700">{{ $results['pending'] }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section heading="Resultado NPS">
            @if ($results['npsScore'] === null)
                <p class="text-sm text-gray-500">Todavía no hay respuestas.</p>
            @else
                <div class="flex flex-wrap items-center gap-6">
                    <p class="text-4xl font-bold {{ $results['npsScore'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $results['npsScore'] }}
                    </p>
                    <div class="flex gap-4 text-sm">
                        <span><span class="font-semibold text-emerald-600">{{ $results['promoters'] }}</span> promotores</span>
                        <span><span class="font-semibold text-amber-600">{{ $results['passives'] }}</span> pasivos</span>
                        <span><span class="font-semibold text-red-600">{{ $results['detractors'] }}</span> detractores</span>
                    </div>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Respuestas">
            @if ($results['responses']->isEmpty())
                <p class="text-sm text-gray-500">Todavía no hay destinatarios cargados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                                <th class="py-2 pr-4">Nombre</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Puntaje</th>
                                <th class="py-2 pr-4">Comentario</th>
                                <th class="py-2 pr-4">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results['responses'] as $response)
                                <tr class="border-b border-gray-100 last:border-0">
                                    <td class="py-2 pr-4 font-medium text-gray-900">{{ $response->name }}</td>
                                    <td class="py-2 pr-4 text-gray-500">{{ $response->email }}</td>
                                    <td class="py-2 pr-4">
                                        @if ($response->score === null)
                                            <x-filament::badge color="gray">Pendiente</x-filament::badge>
                                        @else
                                            <x-filament::badge :color="$response->score >= 9 ? 'success' : ($response->score >= 7 ? 'warning' : 'danger')">
                                                {{ $response->score }}
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">{{ $response->comment ?: '—' }}</td>
                                    <td class="py-2 pr-4 text-gray-500">{{ $response->answered_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
