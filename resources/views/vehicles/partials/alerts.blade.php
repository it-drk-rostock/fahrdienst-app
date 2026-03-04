@if($vehicle->hasOpenDamages() || $vehicle->isDueForService())
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($vehicle->hasOpenDamages())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 shadow-sm rounded-r">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">⚠️</span>
                    <div>
                        <h4 class="font-bold text-red-800 uppercase text-xs">Offene Mängel</h4>
                        <p class="text-xs text-red-700">Es liegen gemeldete Schäden vor. Bitte prüfen.</p>
                    </div>
                </div>
            </div>
        @endif

        @if($vehicle->isDueForService())
            <div class="bg-orange-50 border-l-4 border-orange-500 p-4 shadow-sm rounded-r">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">📅</span>
                    <div>
                        <h4 class="font-bold text-orange-800 uppercase text-xs">Prüfung fällig</h4>
                        <p class="text-xs text-orange-700">HU oder UVV ist bald fällig.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
