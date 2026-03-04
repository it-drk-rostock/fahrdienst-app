<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Organisation & Struktur') }}
            </h2>
            <button x-data @click="$dispatch('open-area-modal', { mode: 'create' })"
                    class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-bold shadow hover:bg-blue-700 transition uppercase tracking-wider">
                + Neuer Bereich
            </button>
        </div>
    </x-slot>

    <div class="py-12 w-full px-4 mx-auto space-y-6">

        @if($areas->isEmpty())
            <div class="bg-white p-12 rounded-lg shadow text-center border border-gray-200">
                <h3 class="text-lg font-bold text-gray-500">Noch keine Struktur angelegt.</h3>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($areas as $area)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full">

                        {{-- HEADER: Area Info --}}
                        <div class="px-6 py-4 bg-gray-100 border-b border-gray-200 flex justify-between items-start">
                            <div class="w-full">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="font-bold text-lg text-gray-800">{{ $area->name }}</h3>

                                    <button x-data
                                            @click="$dispatch('open-area-modal', {
                                                mode: 'edit',
                                                id: {{ $area->id }},
                                                name: '{{ $area->name }}',
                                                company_code: '{{ $area->company_code ?? '' }}',
                                                manager_name: '{{ $area->manager_name ?? '' }}',
                                                manager_email: '{{ $area->manager_email ?? '' }}',
                                                telephone: '{{ $area->telephone ?? '' }}'
                                            })"
                                            class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition uppercase cursor-pointer">
                                        Bearbeiten
                                    </button>
                                </div>

                                <div class="space-y-1">
                                    @if($area->company_code)
                                        <div class="text-xs text-gray-500">
                                            <span class="font-bold">Buchungskreis:</span> {{ $area->company_code }}
                                        </div>
                                    @endif
                                    @if($area->manager_name)
                                        <div class="text-xs text-gray-500 flex items-center gap-1">
                                            <span class="font-bold">Leitung:</span> {{ $area->manager_name }}
                                        </div>
                                    @endif
                                    @if($area->telephone)
                                        <div class="text-xs text-gray-500 flex items-center gap-1">
                                            <span class="font-bold">Tel:</span> {{ $area->telephone }}
                                        </div>
                                    @endif
                                    @if($area->manager_email)
                                        <div class="text-xs text-blue-600">
                                            {{ $area->manager_email }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- BODY: Kostenstellen --}}
                        <div class="p-4 flex-grow bg-white">
                            <h4 class="text-[10px] font-bold uppercase text-gray-400 mb-3 tracking-wider">Kostenstellen</h4>

                            @if($area->costCenters->isEmpty())
                                <p class="text-sm text-gray-400 italic">Keine Kostenstellen.</p>
                            @else
                                <ul class="space-y-2">
                                    @foreach($area->costCenters as $cc)
                                        <li class="bg-gray-50 px-3 py-2 rounded border border-gray-100 group hover:border-blue-200 transition">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-bold text-gray-800 bg-white border border-gray-200 px-1.5 rounded">{{ $cc->code }}</span>
                                                        <span class="text-xs font-bold text-gray-600">{{ $cc->name }}</span>
                                                    </div>
                                                    <div class="mt-1 space-y-0.5">
                                                        @if($cc->contact_name)
                                                            <div class="text-[10px] text-gray-500">👤 {{ $cc->contact_name }}</div>
                                                        @endif
                                                        @if($cc->telephone)
                                                            <div class="text-[10px] text-gray-500">📞 {{ $cc->telephone }}</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <button x-data
                                                        @click="$dispatch('open-kst-modal', {
                                                            mode: 'edit',
                                                            id: {{ $cc->id }},
                                                            area_name: '{{ $area->name }}',
                                                            name: '{{ $cc->name }}',
                                                            code: '{{ $cc->code }}',
                                                            contact_name: '{{ $cc->contact_name ?? '' }}',
                                                            contact_email: '{{ $cc->contact_email ?? '' }}',
                                                            telephone: '{{ $cc->telephone ?? '' }}'
                                                        })"
                                                        class="text-[10px] font-bold text-gray-400 hover:text-blue-600 bg-white border border-gray-200 px-2 py-1 rounded hover:border-blue-300 transition cursor-pointer">
                                                    Bearbeiten
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- FOOTER --}}
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                            <button x-data @click="$dispatch('open-kst-modal', { mode: 'create', area_id: {{ $area->id }}, area_name: '{{ $area->name }}' })"
                                    class="w-full text-center text-xs font-bold text-blue-600 hover:text-blue-800 hover:bg-blue-50 py-2 rounded transition border border-dashed border-blue-200 hover:border-blue-400 cursor-pointer">
                                + Kostenstelle hinzufügen
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- GLOBALE FORMULARE FÜR LÖSCHEN (Verhindert Quoting-Fehler im JS) --}}
    <form id="delete-area-form" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>
    <form id="delete-kst-form" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>

    {{-- MODAL 1: BEREICH (AREA) --}}
    <div x-data="{
            show: false,
            mode: 'create',
            id: null,
            name: '',
            company_code: '',
            manager_name: '',
            manager_email: '',
            telephone: ''
         }"
         @open-area-modal.window="
            show = true;
            mode = $event.detail.mode;
            if(mode === 'edit') {
                id = $event.detail.id;
                name = $event.detail.name;
                company_code = $event.detail.company_code;
                manager_name = $event.detail.manager_name;
                manager_email = $event.detail.manager_email;
                telephone = $event.detail.telephone;
            } else {
                id = null; name = ''; company_code = ''; manager_name = ''; manager_email = ''; telephone = '';
            }
         "
         x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">

        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="show = false">
            <h3 class="text-lg font-bold mb-4 text-gray-800" x-text="mode === 'edit' ? 'Bereich bearbeiten' : 'Neuen Bereich anlegen'"></h3>

            <form x-bind:action="mode === 'edit' ? '{{ url('organization/area') }}/' + id : '{{ route('organization.area.store') }}'" method="POST">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500">Name des Bereichs</label>
                        <input type="text" name="name" x-model="name" class="w-full rounded border-gray-300 text-sm" required>
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
                    {{-- Löschen Button (Sicher implementiert über externes Formular) --}}
                    <div>
                        <template x-if="mode === 'edit'">
                            <button type="button"
                                    @click="if(confirm('Bereich wirklich löschen? ACHTUNG: Alle zugehörigen Kostenstellen werden mitgelöscht!')) {
                                        let form = document.getElementById('delete-area-form');
                                        form.action = '{{ url('organization/area') }}/' + id;
                                        form.submit();
                                    }"
                                    class="text-red-500 hover:text-red-700 text-xs font-bold uppercase underline">
                                Bereich Löschen
                            </button>
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

    {{-- MODAL 2: KOSTENSTELLE (KST) --}}
    <div x-data="{
            show: false,
            mode: 'create',
            id: null,
            area_id: null,
            area_name: '',
            name: '',
            code: '',
            contact_name: '',
            contact_email: '',
            telephone: ''
         }"
         @open-kst-modal.window="
            show = true;
            mode = $event.detail.mode;
            area_name = $event.detail.area_name;
            if(mode === 'edit') {
                id = $event.detail.id;
                name = $event.detail.name;
                code = $event.detail.code;
                contact_name = $event.detail.contact_name;
                contact_email = $event.detail.contact_email;
                telephone = $event.detail.telephone;
            } else {
                area_id = $event.detail.area_id;
                id = null; name = ''; code = ''; contact_name = ''; contact_email = ''; telephone = '';
            }
         "
         x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">

        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6" @click.away="show = false">
            <h3 class="text-lg font-bold mb-2 text-gray-800" x-text="mode === 'edit' ? 'Kostenstelle bearbeiten' : 'Neue Kostenstelle'"></h3>
            <p class="text-xs text-gray-500 mb-4">Bereich: <span class="font-bold text-blue-600" x-text="area_name"></span></p>

            <form x-bind:action="mode === 'edit' ? '{{ url('organization/costcenter') }}/' + id : '{{ route('organization.costcenter.store') }}'" method="POST">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="area_id" :value="area_id">

                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold uppercase text-gray-500">Code</label>
                            <input type="text" name="code" x-model="code" placeholder="KST..." class="w-full rounded border-gray-300 text-sm font-bold" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold uppercase text-gray-500">Bezeichnung</label>
                            <input type="text" name="name" x-model="name" class="w-full rounded border-gray-300 text-sm" required>
                        </div>
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
                    {{-- Löschen Button (Sicher über externes Formular) --}}
                    <div>
                        <template x-if="mode === 'edit'">
                             <button type="button"
                                    @click="if(confirm('Kostenstelle löschen?')) {
                                        let form = document.getElementById('delete-kst-form');
                                        form.action = '{{ url('organization/costcenter') }}/' + id;
                                        form.submit();
                                    }"
                                    class="text-red-500 hover:text-red-700 text-xs font-bold uppercase underline">
                                Löschen
                            </button>
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
