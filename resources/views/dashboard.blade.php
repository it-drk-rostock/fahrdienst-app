<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fuhrpark Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 w-full px-4 mx-auto space-y-4">

        <form method="GET" action="{{ route('dashboard') }}" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 justify-between items-end">

            <div class="flex flex-wrap gap-4 w-full md:w-auto items-end">
                <div class="w-full md:w-48">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Suche</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Kennzeichen..."
                           class="w-full rounded border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bereich</label>
                    <select name="cost_center_id" class="rounded border-gray-300 text-sm w-40 shadow-sm cursor-pointer" onchange="this.form.submit()">
                        <option value="">Alle</option>
                        @foreach($costCenters as $cc)
                            <option value="{{ $cc->id }}" {{ request('cost_center_id') == $cc->id ? 'selected' : '' }}>
                                {{ $cc->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                    <select name="show_only" class="rounded border-gray-300 text-sm shadow-sm cursor-pointer" onchange="this.form.submit()">
                        <option value="">Alles anzeigen</option>
                        <option value="damages" {{ request('show_only') == 'damages' ? 'selected' : '' }}>Nur Mängel</option>
                        <option value="inspections" {{ request('show_only') == 'inspections' ? 'selected' : '' }}>Nur Prüfungen</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Zeilen</label>
                    <select name="per_page" class="rounded border-gray-300 text-sm shadow-sm cursor-pointer font-bold" onchange="this.form.submit()">
                        <option value="all" {{ request('per_page', 'all') == 'all' ? 'selected' : '' }}>Alle</option>
                        <option value="15" {{ request('per_page') == '15' ? 'selected' : '' }}>15</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2 items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-bold shadow hover:bg-blue-700 transition uppercase tracking-wider">
                    Suchen
                </button>
                <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-bold shadow hover:bg-gray-700 transition uppercase tracking-wider flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg border border-gray-300 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider">Kennzeichen</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider">Fahrzeug</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider">Bereich</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider">Fälligkeiten</th>
                        <th class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-[10px] font-bold uppercase tracking-wider">Aktion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300 bg-white">
                    @forelse($vehicles as $vehicle)
                        <tr class="even:bg-gray-100 odd:bg-white hover:bg-blue-100 transition duration-75 group">

                            <td class="px-6 py-4 whitespace-nowrap border-r border-transparent group-hover:border-blue-200">
                                <span class="font-mono font-bold text-base text-gray-800 group-hover:text-blue-800 bg-white px-2 py-1 rounded border border-gray-200 shadow-sm">
                                    {{ $vehicle->license_plate }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-bold">
                                {{ $vehicle->manufacturer }} {{ $vehicle->model }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                <span class="px-2 py-1 rounded border border-gray-300 bg-white font-bold">
                                    {{ $vehicle->costCenter->code ?? '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                                            <div class="flex flex-wrap gap-1.5 max-w-[240px]">
                                                                @forelse($vehicle->getActiveInspections() as $label => $date)
                                                                    @php
                                                                        if (is_null($date)) {
                                                                            // DATUM FEHLT KOMPLETT -> ROT
                                                                            $colors = 'bg-rose-100 text-rose-800 border-rose-300 ring-1 ring-rose-400 animate-pulse';
                                                                            $displayDate = 'FEHLT';
                                                                        } else {
                                                                            $daysUntil = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

                                                                            if ($daysUntil < 0) {
                                                                                // BEREITS ABGELAUFEN -> ROT
                                                                                $colors = 'bg-rose-100 text-rose-800 border-rose-300 ring-1 ring-rose-400';
                                                                                $displayDate = $date->format('m/y');
                                                                            } elseif ($daysUntil <= 60) {
                                                                                // IN DEN NÄCHSTEN 60 TAGEN -> ORANGE
                                                                                $colors = 'bg-orange-100 text-orange-800 border-orange-300';
                                                                                $displayDate = $date->format('m/y');
                                                                            } else {
                                                                                // WEITER WEG -> GRÜN
                                                                                $colors = 'bg-green-50 text-green-700 border-green-300';
                                                                                $displayDate = $date->format('m/y');
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <div class="flex flex-col items-center justify-center px-2 py-1 rounded border {{ $colors }} min-w-[60px] shadow-sm">
                                                                        <span class="text-[7px] uppercase font-bold tracking-wider leading-none mb-0.5">{{ $label }}</span>
                                                                        <span class="text-[10px] font-bold leading-none font-mono">{{ $displayDate }}</span>
                                                                    </div>
                                                                @empty
                                                                    <span class="text-[10px] text-green-700 font-bold flex items-center gap-1">
                                                                        <span class="bg-green-100 rounded-full w-4 h-4 flex items-center justify-center border border-green-200">✓</span>
                                                                        Keine Prüfungen nötig
                                                                    </span>
                                                                @endforelse
                                                            </div>
                                                        </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-24 bg-gray-300 rounded-full h-1.5 overflow-hidden border border-gray-400">
                                        <div class="h-full {{ $vehicle->documentation_percentage == 100 ? 'bg-green-600' : 'bg-orange-500' }}" style="width: {{ $vehicle->documentation_percentage }}%"></div>
                                    </div>
                                    @php $count = $vehicle->damages->where('status', '!=', 'resolved')->count(); @endphp
                                    @if($count > 0)
                                        <div class="w-24 py-0.5 rounded-md bg-rose-600 text-white text-[9px] font-bold shadow-sm flex items-center justify-center gap-1">
                                            ⚠️ {{ $count }} {{ $count === 1 ? 'Mangel' : 'Mängel' }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-800 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-700 shadow-md transition transform hover:-translate-y-0.5">
                                    Akte ➔
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-gray-500 font-bold bg-white">Keine Fahrzeuge gefunden.</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if(request('per_page') !== 'all')
                <div class="p-4 border-t border-gray-300 bg-gray-100">
                    {{ $vehicles->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
