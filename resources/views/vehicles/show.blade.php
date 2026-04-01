<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">

            <div class="flex flex-col md:flex-row items-center gap-6 w-full">
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="transform -rotate-90 w-16 h-16">
                        <circle cx="32" cy="32" r="26" stroke="currentColor" stroke-width="4" fill="transparent" class="text-gray-100" />
                        <circle cx="32" cy="32" r="26" stroke="currentColor" stroke-width="4" fill="transparent"
                                stroke-dasharray="163"
                                stroke-dashoffset="{{ 163 - (163 * $vehicle->documentation_percentage / 100) }}"
                                class="{{ $vehicle->documentation_percentage == 100 ? 'text-green-500' : ($vehicle->documentation_percentage > 70 ? 'text-blue-500' : 'text-orange-500') }}" />
                    </svg>
                    <div class="absolute flex flex-col items-center">
                        <span class="text-xs font-bold text-gray-700">{{ $vehicle->documentation_percentage }}%</span>
                    </div>
                </div>

                <div class="flex-1 grid grid-cols-2 md:grid-cols-2 xl:grid-cols-4 gap-4 text-sm md:border-l border-gray-200 md:pl-6 w-full text-center md:text-left">
                    <div>
                        <h2 class="font-bold text-2xl text-gray-800 leading-none">{{ $vehicle->license_plate }}</h2>
                        <span class="text-xs text-gray-400">{{ $vehicle->manufacturer }} {{ $vehicle->model }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase text-gray-400 font-bold">FIN / VIN</span>
                        <span class="font-mono text-gray-700 text-xs md:text-sm truncate">{{ $vehicle->vin ?? '---' }}</span>
                    </div>
                    <div>
                      <div>
                      <span class="block text-[10px] uppercase text-gray-400 font-bold mb-0.5">Kostenstelle</span>
                      @if($vehicle->costCenter)
                          <div class="flex flex-col leading-tight">
                              <span class="font-bold text-gray-800 text-sm">
                                  {{ $vehicle->costCenter->code }}
                                  @if($vehicle->costCenter->short_name)
                                      <span class="text-blue-600 ml-1">[{{ $vehicle->costCenter->short_name }}]</span>
                                  @endif
                              </span>
                              <span class="text-[10px] text-gray-500 font-bold truncate" title="{{ $vehicle->costCenter->name }}">
                                  {{ $vehicle->costCenter->name }}
                              </span>

                              @if($vehicle->costCenter->contact_name || $vehicle->costCenter->telephone || $vehicle->costCenter->contact_email)
                                  <div class="mt-1.5 border-t border-gray-100 pt-1 space-y-0.5">
                                      @if($vehicle->costCenter->contact_name)
                                          <span class="block text-[10px] text-gray-500">👤 {{ $vehicle->costCenter->contact_name }}</span>
                                      @endif
                                      @if($vehicle->costCenter->telephone)
                                          <span class="block text-[10px] text-gray-500">📞 {{ $vehicle->costCenter->telephone }}</span>
                                      @endif
                                      @if($vehicle->costCenter->contact_email)
                                          <a href="mailto:{{ $vehicle->costCenter->contact_email }}" class="block text-[10px] text-blue-500 hover:underline truncate">
                                              ✉️ {{ $vehicle->costCenter->contact_email }}
                                          </a>
                                      @endif
                                  </div>
                              @endif
                          </div>
                      @else
                          <span class="font-bold text-gray-700">-</span>
                      @endif
                  </div>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase text-gray-400 font-bold">Kilometerstand</span>
                        @php
                            $km = $vehicle->managerAudits->first()->mileage ?? 0;
                        @endphp
                        <span class="font-mono text-gray-700">{{ number_format($km, 0, ',', '.') }} km</span>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 flex-shrink-0 w-full md:w-auto justify-center md:justify-end mt-4 md:mt-0">
                <a href="{{ route('calendar.index', ['action' => 'create', 'vehicle_id' => $vehicle->id, 'license_plate' => $vehicle->license_plate]) }}" class="inline-flex items-center px-4 py-3 bg-blue-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-lg transition transform hover:-translate-y-0.5">
                    📅 Kalender Planung
                </a>
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="inline-flex items-center px-4 py-3 bg-white border border-gray-300 rounded-md font-bold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    ✏️ Edit
                </a>
            </div>

        </div>
    </x-slot>

    {{-- HAUPTBEREICH --}}
    <div class="py-8 w-full px-4 mx-auto space-y-6" x-data="{ showHuModal: false, showHuEditModal: false, showAuditModal: false, showDamageModal: false, editHuReport: {} }">

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-start h-full">

            {{-- SPALTE 1 --}}
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 flex flex-col h-full">
                @include('vehicles.partials.card-master-data', ['vehicle' => $vehicle])
                <div class="mt-6 border-t border-gray-100 pt-6 px-6 pb-6">
                    @include('vehicles.partials.card-maintenance', ['vehicle' => $vehicle])
                </div>
            </div>

            {{-- SPALTE 2 --}}
            <div class="space-y-6 flex flex-col h-full">
                @include('vehicles.partials.card-hu', ['vehicle' => $vehicle])
                @include('vehicles.partials.card-tires', ['vehicle' => $vehicle])
            </div>

            {{-- SPALTE 3: MÄNGEL & HISTORIE --}}
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 h-full flex flex-col md:col-span-1 xl:col-span-1"
                 x-data="{ showCreateModal: false, showEditModal: false, showImage: false, previewImage: '', activeDamage: { id: null, title: '', description: '', severity: '', status: '', type: '', images: [] } }">

                {{-- OFFENE MÄNGEL KOPF --}}
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center gap-4">
                    <h3 class="font-bold text-red-600 whitespace-nowrap">Aktuelle Mängel</h3>
                    <button @click="showCreateModal = true" class="px-3 py-1 bg-red-50 text-red-600 rounded shadow-sm font-bold text-xs uppercase hover:bg-red-100 border border-red-200 transition whitespace-nowrap flex-shrink-0">
                        + Melden
                    </button>
                </div>

                {{-- BILDER PREVIEW --}}
                <div x-show="showImage" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-sm px-4" style="display: none;" x-cloak @click.away="showImage = false">
                    <div class="relative max-w-5xl max-h-[90vh] w-full flex justify-center">
                        <img :src="previewImage" class="max-w-full max-h-[85vh] rounded shadow-2xl border-2 border-white/20 object-contain">
                        <button @click="showImage = false" class="absolute -top-12 right-0 md:-top-10 md:-right-10 text-white hover:text-gray-300 font-bold text-xl flex items-center gap-2 p-2">Schließen ✕</button>
                    </div>
                </div>

                {{-- OFFENE MÄNGEL LISTE --}}
                <div class="divide-y divide-gray-100 overflow-y-auto max-h-[400px]">
                    @forelse($vehicle->damages->where('status', '!=', 'resolved') as $damage)
                        <div class="p-4 hover:bg-gray-50 transition flex items-start gap-4 group">
                            <div class="mt-1 flex-shrink-0 cursor-help" title="Status: {{ $damage->status }}">
                                @if($damage->severity == 'critical')
                                    <div class="w-8 h-8 rounded bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200 font-bold shadow-sm animate-pulse">⚡</div>
                                @elseif($damage->severity == 'high')
                                    <div class="w-8 h-8 rounded bg-red-50 text-red-600 flex items-center justify-center border border-red-100 font-bold shadow-sm">🛑</div>
                                @elseif($damage->severity == 'medium')
                                    <div class="w-8 h-8 rounded bg-orange-50 text-orange-600 flex items-center justify-center border border-orange-200 font-bold shadow-sm">⚠️</div>
                                @else
                                    <div class="w-8 h-8 rounded bg-gray-100 text-gray-500 flex items-center justify-center border border-gray-200 font-bold shadow-sm">ℹ️</div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-gray-800 text-sm truncate">{{ $damage->title }}</h4>
                                    <span class="text-[10px] text-gray-400 font-mono">{{ $damage->created_at->format('d.m.') }}</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">{{ $damage->description }}</p>

                                @if(!empty($damage->images) && is_array($damage->images))
                                    <div class="flex gap-2 mt-2 overflow-x-auto pb-1 no-scrollbar">
                                        @foreach($damage->images as $img)
                                            <div class="relative w-10 h-10 flex-shrink-0 rounded border border-gray-200 overflow-hidden cursor-pointer hover:ring-2 hover:ring-blue-400 transition" @click.stop="previewImage = '{{ asset('storage/' . $img) }}'; showImage = true"><img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover"></div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-2 flex items-center justify-between">
                                    <div>{!! $vehicle->getDamageStatusHtml($damage) !!}</div>
                                    <div class="flex gap-2 transition opacity-100 md:opacity-0 md:group-hover:opacity-100">
                                        <button type="button" @click="activeDamage = { id: {{ $damage->id }}, title: '{{ addslashes($damage->title) }}', description: '{{ addslashes(str_replace(["\r", "\n"], ' ', $damage->description)) }}', severity: '{{ $damage->severity }}', status: '{{ $damage->status }}', type: '{{ $damage->damage_type ?? 'wear' }}', images: {{ json_encode($damage->images ?? []) }} }; showEditModal = true" class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded border border-blue-100 hover:bg-blue-100">✏️ Edit</button>

                                        @if(!$damage->workshop_appointment_id)
                                            <a href="{{ route('calendar.index', ['action' => 'create', 'vehicle_id' => $vehicle->id, 'license_plate' => $vehicle->license_plate, 'preselect_damage' => $damage->id]) }}" class="text-xs font-bold text-gray-600 bg-gray-50 px-2 py-1 rounded border border-gray-200 hover:bg-gray-100 transition shadow-sm">
                                                📅 Dispo
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center"><span class="text-2xl block mb-2">✨</span><span class="text-green-600 font-bold text-sm">Keine offenen Mängel.</span></div>
                    @endforelse
                </div>

                {{-- ERLEDIGTE MÄNGEL GRUPPIERT --}}
                @php
                    $resolvedDamages = $vehicle->damages->where('status', 'resolved');
                    $groupedDamages = $resolvedDamages->groupBy('workshop_appointment_id');
                @endphp

                <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 flex justify-between items-center mt-auto">
                    <h4 class="text-xs font-bold text-gray-500 uppercase">Behobene Schäden (Historie)</h4>
                    <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $resolvedDamages->count() }}</span>
                </div>

                <div class="overflow-y-auto max-h-[300px] bg-white p-4 space-y-4 border-t border-gray-100">
                    @forelse($groupedDamages as $appId => $damagesGroup)
                        @php $appointment = $damagesGroup->first()->workshopAppointment; @endphp

                        <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                            <div class="bg-gray-100 p-2 flex justify-between items-center border-b border-gray-200">
                                <div>
                                    <span class="font-bold text-xs text-gray-800">{{ $appointment ? $appointment->workshop_name : 'Manuell / Direkt behoben' }}</span>
                                    <span class="block text-[9px] text-gray-500 uppercase">{{ $appointment && $appointment->actual_end_time ? 'Fertig am: ' . $appointment->actual_end_time->format('d.m.Y') : 'Behoben: ' . $damagesGroup->first()->resolved_at->format('d.m.Y') }}</span>
                                </div>
                                @if($appointment)
                                    <div class="text-right text-[10px]">
                                        <div class="text-gray-500">RNr: <span class="font-mono text-gray-700 font-bold">{{ $appointment->invoice_number ?? '___' }}</span></div>
                                        <div class="text-gray-500">Summe: <span class="font-bold text-gray-800">{{ $appointment->invoice_amount ? number_format($appointment->invoice_amount, 2, ',', '.') . ' €' : '___ €' }}</span></div>
                                    </div>
                                @endif
                            </div>
                            <div class="p-2 space-y-1 bg-white">
                                @foreach($damagesGroup as $damage)
                                    <div class="text-xs text-gray-700 flex items-start gap-2">
                                        <span class="text-gray-400 mt-0.5">•</span>
                                        <div>
                                            <span class="font-bold">{{ $damage->title }}</span>
                                            @if($damage->description)<span class="block text-[10px] text-gray-500">{{ Str::limit($damage->description, 50) }}</span>@endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 text-xs italic py-4">Noch keine Historie vorhanden.</div>
                    @endforelse
                </div>

                {{-- MODAL CREATE DAMAGE (Blade Komponente) --}}
                <x-modal-create-damage :vehicle="$vehicle" />

                {{-- MODAL EDIT DAMAGE --}}
                <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm px-4" style="display: none;" x-cloak>
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto" @click.away="showEditModal = false">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg font-bold text-gray-800">Mangel bearbeiten</h3>
                            <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                        </div>
                        <form :action="'{{ url('damages') }}/' + activeDamage.id" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="space-y-4">
                                <div><label class="block text-xs font-bold text-gray-500 uppercase">Titel</label><input type="text" name="title" x-model="activeDamage.title" required class="w-full rounded border-gray-300 text-sm font-bold"></div>
                                <div><label class="block text-xs font-bold text-gray-500 uppercase">Beschreibung</label><textarea name="description" x-model="activeDamage.description" rows="3" class="w-full rounded border-gray-300 text-sm"></textarea></div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div><label class="block text-xs font-bold text-gray-500 uppercase">Schweregrad</label><select name="severity" x-model="activeDamage.severity" class="w-full rounded border-gray-300 text-sm"><option value="low">Leicht</option><option value="medium">Mittel</option><option value="high">Hoch</option><option value="critical">KRITISCH</option></select></div>
                                    <div><label class="block text-xs font-bold text-gray-500 uppercase">Status</label><select name="status" x-model="activeDamage.status" class="w-full rounded border-gray-300 text-sm"><option value="open">Offen</option><option value="deferred">Zurückgestellt</option><option value="resolved">Erledigt</option></select></div>
                                    <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-500 uppercase">Art</label><select name="damage_type" x-model="activeDamage.type" class="w-full rounded border-gray-300 text-sm"><option value="wear">Verschleiß/Allg.</option><option value="found">Vorfinde</option><option value="accident_own">Unfall (Eigen)</option><option value="accident_other">Unfall (Fremd)</option></select></div>
                                </div>
                                <div class="border-t pt-4 mt-2 bg-blue-50 p-3 rounded border border-blue-100">
                                    <label class="block text-xs font-bold text-blue-700 uppercase mb-2">Bilder Upload</label>
                                    <div class="flex flex-wrap gap-2 mb-3" x-show="activeDamage.images && activeDamage.images.length > 0">
                                        <template x-for="(img, idx) in activeDamage.images">
                                            <div class="relative group w-16 h-16 border rounded overflow-hidden shadow-sm bg-white">
                                                <img :src="'{{ asset('storage') }}/' + img" class="w-full h-full object-cover">
                                                <a href="#" onclick="event.preventDefault(); if(confirm('Löschen?')) document.getElementById('del-img-'+activeDamage.id+'-'+idx).submit();" class="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 text-xs font-bold cursor-pointer">🗑️</a>
                                                <form :id="'del-img-'+activeDamage.id+'-'+idx" :action="'{{ url('damages') }}/' + activeDamage.id + '/image/' + idx" method="POST" style="display:none;">@csrf @method('DELETE')</form>
                                            </div>
                                        </template>
                                    </div>
                                    <input type="file" name="images[]" multiple class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-white file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-2 border-t pt-4">
                                <button type="button" @click="showEditModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded text-sm font-bold">Abbrechen</button>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded text-sm font-bold hover:bg-blue-700 shadow">Speichern</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        {{-- BERICHT MIT MÄNGELN ERFASSEN --}}
        <div x-show="showHuModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto" @click.away="showHuModal = false">
                <h3 class="text-lg font-bold mb-4">Prüfbericht erfassen</h3>
                <form action="{{ route('vehicles.hu.store', $vehicle) }}" method="POST" x-data="{ huRows: [] }">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Prüfdatum</label>
                                <input type="date" name="inspection_date" value="{{ date('Y-m-d') }}" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Organisation</label>
                                <input type="text" name="organization" placeholder="z.B. TÜV, Werkstatt" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Ergebnis</label>
                            <select name="result" required class="w-full rounded border-gray-300 text-sm font-bold"
                                    @change="if($el.value !== 'pass' && huRows.length === 0) huRows.push({title: '', severity: 'medium', is_resolved: false})">
                                <option value="pass">✅ Bestanden (Ohne Mängel)</option>
                                <option value="note">🆗 Bestanden (Geringe Mängel)</option>
                                <option value="minor">⚠️ Erhebliche Mängel</option>
                                <option value="unsafe">🛑 Verkehrsunsicher</option>
                            </select>
                        </div>

                        {{-- DYNAMISCHE MÄNGEL-LISTE --}}
                        <div class="bg-gray-50 p-4 rounded border border-gray-200 mt-4">
                            <div class="flex justify-between items-center mb-3 border-b border-gray-200 pb-2">
                                <label class="block text-xs font-bold uppercase text-gray-700">Mängel / Hinweise aus Bericht</label>
                                <button type="button" @click="huRows.push({ title: '', severity: 'medium', is_resolved: false })" class="text-[10px] bg-white border border-gray-300 text-gray-700 px-2 py-1 rounded hover:bg-gray-100 font-bold shadow-sm">
                                    + Mangel / Hinweis hinzufügen
                                </button>
                            </div>

                            <template x-for="(row, index) in huRows" :key="index">
                                <div class="flex flex-wrap md:flex-nowrap gap-2 items-start mb-2 bg-white p-2 rounded border border-gray-200 shadow-sm relative pr-8">
                                    <div class="flex-1 min-w-[200px]">
                                        <input type="text" :name="'defects['+index+'][title]'" x-model="row.title" placeholder="Position eintragen..." required class="w-full rounded border-gray-300 text-xs">
                                    </div>
                                    <div class="w-32 flex-shrink-0">
                                        <select :name="'defects['+index+'][severity]'" x-model="row.severity" class="w-full rounded border-gray-300 text-xs font-bold">
                                            <option value="low">Hinweis (OK)</option>
                                            <option value="medium">GM (Gering)</option>
                                            <option value="high">EM (Erheblich)</option>
                                            <option value="critical">VU (Gefahr)</option>
                                        </select>
                                    </div>
                                    <div class="w-24 flex-shrink-0 pt-1.5 pl-1">
                                        <label class="flex items-center cursor-pointer text-[10px] font-bold text-gray-600 border border-gray-200 rounded px-1.5 py-1 hover:bg-green-50">
                                            <input type="checkbox" :name="'defects['+index+'][is_resolved]'" value="1" x-model="row.is_resolved" class="rounded text-green-500 mr-1.5 w-3 h-3">
                                            Sofort<br>behoben
                                        </label>
                                    </div>
                                    <button type="button" @click="huRows.splice(index, 1)" class="text-red-400 hover:text-red-600 font-bold px-2 py-1 absolute right-1 top-2 bg-white rounded-full text-xs">✕</button>
                                </div>
                            </template>

                            <p x-show="huRows.length === 0" class="text-xs text-gray-400 italic text-center py-2">Keine Mängel/Hinweise erfasst.</p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded border border-blue-100 mt-4">
                            <label class="block text-[10px] font-bold uppercase text-blue-800 mb-3">Ausgewählte Fristen aktualisieren (Nur wenn Bestanden):</label>
                            <div class="space-y-2">
                                <label class="flex items-center p-1 hover:bg-blue-100 rounded cursor-pointer">
                                    <input type="checkbox" name="update_hu" value="1" checked class="rounded text-blue-600 h-4 w-4">
                                    <span class="ml-2 text-sm font-bold text-gray-800">HU (TÜV) <span class="text-blue-600">➔ +{{ $vehicle->is_bokraft ? '1 Jahr' : '2 Jahre' }}</span></span>
                                </label>
                                <label class="flex items-center p-1 hover:bg-blue-100 rounded cursor-pointer">
                                    <input type="checkbox" name="update_uvv" value="1" checked class="rounded text-blue-600 h-4 w-4">
                                    <span class="ml-2 text-sm text-gray-700">UVV Fahrzeug <span class="text-blue-600 font-bold">➔ +1 Jahr</span></span>
                                </label>
                                @if($vehicle->is_bokraft)
                                <label class="flex items-center p-1 hover:bg-blue-100 rounded cursor-pointer">
                                    <input type="checkbox" name="update_bokraft" value="1" checked class="rounded text-purple-600 h-4 w-4">
                                    <span class="ml-2 text-sm text-purple-800 font-bold">BOKraft (PersBefG) <span class="text-purple-600">➔ +1 Jahr</span></span>
                                </label>
                                @endif
                                @if($vehicle->has_lift)
                                <label class="flex items-center p-1 hover:bg-blue-100 rounded cursor-pointer">
                                    <input type="checkbox" name="update_lift" value="1" checked class="rounded text-blue-600 h-4 w-4">
                                    <span class="ml-2 text-sm text-blue-800 font-bold">Lift / Rampe <span class="text-blue-600">➔ +1 Jahr</span></span>
                                </label>
                                @endif
                                @if($vehicle->is_electric)
                                <label class="flex items-center p-1 hover:bg-emerald-100 rounded cursor-pointer">
                                    <input type="checkbox" name="update_cable" value="1" checked class="rounded text-emerald-600 h-4 w-4">
                                    <span class="ml-2 text-sm text-emerald-800 font-bold">DGUV V3 (Ladekabel) <span class="text-emerald-600">➔ +1 Jahr</span></span>
                                </label>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="showHuModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded font-bold text-sm">Abbrechen</button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 shadow-sm text-sm">Speichern</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- BERICHT BEARBEITEN --}}
        <div x-show="showHuEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="showHuEditModal = false">
                <h3 class="text-lg font-bold mb-4">Prüfbericht korrigieren</h3>
                <form :action="'{{ url('vehicles/'.$vehicle->id.'/hu') }}/' + editHuReport.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Prüfdatum</label>
                                <input type="date" name="inspection_date" x-model="editHuReport.date" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Organisation</label>
                                <input type="text" name="organization" x-model="editHuReport.org" required class="w-full rounded border-gray-300 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Ergebnis</label>
                            <select name="result" x-model="editHuReport.result" required class="w-full rounded border-gray-300 text-sm font-bold">
                                <option value="pass">✅ Bestanden (Ohne Mängel)</option>
                                <option value="note">🆗 Bestanden (Geringe Mängel)</option>
                                <option value="minor">⚠️ Erhebliche Mängel</option>
                                <option value="unsafe">🛑 Verkehrsunsicher</option>
                            </select>
                        </div>
                        <p class="text-[10px] text-gray-500 italic mt-2">Hinweis: Das Korrigieren ändert den Eintrag in der Historie, passt aber Fristen nicht mehr rückwirkend an.</p>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="showHuEditModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded font-bold text-sm">Abbrechen</button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 shadow-sm text-sm">Ändern</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
