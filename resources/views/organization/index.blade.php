<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Organisation & Struktur') }}
            </h2>
            <button x-data @click="$dispatch('open-area-modal', { mode: 'create', parent_id: '' })"
                    class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-bold shadow hover:bg-blue-700 transition uppercase tracking-wider">
                + Neuer Hauptbereich
            </button>
        </div>
    </x-slot>

    <div class="py-12 w-full px-4 mx-auto space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm font-bold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded shadow-sm font-bold">
                {{ session('warning') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm font-bold">
                @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
            </div>
        @endif

        @if($mainAreas->isEmpty())
            <div class="bg-white p-12 rounded-lg shadow text-center border border-gray-200">
                <h3 class="text-lg font-bold text-gray-500">Noch keine Struktur angelegt.</h3>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-start">
                @foreach($mainAreas as $area)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full">

                        {{-- HEADER: Area Info --}}
                        <div class="px-6 py-4 bg-gray-100 border-b border-gray-200 flex justify-between items-start">
                            <div class="w-full">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                                        <span class="text-blue-600">🏢</span>
                                        @if($area->company_code) <span class="text-gray-400 font-mono text-sm">[{{ $area->company_code }}]</span> @endif
                                        {{ $area->name }}
                                    </h3>

                                    <button x-data
                                            @click="$dispatch('open-area-modal', {
                                                mode: 'edit',
                                                id: {{ $area->id }},
                                                parent_id: '',
                                                name: '{{ addslashes($area->name) }}',
                                                company_code: '{{ addslashes($area->company_code ?? '') }}',
                                                manager_name: '{{ addslashes($area->manager_name ?? '') }}',
                                                manager_email: '{{ addslashes($area->manager_email ?? '') }}',
                                                telephone: '{{ addslashes($area->telephone ?? '') }}'
                                            })"
                                            class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition uppercase cursor-pointer">
                                        Bearbeiten
                                    </button>
                                </div>

                                <div class="space-y-1">
                                    @if($area->manager_name)
                                        <div class="text-xs text-gray-500 flex items-center gap-1"><span class="font-bold">Leitung:</span> {{ $area->manager_name }}</div>
                                    @endif
                                    @if($area->telephone)
                                        <div class="text-xs text-gray-500 flex items-center gap-1"><span class="font-bold">Tel:</span> {{ $area->telephone }}</div>
                                    @endif
                                    @if($area->manager_email)
                                        <div class="text-xs text-blue-600">{{ $area->manager_email }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- BODY: Kostenstellen & Unterbereiche --}}
                        <div class="p-4 flex-grow bg-white space-y-6">

                            {{-- DIREKTE KOSTENSTELLEN --}}
                            <div>
                                <h4 class="text-[10px] font-bold uppercase text-gray-400 mb-3 tracking-wider">Direkte Kostenstellen</h4>
                                @if($area->costCenters->isEmpty())
                                    <p class="text-[10px] text-gray-400 italic">Keine direkten Kostenstellen.</p>
                                @else
                                    <ul class="space-y-2">
                                        @foreach($area->costCenters as $cc)
                                            <li class="bg-gray-50 px-3 py-2 rounded border border-gray-100 group hover:border-blue-200 transition">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="text-sm font-bold text-gray-800 bg-white border border-gray-200 px-1.5 rounded">{{ $cc->code }}</span>
                                                            @if($cc->short_name)
                                                                <span class="text-xs font-bold text-blue-700 bg-blue-50 px-1.5 rounded border border-blue-100">{{ $cc->short_name }}</span>
                                                            @endif
                                                            <span class="text-xs text-gray-600">{{ $cc->name }}</span>
                                                        </div>
                                                        <div class="mt-1 space-y-0.5">
                                                            @if($cc->contact_name) <div class="text-[10px] text-gray-500">👤 {{ $cc->contact_name }}</div> @endif
                                                            @if($cc->telephone) <div class="text-[10px] text-gray-500">📞 {{ $cc->telephone }}</div> @endif
                                                        </div>
                                                    </div>
                                                    <button x-data @click="$dispatch('open-kst-modal', { mode: 'edit', id: {{ $cc->id }}, area_id: {{ $area->id }}, name: '{{ addslashes($cc->name) }}', short_name: '{{ addslashes($cc->short_name ?? '') }}', code: '{{ addslashes($cc->code) }}', contact_name: '{{ addslashes($cc->contact_name ?? '') }}', contact_email: '{{ addslashes($cc->contact_email ?? '') }}', telephone: '{{ addslashes($cc->telephone ?? '') }}' })" class="text-[10px] font-bold text-gray-400 hover:text-blue-600 bg-white border border-gray-200 px-2 py-1 rounded hover:border-blue-300 transition cursor-pointer">
                                                        Edit
                                                    </button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            {{-- UNTERBEREICHE --}}
                            @if($area->children->isNotEmpty())
                                <div>
                                    <h4 class="text-[10px] font-bold uppercase text-blue-400 mb-3 tracking-wider border-t border-gray-100 pt-4">Unterbereiche</h4>
                                    <div class="space-y-3">
                                        @foreach($area->children as $subArea)
                                            <div class="border border-blue-100 rounded-lg p-3 bg-blue-50/30">
                                                <div class="flex justify-between items-start mb-2 border-b border-blue-100 pb-2">
                                                    <div>
                                                        <span class="font-bold text-blue-800 text-sm">↳ {{ $subArea->name }}</span>
                                                        <div class="text-[9px] text-gray-500 mt-0.5">
                                                            @if($subArea->company_code) BK: {{ $subArea->company_code }} @endif
                                                            @if($subArea->manager_name) | {{ $subArea->manager_name }} @endif
                                                        </div>
                                                    </div>
                                                    <button x-data
                                                            @click="$dispatch('open-area-modal', {
                                                                mode: 'edit',
                                                                id: {{ $subArea->id }},
                                                                parent_id: '{{ $area->id }}',
                                                                name: '{{ addslashes($subArea->name) }}',
                                                                company_code: '{{ addslashes($subArea->company_code ?? '') }}',
                                                                manager_name: '{{ addslashes($subArea->manager_name ?? '') }}',
                                                                manager_email: '{{ addslashes($subArea->manager_email ?? '') }}',
                                                                telephone: '{{ addslashes($subArea->telephone ?? '') }}'
                                                            })"
                                                            class="text-[10px] font-bold text-blue-600 hover:underline">
                                                        Edit
                                                    </button>
                                                </div>

                                                {{-- Kostenstellen des Unterbereichs --}}
                                                <ul class="space-y-1 mt-2">
                                                    @forelse($subArea->costCenters as $subCc)
                                                        <li class="flex justify-between items-center bg-white px-2 py-1.5 rounded border border-gray-100">
                                                            <span class="text-xs text-gray-700">
                                                                <span class="font-bold font-mono">{{ $subCc->code }}</span>
                                                                @if($subCc->short_name) <span class="font-bold text-blue-600">[{{ $subCc->short_name }}]</span> @endif
                                                                {{ $subCc->name }}
                                                            </span>
                                                            <button x-data @click="$dispatch('open-kst-modal', { mode: 'edit', id: {{ $subCc->id }}, area_id: {{ $subArea->id }}, name: '{{ addslashes($subCc->name) }}', short_name: '{{ addslashes($subCc->short_name ?? '') }}', code: '{{ addslashes($subCc->code) }}', contact_name: '{{ addslashes($subCc->contact_name ?? '') }}', contact_email: '{{ addslashes($subCc->contact_email ?? '') }}', telephone: '{{ addslashes($subCc->telephone ?? '') }}' })" class="text-[9px] text-gray-400 hover:text-blue-600 font-bold">Edit</button>
                                                        </li>
                                                    @empty
                                                        <li class="text-[10px] text-gray-400 italic">Keine KST im Unterbereich</li>
                                                    @endforelse
                                                </ul>

                                                <div class="mt-2 text-right">
                                                    <button x-data @click="$dispatch('open-kst-modal', { mode: 'create', area_id: {{ $subArea->id }} })" class="text-[10px] font-bold text-blue-600 hover:underline">
                                                        + KST hier anlegen
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>

                        {{-- FOOTER --}}
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex gap-2">
                            <button x-data @click="$dispatch('open-area-modal', { mode: 'create', parent_id: '{{ $area->id }}' })"
                                    class="w-1/2 text-center text-[10px] font-bold text-gray-600 hover:text-gray-800 hover:bg-gray-200 py-2 rounded transition border border-dashed border-gray-300 hover:border-gray-400 cursor-pointer">
                                + Unterbereich
                            </button>
                            <button x-data @click="$dispatch('open-kst-modal', { mode: 'create', area_id: {{ $area->id }} })"
                                    class="w-1/2 text-center text-[10px] font-bold text-blue-600 hover:text-blue-800 hover:bg-blue-50 py-2 rounded transition border border-dashed border-blue-200 hover:border-blue-400 cursor-pointer">
                                + KST (Hauptbereich)
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- GLOBALE FORMULARE FÜR LÖSCHEN --}}
    <form id="delete-area-form" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>
    <form id="delete-kst-form" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>

    {{-- MODAL 1: BEREICH (AREA) --}}
    <div x-data="{
            show: false, mode: 'create', id: null, parent_id: '', name: '', company_code: '', manager_name: '', manager_email: '', telephone: ''
         }"
         @open-area-modal.window="
            show = true; mode = $event.detail.mode; parent_id = $event.detail.parent_id || '';
            if(mode === 'edit') {
                id = $event.detail.id; name = $event.detail.name; company_code = $event.detail.company_code;
                manager_name = $event.detail.manager_name; manager_email = $event.detail.manager_email; telephone = $event.detail.telephone;
            } else {
                id = null; name = ''; company_code = ''; manager_name = ''; manager_email = ''; telephone = '';
            }
         "
         x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">

        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="show = false">
            <h3 class="text-lg font-bold mb-4 text-gray-800" x-text="mode === 'edit' ? 'Bereich bearbeiten' : 'Neuen Bereich anlegen'"></h3>
            <form x-bind:action="mode === 'edit' ? '{{ url('organization/area') }}/' + id : '{{ route('organization.area.store') }}'" method="POST">
                @csrf <template x-if="mode === 'edit'">@method('PUT')</template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500">Name des Bereichs</label>
                        <input type="text" name="name" x-model="name" class="w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-blue-600 mb-1">Übergeordneter Bereich</label>
                        <select name="parent_id" x-model="parent_id" class="w-full rounded border-blue-200 bg-blue-50 text-sm">
                            <option value="">-- Kein (Ist ein Hauptbereich) --</option>
                            @foreach($allAreas as $a) <option value="{{ $a->id }}">{{ $a->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500">Buchungskreis</label>
                        <input type="text" name="company_code" x-model="company_code" placeholder="z.B. 1000" class="w-full rounded border-gray-300 text-sm font-mono">
                    </div>
                    <div class="border-t border-gray-100 pt-2">
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Leitung / Kontakt</label>
                        <div class="space-y-3">
                            <input type="text" name="manager_name" x-model="manager_name" placeholder="Name" class="w-full rounded border-gray-300 text-sm">
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="telephone" x-model="telephone" placeholder="Telefon" class="w-full rounded border-gray-300 text-sm">
                                <input type="email" name="manager_email" x-model="manager_email" placeholder="E-Mail" class="w-full rounded border-gray-300 text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-between items-center">
                    <div>
                        <template x-if="mode === 'edit'">
                            <button type="button" @click="if(confirm('Bereich wirklich löschen? ACHTUNG: Alle zugehörigen Kostenstellen werden mitgelöscht!')) { let form = document.getElementById('delete-area-form'); form.action = '{{ url('organization/area') }}/' + id; form.submit(); }" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase underline">Bereich Löschen</button>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="show = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded text-sm">Abbrechen</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 text-sm">Speichern</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: KOSTENSTELLE (KST) - JETZT MIT VERSCHIEBE-FUNKTION --}}
    <div x-data="{
            show: false, mode: 'create', id: null, area_id: '', name: '', short_name: '', code: '', contact_name: '', contact_email: '', telephone: ''
         }"
         @open-kst-modal.window="
            show = true; mode = $event.detail.mode;
            if(mode === 'edit') {
                id = $event.detail.id; area_id = $event.detail.area_id; name = $event.detail.name; short_name = $event.detail.short_name; code = $event.detail.code;
                contact_name = $event.detail.contact_name; contact_email = $event.detail.contact_email; telephone = $event.detail.telephone;
            } else {
                area_id = $event.detail.area_id; id = null; name = ''; short_name = ''; code = ''; contact_name = ''; contact_email = ''; telephone = '';
            }
         "
         x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">

        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="show = false">
            <h3 class="text-lg font-bold mb-4 text-gray-800" x-text="mode === 'edit' ? 'Kostenstelle bearbeiten' : 'Neue Kostenstelle'"></h3>

            <form x-bind:action="mode === 'edit' ? '{{ url('organization/costcenter') }}/' + id : '{{ route('organization.costcenter.store') }}'" method="POST">
                @csrf <template x-if="mode === 'edit'">@method('PUT')</template>

                <div class="space-y-4">
                    {{-- DROPDOWN ZUM VERSCHIEBEN --}}
                    <div>
                        <label class="block text-xs font-bold uppercase text-blue-600 mb-1">Zugeordneter Bereich</label>
                        <select name="area_id" x-model="area_id" class="w-full rounded border-blue-200 bg-blue-50 text-sm" required>
                            <option value="">-- Bereich wählen --</option>
                            @foreach($allAreas as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500">Nummer / Code</label>
                            <input type="text" name="code" x-model="code" placeholder="4711" class="w-full rounded border-gray-300 text-sm font-bold" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-blue-600">Kürzel (Dashboard)</label>
                            <input type="text" name="short_name" x-model="short_name" placeholder="z.B. Rostock" class="w-full rounded border-blue-300 bg-blue-50 text-sm font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500">Vollständige Bezeichnung</label>
                        <input type="text" name="name" x-model="name" placeholder="z.B. Standort Rostock Nord" class="w-full rounded border-gray-300 text-sm" required>
                    </div>

                    <div class="border-t border-gray-100 pt-2">
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Lokaler Ansprechpartner</label>
                        <div class="space-y-3">
                            <input type="text" name="contact_name" x-model="contact_name" placeholder="Name" class="w-full rounded border-gray-300 text-sm">
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="telephone" x-model="telephone" placeholder="Telefon" class="w-full rounded border-gray-300 text-sm">
                                <input type="email" name="contact_email" x-model="contact_email" placeholder="E-Mail" class="w-full rounded border-gray-300 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-between items-center">
                    <div>
                        <template x-if="mode === 'edit'">
                             <button type="button" @click="if(confirm('Kostenstelle löschen?')) { let form = document.getElementById('delete-kst-form'); form.action = '{{ url('organization/costcenter') }}/' + id; form.submit(); }" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase underline">Löschen</button>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="show = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded text-sm">Abbrechen</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 text-sm">Speichern</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
