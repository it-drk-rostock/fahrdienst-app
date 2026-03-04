<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $vehicle->license_plate }}
                    <span class="ml-2 text-sm text-gray-500 font-normal">{{ $vehicle->manufacturer }} {{ $vehicle->model }}</span>
                </h2>
                <div class="text-xs text-gray-400 mt-1">VIN: {{ $vehicle->vin ?? '---' }}</div>
            </div>

            <div class="flex items-center gap-2">
                <form action="{{ route('vehicles.toggle-status', $vehicle) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-3 py-2 border rounded-md font-bold text-xs uppercase tracking-widest shadow-sm transition
                            {{ $vehicle->is_fully_documented
                                ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                                : 'bg-gray-50 text-gray-600 border-gray-300 hover:bg-gray-100' }}">
                        @if($vehicle->is_fully_documented)
                            ✅ Doku fertig
                        @else
                            ⚪ Doku offen
                        @endif
                    </button>
                </form>

                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    Zurück
                </a>

                <a href="{{ route('vehicles.edit', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 shadow-sm">
                    ✏️ Bearbeiten
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showHuModal: false, showAuditModal: false, showDamageModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @include('vehicles.partials.progress')

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                    <p class="font-bold">Erfolg</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if(session('warning'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded shadow-sm">
                    <p class="font-bold">Hinweis</p>
                    <p>{{ session('warning') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start h-full">

                <div class="flex flex-col h-full">
                    @include('vehicles.partials.card-master-data')
                </div>

                <div class="flex flex-col h-full">
                    @include('vehicles.partials.card-hu')
                </div>

                <div class="flex flex-col gap-6 h-full">

                    <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                        <div class="flex justify-between items-center border-b pb-2 mb-4">
                            <h4 class="text-xs font-bold text-red-600 uppercase">Aktuelle Mängel</h4>
                            <button @click="showDamageModal = true" class="text-[10px] bg-red-50 text-red-600 px-2 py-1 rounded hover:bg-red-100 border border-red-200 font-bold">
                                + Mangel melden
                            </button>
                        </div>

                        <div class="space-y-3 max-h-[250px] overflow-y-auto">
                            @forelse($vehicle->damages->where('status', '!=', 'resolved') as $damage)
                                <div class="bg-white border {{ str_contains($damage->severity_color ?? '', 'bg-red') ? 'border-red-200' : 'border-gray-200' }} p-3 rounded shadow-sm relative group">
                                    <div class="flex justify-between items-start">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $damage->severity_color ?? 'bg-gray-100' }}">
                                            {{ ucfirst($damage->severity) }}
                                        </span>
                                        <div class="text-right">
                                            <span class="block text-[10px] text-gray-400">{{ $damage->created_at->format('d.m.Y') }}</span>
                                            <span class="block text-[9px] text-gray-300">{{ $damage->reporter_name }}</span>
                                        </div>
                                    </div>
                                    <h5 class="font-bold text-sm mt-2 text-gray-800">{{ $damage->title }}</h5>
                                    <p class="text-xs text-gray-600 mt-1">{{ $damage->description }}</p>

                                    @if($damage->images && count($damage->images) > 0)
                                        <div class="flex gap-1 mt-2">
                                            @foreach($damage->images as $img)
                                                <a href="{{ asset('storage/' . $img) }}" target="_blank" class="block w-8 h-8 rounded overflow-hidden border border-gray-200">
                                                    <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <form action="{{ route('damages.resolve', $damage->id) }}" method="POST" class="mt-2 text-right border-t border-gray-50 pt-2">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-green-600 font-bold hover:underline w-full text-right">
                                            ✅ Als erledigt markieren
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="text-center py-6 text-green-600 bg-green-50 rounded border border-green-100">
                                    <span class="text-2xl">✨</span>
                                    <p class="text-sm font-bold mt-1">Keine offenen Mängel</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex-1">
                        @include('vehicles.partials.card-tires')
                    </div>
                </div>

            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-6">
                @include('vehicles.partials.card-maintenance')
            </div>

            <div class="flex justify-end gap-4 mt-6 print:hidden">
                <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-xs font-bold uppercase hover:bg-gray-50 shadow-sm flex items-center gap-2">
                    🖨️ Akte drucken
                </button>
            </div>

        </div>

        <div x-show="showHuModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]" @click.away="showHuModal = false">
                <h3 class="text-lg font-bold mb-4">Neuen HU-Bericht erfassen</h3>

                <form action="{{ route('vehicles.hu.store', $vehicle) }}" method="POST" x-data="{
                    defects: [],
                    addDefect() {
                        this.defects.push({ title: '', type: 'minor' });
                    },
                    removeDefect(index) {
                        this.defects.splice(index, 1);
                    }
                }">
                    @csrf
                    <div class="space-y-6">

                        <div class="bg-gray-50 p-4 rounded border border-gray-200 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Prüfdatum</label>
                                <input type="date" name="inspection_date" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Organisation</label>
                                <input type="text" name="organization" placeholder="TÜV/DEKRA" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Ergebnis</label>
                                <select name="result" required class="w-full rounded border-gray-300 text-sm">
                                    <option value="pass">✅ Bestanden (Ohne Mängel)</option>
                                    <option value="minor">🆗 Bestanden (Geringe Mängel)</option> <option value="major">⚠️ Erhebliche Mängel (Nachprüfung)</option> <option value="unsafe">🛑 Verkehrsunsicher</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Berichtsnummer</label>
                                <input type="text" name="report_number" class="w-full rounded border-gray-300 text-sm">
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-xs font-bold uppercase text-gray-700">Festgestellte Mängel / Hinweise</label>
                                <button type="button" @click="addDefect()" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded border border-blue-200 hover:bg-blue-100 font-bold">
                                    + Position hinzufügen
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(defect, index) in defects" :key="index">
                                    <div class="flex gap-2 items-start">
                                        <select :name="'defects['+index+'][type]'" x-model="defect.type" class="w-1/3 text-xs rounded border-gray-300">
                                            <option value="note">ℹ️ Hinweis</option>
                                            <option value="minor">⚠️ GM (Gering)</option>
                                            <option value="major">🛑 EM (Erheblich)</option>
                                        </select>

                                        <input type="text" :name="'defects['+index+'][title]'" x-model="defect.title" placeholder="Beschreibung (z.B. Licht zu hoch)" class="w-full text-xs rounded border-gray-300" required>

                                        <button type="button" @click="removeDefect(index)" class="text-red-500 hover:text-red-700 px-2">
                                            🗑️
                                        </button>
                                    </div>
                                </template>

                                <div x-show="defects.length === 0" class="text-center py-4 text-xs text-gray-400 italic border border-dashed border-gray-300 rounded">
                                    Keine Mängel eingetragen.
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">* Diese Einträge werden automatisch in die Mängel-Akte übernommen.</p>
                        </div>

                        <div class="bg-blue-50 p-3 rounded border border-blue-100">
                            <label class="block text-xs font-bold uppercase text-blue-800 mb-2">Gleichzeitig aktualisieren:</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="update_uvv" value="1" checked class="rounded text-blue-600 h-4 w-4">
                                    <span class="ml-2 text-sm text-gray-700">UVV Prüfung (+1 Jahr)</span>
                                </label>
                                @if($vehicle->is_bokraft)
                                <label class="flex items-center">
                                    <input type="checkbox" name="update_bokraft" value="1" checked class="rounded text-purple-600 h-4 w-4">
                                    <span class="ml-2 text-sm text-gray-700">BOKraft (+1 Jahr)</span>
                                </label>
                                @endif
                                @if($vehicle->has_lift)
                                <label class="flex items-center">
                                    <input type="checkbox" name="update_lift" value="1" checked class="rounded text-blue-600 h-4 w-4">
                                    <span class="ml-2 text-sm text-gray-700">Lift / Rampe (+1 Jahr)</span>
                                </label>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-gray-100">
                        <button type="button" @click="showHuModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Abbrechen</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700">Bericht Speichern</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showAuditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6" @click.away="showAuditModal = false">
                <h3 class="text-lg font-bold mb-4">Kilometerstand & Reifen prüfen</h3>
                <form action="{{ route('vehicles.audit.store', $vehicle) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Datum</label>
                                <input type="date" name="checked_at" value="{{ date('Y-m-d') }}" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Aktueller KM-Stand</label>
                                <input type="number" name="mileage" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded border border-gray-200">
                            <h4 class="text-xs font-bold uppercase text-gray-500 mb-3 text-center">Reifenwerte (Druck / Profil)</h4>

                            <div class="flex justify-between items-center mb-4">
                                <div class="w-1/2 pr-2">
                                    <label class="text-[10px] uppercase font-bold text-gray-400 block text-center mb-1">Vorne Links</label>
                                    <div class="flex gap-1">
                                        <input type="number" step="0.1" name="tire_pressure_front_left" placeholder="Bar" class="w-1/2 text-xs rounded border-gray-300">
                                        <input type="number" step="0.1" name="tire_tread_front_left" placeholder="mm" class="w-1/2 text-xs rounded border-gray-300">
                                    </div>
                                </div>
                                <div class="w-1/2 pl-2">
                                    <label class="text-[10px] uppercase font-bold text-gray-400 block text-center mb-1">Vorne Rechts</label>
                                    <div class="flex gap-1">
                                        <input type="number" step="0.1" name="tire_pressure_front_right" placeholder="Bar" class="w-1/2 text-xs rounded border-gray-300">
                                        <input type="number" step="0.1" name="tire_tread_front_right" placeholder="mm" class="w-1/2 text-xs rounded border-gray-300">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="w-1/2 pr-2">
                                    <label class="text-[10px] uppercase font-bold text-gray-400 block text-center mb-1">Hinten Links</label>
                                    <div class="flex gap-1">
                                        <input type="number" step="0.1" name="tire_pressure_rear_left" placeholder="Bar" class="w-1/2 text-xs rounded border-gray-300">
                                        <input type="number" step="0.1" name="tire_tread_rear_left" placeholder="mm" class="w-1/2 text-xs rounded border-gray-300">
                                    </div>
                                </div>
                                <div class="w-1/2 pl-2">
                                    <label class="text-[10px] uppercase font-bold text-gray-400 block text-center mb-1">Hinten Rechts</label>
                                    <div class="flex gap-1">
                                        <input type="number" step="0.1" name="tire_pressure_rear_right" placeholder="Bar" class="w-1/2 text-xs rounded border-gray-300">
                                        <input type="number" step="0.1" name="tire_tread_rear_right" placeholder="mm" class="w-1/2 text-xs rounded border-gray-300">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Notizen</label>
                            <textarea name="notes" rows="2" class="w-full rounded border-gray-300 text-sm"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="showAuditModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Abbrechen</button>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white font-bold rounded hover:bg-gray-700">Speichern</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showDamageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="showDamageModal = false">
                <h3 class="text-lg font-bold mb-4 text-red-600">Neuen Mangel melden</h3>
                <form action="{{ route('vehicles.damage.store', $vehicle) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Was ist kaputt? (Titel)</label>
                            <input type="text" name="title" placeholder="z.B. Reifen vorne links platt" required class="w-full rounded border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Dringlichkeit</label>
                            <select name="severity" required class="w-full rounded border-gray-300 text-sm">
                                <option value="low">Niedrig (Schönheitsfehler)</option>
                                <option value="medium" selected>Mittel (Bald beheben)</option>
                                <option value="high">Hoch (Sofort beheben)</option>
                                <option value="critical">KRITISCH (Fahrzeug stehen lassen!)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Beschreibung</label>
                            <textarea name="description" rows="3" class="w-full rounded border-gray-300 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Fotos (Optional)</label>
                            <input type="file" name="images[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="showDamageModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Abbrechen</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white font-bold rounded hover:bg-red-700">Mangel melden</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
