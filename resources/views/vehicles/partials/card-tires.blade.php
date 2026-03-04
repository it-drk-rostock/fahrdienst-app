@php
    $latestTire = $vehicle->latest_tire_check;
@endphp

<div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-6 relative" x-data="{ showTireModal: false, showHistory: false }">
    <div class="flex justify-between items-center mb-4 border-b pb-1">
        <h4 class="text-xs font-bold text-gray-500 uppercase">Bereifung & Zustand</h4>
        <div class="flex gap-2">
            <button @click="showHistory = !showHistory" class="text-[10px] text-gray-500 font-bold hover:underline">
                Historie
            </button>
            <button @click="showTireModal = true" class="text-[10px] text-blue-600 font-bold hover:underline bg-blue-50 px-2 py-1 rounded">
                🖊️ Check erfassen
            </button>
        </div>
    </div>

    @if($latestTire)
        <div class="grid grid-cols-2 gap-2 mb-2">
             <div class="bg-gray-50 p-2 rounded text-center border {{ $latestTire->tire_tread_front_left < 3 ? 'border-rose-300 bg-rose-50' : 'border-gray-200' }}">
                 <span class="block text-[9px] text-gray-400 uppercase">VL</span>
                 <span class="font-bold block {{ $latestTire->tire_tread_front_left < 2 ? 'text-rose-600' : '' }}">{{ $latestTire->tire_tread_front_left }} mm</span>
                 <span class="text-[9px] text-gray-500">{{ $latestTire->tire_pressure_front_left }} bar</span>
             </div>
             <div class="bg-gray-50 p-2 rounded text-center border {{ $latestTire->tire_tread_front_right < 3 ? 'border-rose-300 bg-rose-50' : 'border-gray-200' }}">
                 <span class="block text-[9px] text-gray-400 uppercase">VR</span>
                 <span class="font-bold block {{ $latestTire->tire_tread_front_right < 2 ? 'text-rose-600' : '' }}">{{ $latestTire->tire_tread_front_right }} mm</span>
                 <span class="text-[9px] text-gray-500">{{ $latestTire->tire_pressure_front_right }} bar</span>
             </div>
             <div class="bg-gray-50 p-2 rounded text-center border {{ $latestTire->tire_tread_rear_left < 3 ? 'border-rose-300 bg-rose-50' : 'border-gray-200' }}">
                 <span class="block text-[9px] text-gray-400 uppercase">HL</span>
                 <span class="font-bold block {{ $latestTire->tire_tread_rear_left < 2 ? 'text-rose-600' : '' }}">{{ $latestTire->tire_tread_rear_left }} mm</span>
                 <span class="text-[9px] text-gray-500">{{ $latestTire->tire_pressure_rear_left }} bar</span>
             </div>
             <div class="bg-gray-50 p-2 rounded text-center border {{ $latestTire->tire_tread_rear_right < 3 ? 'border-rose-300 bg-rose-50' : 'border-gray-200' }}">
                 <span class="block text-[9px] text-gray-400 uppercase">HR</span>
                 <span class="font-bold block {{ $latestTire->tire_tread_rear_right < 2 ? 'text-rose-600' : '' }}">{{ $latestTire->tire_tread_rear_right }} mm</span>
                 <span class="text-[9px] text-gray-500">{{ $latestTire->tire_pressure_rear_right }} bar</span>
             </div>
        </div>
        <div class="text-[10px] text-gray-400 text-center">
            Letzte Prüfung: {{ $latestTire->checked_at->format('d.m.Y') }} bei {{ number_format($latestTire->mileage,0,',','.') }} km
        </div>
    @else
        <div class="p-4 text-center text-gray-400 text-xs italic bg-gray-50 rounded">Noch keine Reifendaten.</div>
    @endif

    <div x-show="showHistory" class="mt-4 border-t border-gray-100 pt-2" style="display: none;">
        <h5 class="text-[10px] font-bold uppercase text-gray-400 mb-2">Verlauf</h5>
        <div class="max-h-40 overflow-y-auto space-y-1">
            @foreach($vehicle->managerAudits as $audit)
                @if($audit->tire_tread_front_left)
                <div class="text-[10px] flex justify-between p-1 hover:bg-gray-50 rounded border border-gray-100">
                    <span>{{ $audit->checked_at->format('d.m.Y') }}</span>
                    <span class="text-gray-500">{{ $audit->mileage }} km</span>
                    <span class="font-mono">{{ $audit->tire_tread_front_left }} | {{ $audit->tire_tread_front_right }} | {{ $audit->tire_tread_rear_left }} | {{ $audit->tire_tread_rear_right }}</span>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <div x-show="showTireModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm" style="display: none;" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6" @click.away="showTireModal = false">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Reifencheck erfassen</h3>
            <form action="{{ route('vehicles.audit.store', $vehicle) }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div><label class="text-xs font-bold uppercase text-gray-500">Datum</label><input type="date" name="checked_at" value="{{ date('Y-m-d') }}" class="w-full rounded border-gray-300 text-sm"></div>
                    <div><label class="text-xs font-bold uppercase text-gray-500">KM-Stand</label><input type="number" name="mileage" value="{{ $vehicle->managerAudits->first()->mileage ?? '' }}" class="w-full rounded border-gray-300 text-sm"></div>
                </div>

                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3 text-center">Profiltiefe (mm) & Druck (bar)</h4>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div>
                                <div class="text-[10px] font-bold text-center mb-1">Vorne Links</div>
                                <div class="flex gap-1">
                                    <input type="number" step="0.1" name="tire_tread_front_left" placeholder="mm" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                    <input type="number" step="0.1" name="tire_pressure_front_left" placeholder="bar" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-center mb-1">Hinten Links</div>
                                <div class="flex gap-1">
                                    <input type="number" step="0.1" name="tire_tread_rear_left" placeholder="mm" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                    <input type="number" step="0.1" name="tire_pressure_rear_left" placeholder="bar" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <div class="text-[10px] font-bold text-center mb-1">Vorne Rechts</div>
                                <div class="flex gap-1">
                                    <input type="number" step="0.1" name="tire_tread_front_right" placeholder="mm" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                    <input type="number" step="0.1" name="tire_pressure_front_right" placeholder="bar" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-center mb-1">Hinten Rechts</div>
                                <div class="flex gap-1">
                                    <input type="number" step="0.1" name="tire_tread_rear_right" placeholder="mm" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                    <input type="number" step="0.1" name="tire_pressure_rear_right" placeholder="bar" class="w-1/2 rounded border-gray-300 text-sm text-center">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="showTireModal = false" class="px-4 py-2 text-gray-500 text-sm font-bold">Abbrechen</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded font-bold text-sm hover:bg-blue-700">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>
