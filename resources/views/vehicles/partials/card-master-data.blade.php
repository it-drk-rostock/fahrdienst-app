<div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100 h-full">
    <h4 class="text-xs font-bold text-blue-600 uppercase border-b pb-2 mb-4">Fahrzeug & Standort</h4>

    <dl class="space-y-3 text-sm">
        <div class="flex justify-between">
            <dt class="text-gray-500">Hersteller:</dt>
            <dd class="font-bold">{{ $vehicle->manufacturer }}</dd>
        </div>
        <div class="flex justify-between">
            <dt class="text-gray-500">Modell:</dt>
            <dd class="font-bold">{{ $vehicle->model }}</dd>
        </div>

        <div class="flex justify-between">
            <dt class="text-gray-500">Erstzulassung:</dt>
            <dd>{{ $vehicle->first_registration_date ? $vehicle->first_registration_date->format('d.m.Y') : '-' }}</dd>
        </div>

        <div class="border-t border-dashed border-gray-200 pt-2 mt-2">
            <dt class="text-[10px] uppercase text-gray-400 font-bold mb-1">Nutzungsart</dt>
            <dd>
                @if($vehicle->is_bokraft)
                    @if(!empty($vehicle->concession_number))
                        <div class="flex flex-col items-start gap-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-purple-100 text-purple-800 text-xs font-bold border border-purple-200">
                                🚕 PTW (Mietwagen/Taxi)
                            </span>
                            <span class="text-xs font-mono text-purple-600 pl-1">
                                Nr: {{ $vehicle->concession_number }}
                            </span>
                        </div>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs font-bold border border-blue-200">
                            🚌 Personenbeförderung
                        </span>
                    @endif
                @else
                    <div class="flex flex-col items-start gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs font-bold border border-gray-200">
                            @if(!empty($vehicle->private_use_scope))
                                🚗 Dienstwagen mit Privater Nutzung
                            @else
                                🚗 Dienstwagen
                            @endif
                        </span>

                        @if(!empty($vehicle->private_use_scope))
                            <div class="flex items-center gap-1 text-xs mt-1">
                                <span class="text-gray-400">Tank-Radius:</span>
                                <span class="font-bold text-gray-700 uppercase">
                                    @switch($vehicle->private_use_scope)
                                        @case('ort') Nur am Ort @break
                                        @case('bundesland') Bundesland @break
                                        @case('deutschland') Deutschland @break
                                        @case('international') International @break
                                        @default {{ $vehicle->private_use_scope }}
                                    @endswitch
                                </span>
                            </div>
                        @else
                            <span class="text-[10px] text-gray-400 italic pl-1">Rein geschäftlich</span>
                        @endif
                    </div>
                @endif
            </dd>
        </div>

        <div class="border-t pt-2 mt-2">
            <dt class="text-[10px] uppercase text-gray-400 font-bold mb-1">Bereich / Kostenstelle</dt>
            @if($vehicle->costCenter)
                <dd class="font-bold text-gray-800">{{ $vehicle->costCenter->code }} - {{ $vehicle->costCenter->name }}</dd>
                <dd class="text-xs text-gray-500">{{ $vehicle->costCenter->area->name ?? 'Keine Area' }}</dd>
            @else
                <dd class="text-red-500 font-bold text-xs bg-red-50 px-2 py-1 rounded inline-block">NICHT ZUGEORDNET</dd>
            @endif
        </div>
    </dl>
</div>
