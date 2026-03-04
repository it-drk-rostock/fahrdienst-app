@props(['vehicle' => null, 'areas'])

<div class="mb-8 border-b border-gray-100 pb-6">
    <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
        <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">1</span>
        Basisdaten
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kennzeichen *</label>
            <input type="text" name="license_plate" value="{{ old('license_plate', $vehicle->license_plate ?? '') }}" required
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono uppercase">
            <x-input-error :messages="$errors->get('license_plate')" class="mt-1" />
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">VIN</label>
            <input type="text" name="vin" value="{{ old('vin', $vehicle->vin ?? '') }}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 uppercase">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hersteller *</label>
            <input type="text" name="manufacturer" value="{{ old('manufacturer', $vehicle->manufacturer ?? '') }}" required
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Modell *</label>
            <input type="text" name="model" value="{{ old('model', $vehicle->model ?? '') }}" required
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
        </div>
    </div>
</div>

<div class="mb-8 border-b border-gray-100 pb-6">
    <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
        <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">2</span>
        Zuordnung
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kostenstelle</label>
            <select name="cost_center_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
                <option value="">-- Keine Zuordnung --</option>
                @foreach($areas as $area)
                    <optgroup label="{{ $area->name }}">
                        @foreach($area->costCenters as $cc)
                            <option value="{{ $cc->id }}" {{ old('cost_center_id', $vehicle->cost_center_id ?? '') == $cc->id ? 'selected' : '' }}>
                                {{ $cc->name }} ({{ $cc->code }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Erstzulassung</label>
            <input type="date" name="first_registration_date" value="{{ old('first_registration_date', optional($vehicle->first_registration_date ?? null)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
        </div>
    </div>
</div>

<div class="mb-8 border-b border-gray-100 pb-6 bg-gray-50 -mx-6 px-6 py-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
        <span class="bg-white text-gray-600 border border-gray-300 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">3</span>
        Eigenschaften & Nutzung
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 ml-2">

        <div x-data="{
                showBokraft: {{ old('is_bokraft', $vehicle->is_bokraft ?? false) ? 'true' : 'false' }},
                showPrivate: {{ old('private_use_scope', $vehicle->private_use_scope ?? '') ? 'true' : 'false' }}
             }"
             class="col-span-1 md:col-span-3 bg-white p-4 rounded border-l-4 shadow-sm mb-4 transition-all duration-300"
             :class="showBokraft ? 'border-purple-500 bg-purple-50' : (showPrivate ? 'border-green-500' : 'border-gray-300')">

            <div class="flex items-start gap-3 mb-4">
                <div class="flex items-center h-6">
                    <input type="checkbox" id="is_bokraft" name="is_bokraft" value="1" x-model="showBokraft"
                           @change="if(showBokraft) showPrivate = false"
                           class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded cursor-pointer">
                </div>
                <div class="flex-1">
                    <label for="is_bokraft" class="font-bold text-gray-800 cursor-pointer">
                        Einsatz im Personenverkehr (BOKraft)
                    </label>
                    <p class="text-xs text-gray-500 mt-1" x-show="showBokraft">
                        Aktiviert verkürzte HU-Intervalle (12 Monate). Für Taxi, Mietwagen & Bus.
                    </p>

                    <div x-show="showBokraft" x-transition class="mt-3 pt-3 border-t border-purple-200">
                        <label class="block text-xs font-bold text-purple-800 uppercase mb-1">
                            Ordnungsnummer (Nur bei Taxi / Mietwagen)
                        </label>
                        <div class="flex flex-col sm:flex-row gap-2 items-center">
                            <input type="text" name="concession_number"
                                   value="{{ old('concession_number', $vehicle->concession_number ?? '') }}"
                                   placeholder="z.B. 1234 (Leer lassen bei Schülerverkehr)"
                                   class="w-full sm:w-1/2 rounded-md border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                            <span class="text-xs text-gray-400 italic">Optional</span>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="!showBokraft" x-transition class="border-t border-gray-100 pt-4 mt-2">

                <div class="flex items-center gap-3">
                    <div class="flex items-center h-5">
                        <input type="checkbox" id="allow_private" x-model="showPrivate"
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded cursor-pointer">
                    </div>
                    <label for="allow_private" class="font-bold text-sm text-gray-700 cursor-pointer">
                        Privatnutzung gestattet?
                    </label>
                </div>

                <div x-show="showPrivate" x-transition class="mt-3 ml-7">
                    <label class="block text-xs font-bold text-green-700 uppercase mb-1">
                        Tankvalidierung (Erlaubter Radius)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2 items-center">
                        <select name="private_use_scope" class="w-full sm:w-1/2 rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                            <option value="">-- Bitte wählen --</option>
                            <option value="ort" {{ old('private_use_scope', $vehicle->private_use_scope ?? '') == 'ort' ? 'selected' : '' }}>Nur am Dienstort</option>
                            <option value="bundesland" {{ old('private_use_scope', $vehicle->private_use_scope ?? '') == 'bundesland' ? 'selected' : '' }}>Im Bundesland</option>
                            <option value="deutschland" {{ old('private_use_scope', $vehicle->private_use_scope ?? '') == 'deutschland' ? 'selected' : '' }}>Deutschlandweit</option>
                            <option value="international" {{ old('private_use_scope', $vehicle->private_use_scope ?? '') == 'international' ? 'selected' : '' }}>International (EU)</option>
                        </select>
                        <span class="text-xs text-gray-400 italic">Definiert gültige Tank-Standorte</span>
                    </div>
                </div>
                 <div x-show="!showPrivate" class="mt-2 ml-7 text-xs text-gray-400 italic">
                    Fahrzeug ist als reiner Dienstwagen deklariert (Rein geschäftlich).
                </div>
            </div>

        </div>

        <label class="flex items-center p-3 bg-white rounded border border-gray-200 shadow-sm hover:border-blue-300 transition cursor-pointer">
            <input type="checkbox" name="is_electric" value="1" {{ old('is_electric', $vehicle->is_electric ?? false) ? 'checked' : '' }} class="rounded text-blue-600 h-4 w-4">
            <span class="ml-2 text-sm font-bold text-gray-700">Elektro-Fahrzeug</span>
        </label>

        <label class="flex items-center p-3 bg-white rounded border border-gray-200 shadow-sm hover:border-blue-300 transition cursor-pointer">
            <input type="checkbox" name="has_lift" value="1" {{ old('has_lift', $vehicle->has_lift ?? false) ? 'checked' : '' }} class="rounded text-blue-600 h-4 w-4">
            <span class="ml-2 text-sm font-bold text-gray-700">Hat Lift / Rampe</span>
        </label>

        <label class="flex items-center p-3 bg-white rounded border border-gray-200 shadow-sm hover:border-blue-300 transition cursor-pointer">
            <input type="checkbox" name="has_chair" value="1" {{ old('has_chair', $vehicle->has_chair ?? false) ? 'checked' : '' }} class="rounded text-blue-600 h-4 w-4">
            <span class="ml-2 text-sm font-bold text-gray-700">Hat Tragestuhl</span>
        </label>

        <label class="flex items-center p-3 bg-white rounded border border-gray-200 shadow-sm hover:border-blue-300 transition cursor-pointer">
            <input type="checkbox" name="has_smartfloor" value="1" {{ old('has_smartfloor', $vehicle->has_smartfloor ?? false) ? 'checked' : '' }} class="rounded text-blue-600 h-4 w-4">
            <span class="ml-2 text-sm font-bold text-gray-700">Hat Smartfloor</span>
        </label>

        <label class="flex items-center p-3 bg-white rounded border border-gray-200 shadow-sm hover:border-blue-300 transition cursor-pointer">
            <input type="checkbox" name="has_home_cable" value="1" {{ old('has_home_cable', $vehicle->has_home_cable ?? false) ? 'checked' : '' }} class="rounded text-blue-600 h-4 w-4">
            <span class="ml-2 text-sm font-bold text-gray-700">Hat Schuko-Kabel</span>
        </label>
    </div>
</div>

<div class="mb-6">
    <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
        <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">4</span>
        Prüfungs-Termine
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nächste HU</label>
            <input type="date" name="next_hu_date" value="{{ old('next_hu_date', optional($vehicle->next_hu_date ?? null)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nächste UVV</label>
            <input type="date" name="next_uvv_date" value="{{ old('next_uvv_date', optional($vehicle->next_uvv_date ?? null)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500">
        </div>
        <div>
            <label class="block text-xs font-bold text-purple-600 uppercase mb-1">BOKraft (Termin)</label>
            <input type="date" name="next_bokraft_date" value="{{ old('next_bokraft_date', optional($vehicle->next_bokraft_date ?? null)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-purple-200 bg-purple-50 shadow-sm focus:border-purple-500">
        </div>
    </div>

    <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Lift UVV</label>
            <input type="date" name="next_lift_uvv_date" value="{{ old('next_lift_uvv_date', optional($vehicle->next_lift_uvv_date ?? null)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-200 text-gray-600 shadow-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tragestuhl Check</label>
            <input type="date" name="next_chair_uvv_date" value="{{ old('next_chair_uvv_date', optional($vehicle->next_chair_uvv_date ?? null)->format('Y-m-d')) }}"
                   class="w-full rounded-md border-gray-200 text-gray-600 shadow-sm">
        </div>
    </div>
</div>
