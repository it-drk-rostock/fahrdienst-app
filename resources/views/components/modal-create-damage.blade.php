@props(['vehicle'])

<div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm px-4" style="display: none;" x-cloak>
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl p-6 max-h-[90vh] overflow-y-auto" @click.away="showCreateModal = false">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 class="text-lg font-bold text-gray-800">Neue Mängel erfassen</h3>
            <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form action="{{ route('vehicles.damage.store', $vehicle) }}" method="POST" x-data="{ rows: [{ title: '', desc: '', type: 'wear', severity: 'medium' }] }">
            @csrf
            <div class="space-y-4">
                <div class="hidden md:grid grid-cols-12 gap-2 text-xs font-bold text-gray-500 uppercase border-b pb-1">
                    <div class="col-span-3">Bauteil / Titel</div><div class="col-span-4">Beschreibung</div><div class="col-span-2">Art</div><div class="col-span-2">Rep.-Grad</div><div class="col-span-1"></div>
                </div>

                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-start bg-gray-50 p-3 rounded border border-gray-200 mb-2 md:mb-0">
                        <div class="md:col-span-3"><label class="md:hidden text-xs font-bold text-gray-500 uppercase">Titel</label><input type="text" :name="'positions['+index+'][title]'" x-model="row.title" required class="w-full rounded border-gray-300 text-sm" placeholder="Titel..."></div>
                        <div class="md:col-span-4"><label class="md:hidden text-xs font-bold text-gray-500 uppercase">Beschreibung</label><textarea :name="'positions['+index+'][description]'" x-model="row.desc" rows="1" class="w-full rounded border-gray-300 text-sm" placeholder="Details..."></textarea></div>
                        <div class="md:col-span-2"><label class="md:hidden text-xs font-bold text-gray-500 uppercase">Art</label><select :name="'positions['+index+'][damage_type]'" x-model="row.type" class="w-full rounded border-gray-300 text-xs"><option value="wear">Verschleiß/Allg.</option><option value="found">Vorfinde</option><option value="accident_own">Unfall (Eigen)</option><option value="accident_other">Unfall (Gegner)</option></select></div>
                        <div class="md:col-span-2"><label class="md:hidden text-xs font-bold text-gray-500 uppercase">Schwere</label><select :name="'positions['+index+'][severity]'" x-model="row.severity" class="w-full rounded border-gray-300 text-xs font-bold" :class="{'text-red-600': ['high','critical'].includes(row.severity)}"><option value="low">Leicht</option><option value="medium">Mittel</option><option value="high">Hoch</option><option value="critical">KRITISCH</option></select></div>
                        <div class="md:col-span-1 text-right mt-2 md:mt-0"><button type="button" @click="rows.length > 1 ? rows.splice(index, 1) : null" class="text-red-400 font-bold px-2 border border-red-200 rounded hover:bg-red-50">Löschen</button></div>
                    </div>
                </template>
            </div>
            <button type="button" @click="rows.push({ title: '', desc: '', type: 'wear', severity: 'medium' })" class="mt-4 text-xs font-bold text-blue-600 hover:underline flex items-center gap-1"><span class="bg-blue-100 px-1 rounded">+</span> Zeile hinzufügen</button>
            <div class="mt-6 pt-4 border-t flex justify-between items-center flex-wrap gap-2">
                <div class="text-xs text-gray-400 w-full md:w-auto">Bilder-Upload im nächsten Schritt.</div>
                <div class="flex gap-2 w-full md:w-auto justify-end"><button type="button" @click="showCreateModal = false" class="px-3 py-2 text-gray-500 text-xs font-bold">Abbrechen</button><button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded text-xs font-bold hover:bg-rose-700">Alle Speichern</button></div>
            </div>
        </form>
    </div>
</div>
