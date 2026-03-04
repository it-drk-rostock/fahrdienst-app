<div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <div class="text-center md:text-left">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $vehicle->license_plate }}
            <span class="ml-2 text-sm text-gray-500 font-normal block sm:inline">{{ $vehicle->manufacturer }} {{ $vehicle->model }}</span>
        </h2>
        <div class="text-xs text-gray-400 mt-1">VIN: {{ $vehicle->vin ?? '---' }}</div>
    </div>

    <div class="flex flex-wrap justify-center gap-2 w-full md:w-auto">

        <form action="{{ route('vehicles.toggle-status', $vehicle) }}" method="POST">
            @csrf
            <button type="submit"
                    class="inline-flex items-center px-3 py-2 border rounded-md font-bold text-xs uppercase tracking-widest shadow-sm transition whitespace-nowrap
                    {{ $vehicle->is_fully_documented
                        ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                        : 'bg-gray-50 text-gray-600 border-gray-300 hover:bg-gray-100' }}">
                @if($vehicle->is_fully_documented)
                    ✅ Doku Fertig
                @else
                    ⚪ Doku Offen
                @endif
            </button>
        </form>

        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 whitespace-nowrap">
            Zurück
        </a>

        <a href="{{ route('vehicles.edit', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 shadow-sm whitespace-nowrap">
            ✏️ Bearbeiten
        </a>
    </div>
</div>
