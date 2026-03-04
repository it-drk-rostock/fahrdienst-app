<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 {{ $vehicle->is_fully_documented ? 'border-green-500' : 'border-blue-500' }}">
    <div class="flex justify-between items-center mb-2">
        <h3 class="text-sm font-bold text-gray-500 uppercase flex items-center gap-2">
            Daten-Qualität

            {{-- Mängelzähler NEU --}}
            @if($vehicle->damages->where('status', '!=', 'resolved')->count() > 0)
                <span class="bg-red-100 text-red-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-200">
                    {{ $vehicle->damages->where('status', '!=', 'resolved')->count() }} Mängel offen
                </span>
            @endif
        </h3>

        <span class="text-sm font-bold {{ $vehicle->documentation_percentage == 100 ? 'text-green-600' : 'text-blue-600' }}">
            {{ round($vehicle->documentation_percentage) }}%
        </span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-4">
        <div class="h-4 rounded-full transition-all duration-1000 {{ $vehicle->documentation_percentage == 100 ? 'bg-green-500' : 'bg-blue-600' }}"
             style="width: {{ $vehicle->documentation_percentage }}%"></div>
    </div>
</div>
