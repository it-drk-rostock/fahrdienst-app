<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fahrzeug bearbeiten') }}: {{ $vehicle->license_plate }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" class="p-6">
                    @csrf
                    @method('PATCH')
                    @include('vehicles.partials.form', ['areas' => $areas, 'vehicle' => $vehicle])
                    <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-bold text-gray-700 hover:bg-gray-50">ABBRECHEN</a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 rounded-md text-sm font-bold text-white shadow hover:bg-blue-700 transition">ÄNDERUNGEN SPEICHERN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
