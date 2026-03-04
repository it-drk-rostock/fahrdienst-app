<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Werkstatt-Kalender') }}
            </h2>

            <div class="flex bg-gray-200 rounded p-1">
                <a href="{{ route('calendar.index', ['date' => $date->format('Y-m-d'), 'mode' => 'month']) }}"
                   class="px-3 py-1 text-xs font-bold rounded {{ $mode == 'month' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                   Monat
                </a>
                <a href="{{ route('calendar.index', ['date' => $date->format('Y-m-d'), 'mode' => 'week']) }}"
                   class="px-3 py-1 text-xs font-bold rounded {{ $mode == 'week' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                   Woche
                </a>
            </div>

            <div class="flex items-center gap-4 bg-white rounded-lg shadow-sm p-1 border border-gray-200" x-data="{ showPicker: false }">
                @php
                    $prevDate = $mode == 'week' ? $date->copy()->subWeek() : $date->copy()->subMonth();
                    $nextDate = $mode == 'week' ? $date->copy()->addWeek() : $date->copy()->addMonth();
                    $label = $mode == 'week' ? 'KW ' . $date->week . ' (' . $date->format('M Y') . ')' : $date->locale('de')->translatedFormat('F Y');
                @endphp
                <a href="{{ route('calendar.index', ['date' => $prevDate->format('Y-m-d'), 'mode' => $mode]) }}" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded">◀</a>

                <div class="relative">
                    <button @click="showPicker = !showPicker" class="font-bold text-gray-700 w-40 text-center hover:bg-gray-50 rounded px-2 py-1 border border-transparent hover:border-gray-200 transition">
                        {{ $label }} ▾
                    </button>

                    <div x-show="showPicker" @click.away="showPicker = false" class="absolute top-full left-1/2 -translate-x-1/2 mt-2 bg-white border border-gray-200 shadow-xl rounded-lg p-3 z-50 w-72" style="display: none;" x-transition>
                        <div class="flex justify-between items-center mb-3 pb-2 border-b">
                            <a href="{{ route('calendar.index', ['date' => $date->copy()->subYear()->format('Y-m-d'), 'mode' => $mode]) }}" class="text-gray-500 hover:text-blue-600 font-bold px-2">«</a>
                            <span class="font-bold text-sm">{{ $date->year }}</span>
                            <a href="{{ route('calendar.index', ['date' => $date->copy()->addYear()->format('Y-m-d'), 'mode' => $mode]) }}" class="text-gray-500 hover:text-blue-600 font-bold px-2">»</a>
                        </div>

                        @if($mode == 'month')
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(range(1, 12) as $m)
                                    <a href="{{ route('calendar.index', ['date' => Carbon\Carbon::create($date->year, $m, 1)->format('Y-m-d'), 'mode' => 'month']) }}"
                                       class="text-xs text-center py-2 rounded hover:bg-blue-50 {{ $date->month == $m ? 'bg-blue-100 text-blue-700 font-bold' : 'text-gray-600' }}">
                                        {{ Carbon\Carbon::create(2024, $m, 1)->locale('de')->translatedFormat('M') }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-[10px] text-gray-400 text-center mb-1">Kalenderwoche wählen</div>
                            <div class="grid grid-cols-6 gap-1 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                @for($i = 1; $i <= 53; $i++)
                                    <a href="{{ route('calendar.index', ['date' => Carbon\Carbon::now()->setISODate($date->year, $i)->format('Y-m-d'), 'mode' => 'week']) }}"
                                       class="text-[10px] text-center py-1.5 rounded hover:bg-blue-50 border {{ $date->week == $i ? 'bg-blue-100 text-blue-700 font-bold border-blue-200' : 'text-gray-600 border-gray-100' }}">
                                        {{ $i }}
                                    </a>
                                @endfor
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('calendar.index', ['date' => $nextDate->format('Y-m-d'), 'mode' => $mode]) }}" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded">▶</a>
            </div>
        </div>
    </x-slot>

    {{-- SICHERER DATEN-TRANSFER FÜR ALPINE.JS --}}
    <script>
        window.calendarVehiclesData = @json($vehicles);
    </script>

    <div class="py-6 px-4 w-full h-[calc(100vh-100px)] flex flex-col" x-data="{
        showEditModal: false,
        modalMode: 'edit',
        actionUrl: '',
        searchLicense: '',
        vehiclesData: window.calendarVehiclesData,
        activeAppointmentsWarning: [],

        editForm: {
            id: null, title: '', vehicle_id: '', workshop: '', start: '', end: '', status: 'planned', actual_end: '', notes: '',
            items: [], services: [], newPositions: [], removePositions: [], suggestions: [],
            transport_organized: false, transport_method: '', has_rental_car: false, driver_name: '', driver_status: '', billing_dept: '', vehicle_cost_center: '',
            pickup_needed: false, pickup_method: '', pickup_name: '', pickup_status: ''
        },
        updateUrlTemplate: '{{ route('workshop.update', '000') }}',
        storeUrl: '{{ route('workshop.store') }}',

        // --- AUTO-START WENN VON FAHRZEUGAKTE KOMMEND ---
        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'create') {
                const vId = urlParams.get('vehicle_id');
                const lp = urlParams.get('license_plate');
                const preselectDamage = urlParams.get('preselect_damage');

                setTimeout(() => {
                    this.createAppointment('{{ $date->format('Y-m-d') }}', '08:00', vId, '', lp);
                    this.findVehicleId();

                    if (preselectDamage) {
                        setTimeout(() => {
                            let index = this.editForm.suggestions.findIndex(s => s.value === 'damage_id:' + preselectDamage);
                            if (index !== -1) {
                                let sugg = this.editForm.suggestions[index];
                                this.editForm.newPositions.push({value: sugg.value, label: sugg.label, info: sugg.info});
                                this.editForm.suggestions.splice(index, 1);
                            }
                        }, 100);
                    }
                }, 150);
            }
        },

        editAppointment(data) {
            this.modalMode = 'edit';
            this.editForm = data;
            this.searchLicense = data.title;
            this.editForm.newPositions = [];
            this.editForm.removePositions = [];
            this.activeAppointmentsWarning = [];

            if(this.editForm.notes === null) this.editForm.notes = '';

            this.actionUrl = this.updateUrlTemplate.replace('000', data.id);

            if(this.editForm.start) this.editForm.start = this.editForm.start.replace(' ', 'T').substring(0, 16);
            if(this.editForm.end) this.editForm.end = this.editForm.end.replace(' ', 'T').substring(0, 16);

            this.showEditModal = true;
        },

        createAppointment(dateStr, timeStr = '08:00', vehicleId = null, note = '', vehicleTitle = '') {
            this.modalMode = 'create';
            this.searchLicense = vehicleTitle;
            this.activeAppointmentsWarning = [];

            this.editForm = {
                id: null, title: '', vehicle_id: vehicleId, workshop: '',
                start: dateStr + 'T' + timeStr,
                end: dateStr + 'T16:00',
                status: 'planned', actual_end: '', notes: note,
                items: [], services: [], newPositions: [], removePositions: [], suggestions: [],
                transport_organized: false, transport_method: '', has_rental_car: false, driver_name: '', driver_status: '', billing_dept: '', vehicle_cost_center: '',
                pickup_needed: false, pickup_method: '', pickup_name: '', pickup_status: ''
            };

            this.actionUrl = this.storeUrl;
            this.showEditModal = true;
        },

        // --- ZEIT RUNDEN (15-Minuten Raster) ---
        snapTo15Min(field) {
            if (!this.editForm[field]) return;
            let d = new Date(this.editForm[field]);
            let m = d.getMinutes();
            let roundedM = Math.round(m / 15) * 15;
            d.setMinutes(roundedM);
            d.setSeconds(0);
            let pad = (n) => n < 10 ? '0'+n : n;
            this.editForm[field] = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        },

        markForRemoval(index) {
            let item = this.editForm.items[index];
            this.editForm.removePositions.push(item.type + ':' + item.id);
            this.editForm.suggestions.push({ value: (item.type === 'damage' ? 'damage_id:' : '') + item.id, label: item.label, info: 'Zurückgestellt' });
            this.editForm.items.splice(index, 1);
        },

        updateMethodLogic() {
            if (this.editForm.transport_method === 'driver_service' && !this.editForm.billing_dept) {
                this.editForm.billing_dept = this.editForm.vehicle_cost_center;
            }
        },

        // --- AUTO FINDEN & MÄNGEL LADEN ---
        findVehicleId() {
            let selected = this.vehiclesData.find(v => v.license_plate === this.searchLicense);
            if (selected) {
                this.editForm.vehicle_id = selected.id;
                this.editForm.suggestions = [];

                if (selected.damages && selected.damages.length > 0) {
                    selected.damages.forEach(d => {
                        let dDate = new Date(d.created_at);
                        let formattedDate = ('0' + dDate.getDate()).slice(-2) + '.' + ('0' + (dDate.getMonth() + 1)).slice(-2) + '.' + dDate.getFullYear();
                        this.editForm.suggestions.push({ value: 'damage_id:' + d.id, label: '⚠️ ' + d.title, info: 'Gemeldet: ' + formattedDate });
                    });
                }

                if (selected.workshop_appointments && selected.workshop_appointments.length > 0) {
                    this.activeAppointmentsWarning = selected.workshop_appointments;
                } else {
                    this.activeAppointmentsWarning = [];
                }
            } else {
                this.editForm.vehicle_id = null;
                this.editForm.suggestions = [];
                this.activeAppointmentsWarning = [];
            }
        }
    }">
        <div class="bg-white flex flex-col flex-1 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 select-none">

                <div class="grid grid-cols-7 border-b border-gray-200 bg-white z-30 shadow-sm flex-shrink-0 sticky top-0" style="{{ $mode == 'week' ? 'margin-left: 50px;' : '' }}">
                    @if($mode == 'month')
                        @foreach(['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'] as $dayName)
                            <div class="py-2 text-center text-xs font-bold text-gray-500 uppercase bg-white border-r border-transparent">{{ $dayName }}</div>
                        @endforeach
                    @else
                        @foreach($calendar as $day)
                            @php
                                $headerColor = 'text-gray-500';
                                if($day['type'] == 'holiday') $headerColor = 'text-red-500'; elseif($day['type'] == 'vacation') $headerColor = 'text-yellow-600'; elseif($day['type'] == 'weekend') $headerColor = 'text-gray-400';
                            @endphp
                            <div class="py-2 text-center text-xs font-bold {{ $headerColor }} uppercase relative group bg-white border-r border-transparent">
                                {{ $day['date']->locale('de')->translatedFormat('D') }}
                                <span class="{{ $day['isToday'] ? 'bg-blue-600 text-white px-1.5 rounded-full' : '' }}">{{ $day['date']->day }}.</span>
                                @if($day['typeLabel']) <span class="block text-[9px] truncate px-1 opacity-70">{{ $day['typeLabel'] }}</span> @endif
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto relative bg-gray-200">
                    @if($mode == 'month')
                        <div class="grid grid-cols-7 auto-rows-fr gap-px h-full">
                            @foreach($calendar as $day)
                                @php
                                    $bgClass = 'bg-white';
                                    if ($day['type'] == 'holiday') $bgClass = 'bg-rose-50'; elseif ($day['type'] == 'vacation') $bgClass = 'bg-amber-50'; elseif ($day['type'] == 'weekend') $bgClass = 'bg-gray-100';
                                    if (!$day['isCurrentMonth']) $bgClass = 'bg-gray-50 text-gray-400';
                                @endphp
                                <div class="{{ $bgClass }} p-1 flex flex-col gap-1 relative group transition hover:brightness-95 min-h-[100px]">
                                    <div class="flex justify-between items-center px-1 h-6">
                                        <span class="text-sm font-bold {{ $day['isToday'] ? 'bg-blue-600 text-white w-6 h-6 flex items-center justify-center rounded-full' : 'text-gray-700' }}">{{ $day['date']->day }}</span>
                                        <button @click.stop="createAppointment('{{ $day['date']->toDateString() }}')" class="opacity-0 group-hover:opacity-100 text-blue-400 hover:text-blue-700 font-bold text-lg leading-none p-1" title="Neuen Termin anlegen">+</button>
                                    </div>
                                    <div class="flex-1 flex flex-col gap-1 mt-1 overflow-y-auto max-h-[140px]">
                                        @foreach($day['events'] as $event)
                                            @include('calendar.partials.event-item', ['event' => $event, 'dayDate' => $day['date']])
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="relative min-h-[800px] h-full" style="display: grid; grid-template-columns: 50px repeat(7, 1fr); grid-template-rows: repeat(12, 1fr);">
                            <div class="row-span-12 border-r border-gray-300 bg-white sticky left-0 z-20 flex flex-col justify-between text-[10px] text-gray-400 font-mono py-1">
                                @for($i=6; $i<=18; $i++) <div class="h-full border-b border-gray-100 text-right pr-1 relative"><span class="-top-2 relative">{{ $i }}:00</span></div> @endfor
                            </div>
                            @foreach($calendar as $dayIndex => $day)
                                @php
                                    $bgClass = 'bg-white';
                                    if ($day['type'] == 'holiday') $bgClass = 'bg-rose-50'; elseif ($day['type'] == 'vacation') $bgClass = 'bg-amber-50'; elseif ($day['type'] == 'weekend') $bgClass = 'bg-gray-100';
                                @endphp
                                <div class="{{ $bgClass }} border-r border-gray-200 relative h-full group" style="grid-column: {{ $dayIndex + 2 }}; grid-row: 1 / span 12;">
                                    @for($i=0; $i<12; $i++)
                                        <div class="absolute w-full border-b border-gray-100/50 pointer-events-none" style="top: {{ ($i/12)*100 }}%; height: {{ 100/12 }}%;"></div>
                                        <div class="absolute w-full z-0 cursor-pointer hover:bg-blue-50/30" style="top: {{ ($i/12)*100 }}%; height: {{ 100/12 }}%;"
                                             @dblclick="createAppointment('{{ $day['date']->toDateString() }}', '{{ sprintf('%02d:00', $i+6) }}')" title="Doppelklick für neuen Termin"></div>
                                    @endfor
                                    @foreach($day['events'] as $event)
                                        @php
                                            $colIndex = $event->visual_col ?? 0;
                                            $totalCols = $event->visual_total_cols ?? 1;
                                            $widthPercent = 100 / $totalCols;
                                            $leftPercent = $colIndex * $widthPercent;
                                        @endphp
                                        @include('calendar.partials.event-item-week', ['event' => $event, 'dayDate' => $day['date'], 'left' => $leftPercent, 'width' => $widthPercent])
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
        </div>

        {{-- MODAL (TERMIN BEARBEITEN / ERFASSEN) --}}
        <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]" @click.away="showEditModal = false">
                <div class="flex justify-between items-start mb-4 border-b pb-2">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800" x-text="modalMode === 'create' ? 'Neuer Werkstatt-Termin' : editForm.title"></h3>
                        <p class="text-sm text-gray-500 font-bold" x-text="modalMode === 'create' ? 'Schnellerfassung' : editForm.workshop"></p>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>

                <form :action="actionUrl" method="POST">
                    @csrf
                    <template x-if="modalMode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <template x-for="newPos in editForm.newPositions"><input type="hidden" name="new_positions[]" :value="newPos.value"></template>
                    <template x-for="delPos in editForm.removePositions"><input type="hidden" name="remove_positions[]" :value="delPos"></template>

                     <div x-show="modalMode === 'create'" class="mb-4 bg-yellow-50 p-3 rounded border border-yellow-200">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Fahrzeug (Kennzeichen)</label>
                                <input type="text" list="vehicle_list" x-model="searchLicense" @change="findVehicleId()" class="w-full rounded border-gray-300 text-sm font-bold text-blue-800" placeholder="Kennzeichen tippen..." :required="modalMode === 'create'">
                                <datalist id="vehicle_list">
                                    @foreach($vehicles ?? [] as $v)
                                        <option value="{{ $v->license_plate }}" data-id="{{ $v->id }}">{{ $v->model }}</option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="vehicle_id" x-model="editForm.vehicle_id">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500">Werkstatt</label>
                                <input list="providers" name="provider_name" x-model="editForm.workshop" class="w-full rounded border-gray-300 text-sm" :required="modalMode === 'create'">
                                <datalist id="providers">@foreach($providers ?? [] as $p) <option value="{{ $p->name }}"> @endforeach</datalist>
                            </div>
                        </div>
                    </div>

                    {{-- WARNUNG VOR DOPPELTERMINEN --}}
                    <div x-show="modalMode === 'create' && activeAppointmentsWarning.length > 0" class="mb-4 bg-orange-50 p-3 rounded border border-orange-200 shadow-sm" x-cloak>
                        <div class="flex items-start gap-2 mb-2">
                            <span class="text-lg leading-none mt-0.5">⚠️</span>
                            <div>
                                <h4 class="text-xs font-bold uppercase text-orange-800">Offene Termine für dieses Fahrzeug!</h4>
                                <p class="text-[10px] text-orange-700 leading-tight mt-0.5">Es gibt bereits geplante Werkstattaufenthalte. Prüfe, ob du die Schäden nicht lieber einem bestehenden Termin hinzufügen möchtest.</p>
                            </div>
                        </div>
                        <div class="space-y-1 pl-7">
                            <template x-for="app in activeAppointmentsWarning">
                                <div class="bg-white px-2 py-1.5 rounded border border-orange-100 flex justify-between items-center text-xs shadow-sm">
                                    <span class="font-bold text-gray-700" x-text="app.workshop_name"></span>
                                    <span class="text-gray-500 font-mono font-bold" x-text="new Date(app.start_time).toLocaleDateString('de-DE')"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Auftrags-Notizen / Sonstiges</label>
                        <textarea name="notes" x-model="editForm.notes" rows="2" class="w-full rounded border-gray-300 text-sm" placeholder="Zusätzliche Infos..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                             <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500">Start (15-Min)</label>
                                    <input type="datetime-local" name="start_time" step="900" x-model="editForm.start" @change="snapTo15Min('start')" class="w-full rounded border-gray-300 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500">Ende (15-Min)</label>
                                    <input type="datetime-local" name="planned_end_time" step="900" x-model="editForm.end" @change="snapTo15Min('end')" class="w-full rounded border-gray-300 text-xs">
                                </div>
                            </div>

                            <div class="bg-blue-50 p-3 rounded border border-blue-100 space-y-3">
                                <h4 class="text-xs font-bold uppercase text-blue-700">Hinfahrt</h4>
                                <label class="flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="is_transport_organized" x-model="editForm.transport_organized" value="1" class="rounded border-gray-300 text-blue-600 h-4 w-4">
                                    <span class="ml-2 text-xs font-bold text-gray-700">Hinfahrt notwendig?</span>
                                </label>
                                <div x-show="editForm.transport_organized" class="space-y-3 pt-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Methode</label>
                                            <select name="transport_method" x-model="editForm.transport_method" @change="updateMethodLogic()" class="w-full rounded border-gray-300 text-xs font-bold">
                                                <option value="">- Wahl -</option><option value="department">Abt. fährt</option><option value="driver_service">Fahrdienst</option><option value="replacement">Werkstatt holt</option>
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <label class="flex items-center cursor-pointer bg-white px-2 py-1.5 rounded border border-gray-300 w-full hover:bg-gray-50">
                                                <input type="checkbox" name="has_rental_car" x-model="editForm.has_rental_car" value="1" class="rounded border-gray-300 text-blue-600 h-4 w-4">
                                                <span class="ml-2 text-[10px] font-bold text-gray-700">🚗 Leihwagen?</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div x-show="editForm.transport_method === 'department' || editForm.transport_method === 'driver_service'" class="pl-2 border-l-2 border-gray-300">
                                        <div x-show="editForm.transport_method === 'driver_service'" class="mb-2">
                                            <input type="text" name="transport_driver_name" x-model="editForm.driver_name" placeholder="Fahrer..." class="w-full rounded border-gray-300 text-xs mb-1">
                                            <select name="transport_billing_department" x-model="editForm.billing_dept" class="w-full rounded border-gray-300 text-xs shadow-sm bg-yellow-50"><option value="">Kostenstelle...</option>@foreach($costCenters ?? [] as $cc) <option value="{{ $cc->code }}">{{ $cc->code }}</option> @endforeach</select>
                                        </div>
                                        <select name="transport_driver_status" x-model="editForm.driver_status" class="w-full rounded border-gray-300 text-xs"><option value="">Status...</option><option value="search_needed">🔍 Suchen</option><option value="informed">✅ Informiert</option></select>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                <h4 class="text-xs font-bold uppercase text-gray-700 mb-2">Rückfahrt</h4>
                                <label class="flex items-center cursor-pointer select-none mb-2">
                                    <input type="checkbox" name="is_pickup_needed" x-model="editForm.pickup_needed" value="1" class="rounded border-gray-300 text-blue-600 h-4 w-4">
                                    <span class="ml-2 text-xs font-bold text-gray-700">Notwendig?</span>
                                </label>
                                <div x-show="editForm.pickup_needed" class="space-y-2 pl-2 border-l-2 border-gray-300">
                                    <div>
                                        <select name="pickup_method" x-model="editForm.pickup_method" class="w-full rounded border-gray-300 text-xs font-bold">
                                            <option value="">- Wahl -</option><option value="department">Abt. holt</option><option value="driver_service">Fahrdienst</option><option value="workshop">Werkstatt bringt</option>
                                        </select>
                                    </div>
                                    <div x-show="editForm.pickup_method !== 'workshop' && editForm.pickup_method !== ''">
                                        <div class="flex gap-2">
                                            <input type="text" name="pickup_driver_name" x-model="editForm.pickup_name" placeholder="Name..." class="w-2/3 rounded border-gray-300 text-xs">
                                            <select name="pickup_driver_status" x-model="editForm.pickup_status" class="w-1/3 rounded border-gray-300 text-xs cursor-pointer"><option value="search_needed">🔍 Suchen</option><option value="informed">✅ Informiert</option></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Status</label>
                                <select name="status" x-model="editForm.status" class="w-full rounded border-gray-300 text-sm font-bold shadow-sm">
                                    <option value="planned">📅 Geplant</option>
                                    <option value="active">⚙️ In Werkstatt</option>
                                    <option value="resolved">✅ Erledigt</option>
                                </select>
                            </div>

                            <div class="bg-gray-50 p-3 rounded border border-gray-200 h-64 flex flex-col" x-data="{ tempInput: '' }">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Beauftragte Positionen</label>
                                <ul class="text-xs space-y-1 overflow-y-auto flex-1 mb-2 bg-white border border-gray-200 rounded p-2">
                                    {{-- BEREITS GESPEICHERTE POSITIONEN (Vom Server) --}}
                                    <template x-for="(item, index) in editForm.items" :key="'item-'+index">
                                        <li class="flex items-center justify-between text-gray-700 border-b border-gray-50 py-1 last:border-0 group">
                                            <div class="flex items-start gap-2"><span class="text-blue-500 select-none">•</span><span x-text="item.label"></span></div>
                                            <button type="button" @click="markForRemoval(index)" class="text-red-300 hover:text-red-500 font-bold px-1" title="Zurückstellen">✕</button>
                                        </li>
                                    </template>

                                    {{-- NEU HINZUGEFÜGTE POSITIONEN (Noch nicht gespeichert) --}}
                                    <template x-for="(newItem, index) in editForm.newPositions" :key="'new-'+index">
                                        <li class="flex items-center gap-2 text-green-700 font-bold bg-green-50 px-1 py-1 rounded mb-1 border border-green-200">
                                            <span class="text-green-500">+</span><span x-text="newItem.label"></span>
                                            <button type="button" @click="
                                                if(newItem.value.includes('damage_id:')) {
                                                    editForm.suggestions.push(newItem);
                                                }
                                                editForm.newPositions.splice(index, 1);
                                            " class="text-red-400 hover:text-red-600 ml-auto px-1 font-bold">✕</button>
                                        </li>
                                    </template>
                                </ul>
                                <div class="flex gap-2 pt-2 border-t border-gray-200">
                                    <input type="text" x-model="tempInput" @keydown.enter.prevent="if(tempInput) { editForm.newPositions.push({value: tempInput, label: tempInput}); tempInput = ''; }" placeholder="+ Position..." class="w-full rounded border-gray-300 text-xs">
                                    <button type="button" @click="if(tempInput) { editForm.newPositions.push({value: tempInput, label: tempInput}); tempInput = ''; }" class="bg-blue-100 text-blue-700 px-3 rounded text-xs font-bold border border-blue-200">+</button>
                                </div>
                            </div>

                            {{-- VORSCHLÄGE --}}
                            <div x-show="editForm.suggestions.length > 0" class="bg-yellow-50 p-2 rounded border border-yellow-200 max-h-32 overflow-y-auto" x-cloak>
                                <label class="block text-[10px] font-bold uppercase text-yellow-700 mb-1">Vorschläge & Offene Mängel</label>
                                <div class="flex flex-col gap-1">
                                    <template x-for="(sugg, index) in editForm.suggestions" :key="'sugg-'+index">
                                        <button type="button" @click="
                                            editForm.newPositions.push({value: sugg.value, label: sugg.label, info: sugg.info});
                                            editForm.suggestions.splice(index, 1);
                                        " class="text-[10px] text-left border bg-white hover:bg-yellow-100 p-1.5 rounded w-full flex justify-between items-center group shadow-sm transition">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-800" x-text="sugg.label"></span>
                                                <span class="text-[9px] text-gray-500" x-text="sugg.info"></span>
                                            </div>
                                            <span class="text-blue-500 font-bold opacity-0 group-hover:opacity-100 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">+</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div x-show="editForm.status === 'resolved'" class="bg-green-50 p-3 rounded border border-green-200 space-y-3">
                                <div><label class="block text-[10px] font-bold uppercase text-green-700">Fertig am</label><input type="datetime-local" name="actual_end_time" x-model="editForm.actual_end" class="w-full rounded border-green-300 text-xs"></div>
                                <template x-if="editForm.services.includes('HU')"><div><label class="block text-[10px] font-bold uppercase text-green-700">Neue HU vom:</label><input type="date" name="update_hu_date" class="w-full rounded border-green-300 text-xs"></div></template>
                                <template x-if="editForm.services.includes('UVV')"><div><label class="block text-[10px] font-bold uppercase text-green-700">Neue UVV vom:</label><input type="date" name="update_uvv_date" class="w-full rounded border-green-300 text-xs"></div></template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center">
                        <div x-show="modalMode === 'edit'"><button type="button" @click="if(confirm('Termin wirklich löschen?')) { document.getElementById('delete-form').submit(); }" class="text-red-500 text-xs font-bold hover:underline flex items-center gap-1">🗑️ Stornieren</button></div>
                        <div x-show="modalMode === 'create'"></div>
                        <div class="flex gap-2"><button type="button" @click="showEditModal = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded text-sm">Abbrechen</button><button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 text-sm shadow">Speichern</button></div>
                    </div>
                </form>
            </div>
        </div>

        <form id="delete-form" method="POST" :action="deleteUrl" class="hidden">@csrf @method('DELETE')</form>
    </div>
</x-app-layout>
