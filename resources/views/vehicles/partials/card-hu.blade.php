<div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100 h-full flex flex-col">
    <div class="flex justify-between items-center border-b border-gray-100 pb-2 mb-4">
        <h4 class="text-xs font-bold text-blue-800 uppercase">Prüfungen & Fristen</h4>
        <button @click="showHuModal = true" class="text-[10px] bg-blue-50 text-blue-700 px-2 py-1 rounded hover:bg-blue-100 border border-blue-200 font-bold transition shadow-sm">
            + Bericht erfassen
        </button>
    </div>

    <div class="space-y-3 mb-6">
        {{-- HU --}}
        <div class="flex justify-between items-center bg-gray-50 p-3 rounded border border-gray-200">
            <span class="font-bold text-sm text-gray-700">HU (TÜV)</span>
            <span class="font-bold {{ $vehicle->next_hu_date && $vehicle->next_hu_date->isPast() ? 'text-red-600 animate-pulse' : 'text-gray-800' }}">
                {{ $vehicle->next_hu_date ? $vehicle->next_hu_date->format('m / Y') : 'Fehlt' }}
            </span>
        </div>

        {{-- UVV --}}
        <div class="flex justify-between items-center bg-gray-50 p-3 rounded border border-gray-200">
            <span class="font-bold text-sm text-gray-700">UVV (Fahrzeug)</span>
            <span class="font-bold {{ $vehicle->next_uvv_date && $vehicle->next_uvv_date->isPast() ? 'text-red-600 animate-pulse' : 'text-gray-800' }}">
                {{ $vehicle->next_uvv_date ? $vehicle->next_uvv_date->format('m / Y') : 'Fehlt' }}
            </span>
        </div>

        {{-- LIFT --}}
        @if($vehicle->has_lift)
        <div class="flex justify-between items-center bg-blue-50 p-3 rounded border border-blue-200">
            <span class="font-bold text-sm text-blue-900">UVV Lift / Rampe</span>
            <span class="font-bold {{ $vehicle->next_lift_uvv_date && $vehicle->next_lift_uvv_date->isPast() ? 'text-red-600 animate-pulse' : 'text-blue-900' }}">
                {{ $vehicle->next_lift_uvv_date ? $vehicle->next_lift_uvv_date->format('m / Y') : 'Fehlt' }}
            </span>
        </div>
        @endif

        {{-- BOKRAFT --}}
        @if($vehicle->is_bokraft)
        <div class="flex justify-between items-center bg-purple-50 p-3 rounded border border-purple-200">
            <span class="font-bold text-sm text-purple-900">BOKraft (PersBefG)</span>
            <span class="font-bold {{ $vehicle->next_bokraft_date && $vehicle->next_bokraft_date->isPast() ? 'text-red-600 animate-pulse' : 'text-purple-900' }}">
                {{ $vehicle->next_bokraft_date ? $vehicle->next_bokraft_date->format('m / Y') : 'Fehlt' }}
            </span>
        </div>
        @endif

        {{-- E-KABEL --}}
        @if($vehicle->is_electric)
        <div class="flex justify-between items-center bg-emerald-50 p-3 rounded border border-emerald-200">
            <span class="font-bold text-sm text-emerald-900">DGUV V3 (Ladekabel)</span>
            <span class="font-bold {{ $vehicle->next_cable_uvv_date && $vehicle->next_cable_uvv_date->isPast() ? 'text-red-600 animate-pulse' : 'text-emerald-900' }}">
                {{ $vehicle->next_cable_uvv_date ? $vehicle->next_cable_uvv_date->format('m / Y') : 'Fehlt' }}
            </span>
        </div>
        @endif
    </div>

    {{-- Letzte Prüfberichte Historie --}}
    <div class="mt-auto pt-4 border-t border-gray-100">
        <h5 class="text-[10px] font-bold text-gray-400 uppercase mb-3">Letzte Berichte</h5>
        <div class="space-y-2 max-h-[150px] overflow-y-auto pr-1 custom-scrollbar">
            @forelse($vehicle->huReports as $report)
                <div class="text-xs flex justify-between items-center bg-white border border-gray-100 p-2 rounded shadow-sm hover:bg-gray-50 group">
                    <div class="flex flex-col">
                        <span class="text-gray-800 font-bold">{{ $report->inspection_date ? $report->inspection_date->format('d.m.Y') : '' }}</span>
                        <span class="text-[9px] text-gray-500 uppercase">{{ $report->organization }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold px-2 py-0.5 rounded-full text-[9px]
                            {{ $report->result == 'pass' ? 'bg-green-100 text-green-800 border border-green-200' :
                              ($report->result == 'note' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' :
                              'bg-red-100 text-red-800 border border-red-200') }}">
                            {{ strtoupper($report->result) }}
                        </span>

                        {{-- EDIT STATT DELETE --}}
                        <button type="button"
                                @click="editHuReport = { id: {{ $report->id }}, date: '{{ $report->inspection_date ? $report->inspection_date->format('Y-m-d') : '' }}', org: '{{ $report->organization }}', result: '{{ $report->result }}' }; showHuEditModal = true"
                                class="opacity-0 group-hover:opacity-100 text-blue-500 hover:text-blue-700 transition" title="Bearbeiten">
                            ✏️
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 bg-gray-50 rounded border border-gray-100">
                    <span class="text-xs text-gray-400 block font-bold">Noch keine Berichte erfasst.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
