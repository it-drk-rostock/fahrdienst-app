<div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 h-full flex flex-col">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <h3 class="text-xs font-bold text-gray-700 uppercase">🔧 Wartung & Service</h3>

        @if($vehicle->maintenanceTemplate)
            <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded border border-blue-200">
                Plan: {{ $vehicle->maintenanceTemplate->name }}
            </span>
        @else
            <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded border border-gray-200">
                Kein Plan zugewiesen
            </span>
        @endif
    </div>

    <div class="p-6 flex-1 flex flex-col gap-4">

        <div class="flex justify-between items-center text-xs text-gray-600 mb-2">
            <span>Kilometerstand aktuell:</span>
            <span class="font-bold font-mono text-sm">{{ number_format($vehicle->managerAudits->first()->mileage ?? 0, 0, ',', '.') }} km</span>
        </div>

        <div class="flex-1 flex flex-col">
            <div class="flex-1 overflow-y-auto max-h-[200px] space-y-2 border rounded p-2 bg-gray-50 mb-4">
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Checkliste (Information)</p>

                @if($vehicle->maintenanceTemplate && $vehicle->maintenanceTemplate->items)
                    @foreach($vehicle->maintenanceTemplate->items as $item)
                        <div class="flex items-center p-2 bg-white border border-gray-200 rounded">
                            <span class="ml-2">
                                <span class="block text-xs font-bold text-gray-700">{{ $item->title }}</span>
                                <span class="block text-[9px] text-gray-500">Alle {{ $item->interval_months }} Monate / {{ number_format($item->interval_km,0,',','.') }} km</span>
                            </span>
                        </div>
                    @endforeach
                @endif

                <div class="flex items-center p-2 bg-white border border-gray-200 rounded">
                    <span class="ml-2 text-xs font-bold text-gray-700">Ölwechsel (Obligatorisch)</span>
                </div>
                <div class="flex items-center p-2 bg-white border border-gray-200 rounded">
                    <span class="ml-2 text-xs font-bold text-gray-700">Zahnriemen</span>
                </div>
            </div>

            <a href="{{ route('calendar.index', ['action' => 'create', 'vehicle_id' => $vehicle->id, 'license_plate' => $vehicle->license_plate]) }}" class="w-full bg-blue-600 text-white font-bold text-xs uppercase py-2.5 rounded shadow hover:bg-blue-700 flex justify-center items-center gap-2 transition">
                📅 Im Kalender planen
            </a>
        </div>
    </div>
</div>
