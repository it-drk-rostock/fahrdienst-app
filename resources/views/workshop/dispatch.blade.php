<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Werkstatt-Disposition') }}
        </h2>
    </x-slot>

    <div class="py-12 w-full px-4 mx-auto">
        <div class="space-y-8">
            @if(empty($vehicles))
                <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">Alles erledigt! Keine offenen Aufgaben.</div>
            @else
                @foreach($vehicles as $vehicle)
                    {{--
                       FARB-LOGIK (Aus Dashboard übernommen):
                       Gerade: bg-gray-100 (Stärkeres Grau)
                       Ungerade: bg-white
                    --}}
                    @php
                        $cardBg = $loop->even ? 'bg-gray-100' : 'bg-white';
                    @endphp

                    {{-- CONTAINER --}}
                    <div class="overflow-hidden shadow-lg sm:rounded-lg border border-gray-300 {{ $vehicle->is_preselected ? 'ring-2 ring-blue-500' : '' }} {{ $cardBg }}"
                         x-data="{ selectedDamages: [], selectedServices: [] }">

                        {{--
                            HEADER:
                            Dunkelgrau (bg-gray-700) und weiße Schrift,
                            damit es exakt zum Dashboard-Tabellenkopf passt.
                        --}}
                        <div class="px-6 py-4 border-b border-gray-400 bg-gray-700 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    {{ $vehicle->license_plate }}
                                    @if($vehicle->is_preselected)
                                        <span class="bg-blue-500 text-white text-[10px] px-2 py-0.5 rounded shadow-sm">Ausgewählt</span>
                                    @endif
                                </h3>
                                {{-- Untertitel in hellem Grau (text-gray-300) für Kontrast auf dunklem Grund --}}
                                <span class="text-xs text-gray-300 font-bold uppercase tracking-wider">{{ $vehicle->manufacturer }} {{ $vehicle->model }}</span>
                            </div>

                            <button x-show="selectedDamages.length > 0 || selectedServices.length > 0"
                                    @click="$dispatch('open-workshop-modal', {
                                        vehicle_id: {{ $vehicle->id }},
                                        damages: selectedDamages,
                                        services: selectedServices,
                                        cost_center: '{{ $vehicle->costCenter ? $vehicle->costCenter->code . ' (' . $vehicle->costCenter->name . ')' : '' }}'
                                    })"
                                    class="bg-blue-500 text-white px-4 py-2 rounded text-xs font-bold uppercase hover:bg-blue-400 transition shadow-md tracking-wider">
                                Beauftragen ➔
                            </button>
                        </div>

                        {{-- TABELLE --}}
                        <table class="min-w-full divide-y divide-gray-300 table-fixed">
                            {{-- THEAD: Leicht abgedunkelt im Vergleich zur Karte, aber hell --}}
                            <thead class="bg-gray-200 text-gray-700">
                                <tr>
                                    <th class="w-16 px-6 py-2"></th>
                                    <th class="px-6 py-2 text-left text-[10px] font-bold uppercase tracking-wider w-auto">Aufgabe</th>
                                    <th class="px-6 py-2 text-left text-[10px] font-bold uppercase tracking-wider w-40">Fällig / Gemeldet</th>
                                    <th class="px-6 py-2 text-left text-[10px] font-bold uppercase tracking-wider w-32">Typ</th>
                                </tr>
                            </thead>

                            {{-- BODY: Transparent, damit Gray-100 oder White durchscheint --}}
                            <tbody class="divide-y divide-gray-300 bg-transparent">
                                @foreach($vehicle->todo_list as $task)
                                    {{--
                                        ZEILE:
                                        - Standard: Transparent (zeigt Kartenfarbe)
                                        - Dringend: bg-rose-100 (wie im Dashboard für überfällig)
                                        - Hover: bg-blue-100 (exakt wie im Dashboard)
                                    --}}
                                    <tr class="{{ $task['urgent'] ? 'bg-rose-100' : '' }} hover:bg-blue-100 transition duration-75 group">
                                        <td class="px-6 py-3">
                                            @if($task['type'] == 'damage')
                                                <input type="checkbox"
                                                       value="{{ $task['id'] }}"
                                                       x-model="selectedDamages"
                                                       class="rounded border-gray-400 text-blue-600 h-4 w-4 bg-white focus:ring-offset-0 cursor-pointer">
                                            @else
                                                <input type="checkbox"
                                                       value="{{ $task['id'] }}"
                                                       x-model="selectedServices"
                                                       x-init="if({{ $task['preselected'] ? 'true' : 'false' }}) selectedServices.push('{{ $task['id'] }}')"
                                                       class="rounded border-gray-400 text-green-600 h-4 w-4 bg-white focus:ring-offset-0 cursor-pointer">
                                            @endif
                                        </td>

                                        <td class="px-6 py-3 text-sm font-bold text-gray-700 truncate group-hover:text-blue-900" title="{{ $task['label'] }}">
                                            {{ $task['label'] }}
                                        </td>

                                        <td class="px-6 py-3 text-xs font-mono font-bold {{ $task['urgent'] ? 'text-rose-700' : 'text-gray-600' }}">
                                            {{ $task['date']->format('d.m.Y') }}
                                        </td>

                                        <td class="px-6 py-3">
                                            {{-- Badges im Stil des Dashboards (Weißer Hintergrund, Border) --}}
                                            @if($task['type'] == 'damage')
                                                <span class="px-2 py-1 rounded text-[10px] font-bold text-gray-700 border border-gray-300 bg-white shadow-sm">Mangel</span>
                                            @elseif($task['type'] == 'adhoc_service')
                                                <span class="px-2 py-1 rounded text-[10px] font-bold text-blue-700 border border-blue-300 bg-white shadow-sm">Wartung</span>
                                            @else
                                                <span class="px-2 py-1 rounded text-[10px] font-bold text-green-700 border border-green-300 bg-white shadow-sm">Prüfung</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- MODAL (Unverändert) --}}
    <div x-data="{
            show: false,
            vehicle_id: null,
            damages: [],
            services: [],
            isTransport: false,
            transportMethod: 'department',
            driverStatus: 'search_needed',
            costCenter: ''
         }"
         @open-workshop-modal.window="
            show = true;
            vehicle_id = $event.detail.vehicle_id;
            damages = $event.detail.damages;
            services = $event.detail.services;
            costCenter = $event.detail.cost_center;
            isTransport = false;
            transportMethod = 'department';
            driverStatus = 'search_needed';
         "
         x-show="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>

        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 overflow-y-auto max-h-[90vh]" @click.away="show = false">
            <h3 class="text-lg font-bold mb-4 text-blue-600">Werkstatt beauftragen</h3>

            <form action="{{ route('workshop.store') }}" method="POST">
                @csrf
                <input type="hidden" name="vehicle_id" :value="vehicle_id">
                <template x-for="id in damages"><input type="hidden" name="selected_damages[]" :value="id"></template>
                <template x-for="id in services"><input type="hidden" name="selected_services[]" :value="id"></template>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500">Dienstleister</label>
                        <input list="providers" name="provider_name" class="w-full rounded border-gray-300 text-sm" placeholder="Name..." required>
                        <datalist id="providers">
                            @foreach($providers as $provider)
                                <option value="{{ $provider->name }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-bold uppercase text-gray-500">Start</label><input type="datetime-local" name="start_time" required class="w-full rounded border-gray-300 text-sm"></div>
                        <div><label class="block text-xs font-bold uppercase text-gray-500">Ende (Plan)</label><input type="datetime-local" name="planned_end_time" required class="w-full rounded border-gray-300 text-sm"></div>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <label class="flex items-center mb-2 cursor-pointer bg-gray-50 p-2 rounded border border-gray-200 hover:bg-gray-100">
                            <input type="checkbox" name="is_transport_organized" x-model="isTransport" class="rounded border-gray-300 text-blue-600 h-4 w-4">
                            <span class="ml-2 text-sm font-bold text-gray-700">Verbringung organisieren?</span>
                        </label>

                        <div x-show="isTransport" class="bg-blue-50 p-3 rounded border border-blue-200 space-y-3">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Methode</label>
                                <select name="transport_method" x-model="transportMethod" class="w-full rounded border-gray-300 text-sm">
                                    <option value="department">Abteilung verbringt selbst</option>
                                    <option value="driver_service">Fahrer aus Fahrdienst</option>
                                    <option value="replacement">Werkstatt-Ersatzwagen</option>
                                </select>
                            </div>

                            <div x-show="transportMethod === 'driver_service'" class="space-y-3 border-t border-blue-200 pt-2">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500">Fahrer</label>
                                    <input list="drivers" name="transport_driver_name" placeholder="Fahrer suchen..." class="w-full rounded border-gray-300 text-sm">
                                    <datalist id="drivers">
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Status</label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center cursor-pointer hover:bg-rose-50 p-1 rounded">
                                            <input type="radio" name="transport_driver_status" value="search_needed" x-model="driverStatus" class="text-rose-500 focus:ring-rose-500">
                                            <span class="ml-2 text-xs font-bold text-rose-600">🔍 Fahrer suchen</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer hover:bg-green-50 p-1 rounded">
                                            <input type="radio" name="transport_driver_status" value="informed" x-model="driverStatus" class="text-green-600 focus:ring-green-500">
                                            <span class="ml-2 text-xs font-bold text-green-600">✅ Informiert</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Abrechnung an</label>
                                <input type="text" name="transport_billing_department" :value="costCenter" class="w-full rounded border-gray-300 text-sm bg-gray-100" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="show = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Abbrechen</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700">Auftrag erstellen</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
