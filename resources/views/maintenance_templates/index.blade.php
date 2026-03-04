<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('FMS - Wartungsplan-Gedächtnis & Referenzdaten') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

            @if($unconfirmedTemplates->count() > 0)
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 shadow-sm rounded-r-lg">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider flex items-center">
                            <span class="mr-2">🔍</span> Neue System-Vorschläge zur Verifizierung
                        </h3>
                        <p class="text-xs text-amber-700 mt-1">Diese Modelle wurden neu angelegt. Bitte definieren Sie die herstellerspezifischen Intervalle.</p>
                    </div>

                    <div class="space-y-6">
                        @foreach($unconfirmedTemplates as $template)
                            <div class="bg-white p-6 rounded-lg border border-amber-200 shadow-sm">
                                <form action="{{ route('maintenance-templates.confirm', $template) }}" method="POST">
                                    @csrf
                                    <div class="flex justify-between items-center border-b pb-4 mb-4">
                                        <div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Erkanntes Modell</span>
                                            <h4 class="text-lg font-bold text-gray-800">{{ $template->manufacturer }} {{ $template->model_series }}</h4>
                                        </div>
                                        <div class="text-right">
                                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase">Status: Ungeprüft</span>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <h5 class="text-[10px] font-bold text-gray-500 uppercase">Wartungspunkte & Intervalle festlegen</h5>

                                        <div id="item-container-{{ $template->id }}" class="space-y-3">
                                            <div class="flex flex-wrap md:flex-nowrap gap-3 items-end p-3 bg-gray-50 rounded border border-gray-100">
                                                <div class="flex-1 min-w-[200px]">
                                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Bezeichnung (z.B. Ölservice)</label>
                                                    <input type="text" name="tasks[]" value="Allgemeine Inspektion" class="w-full text-sm border-gray-300 rounded shadow-sm focus:ring-amber-500">
                                                </div>
                                                <div class="w-full md:w-32">
                                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Intervall KM</label>
                                                    <input type="number" name="km[]" value="30000" class="w-full text-sm border-gray-300 rounded shadow-sm">
                                                </div>
                                                <div class="w-full md:w-32">
                                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Intervall Monate</label>
                                                    <input type="number" name="months[]" value="24" class="w-full text-sm border-gray-300 rounded shadow-sm">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                                onclick="addMaintenanceRow({{ $template->id }})"
                                                class="mt-2 text-[10px] font-bold text-blue-600 hover:text-blue-800 uppercase flex items-center">
                                            <span class="text-lg mr-1">+</span> Weiteren Prüfpunkt (z.B. Getriebeöl / Bremsflüssigkeit) hinzufügen
                                        </button>
                                    </div>

                                    <div class="mt-6 pt-6 border-t flex flex-col md:flex-row justify-between items-center gap-4">
                                        <div class="w-full md:w-64">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Basis-Garantie (Monate)</label>
                                            <input type="number" name="warranty_months" value="24" class="w-full text-sm border-gray-300 rounded shadow-sm">
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded font-bold text-xs uppercase shadow transition">
                                                Daten verifizieren & Freigeben
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif


            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Aktive Referenz-Datenbank</h3>
                        <p class="text-xs text-gray-500 mt-1">Diese Pläne dienen als Vorlage für alle Fahrzeuge im Fuhrpark.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Hersteller & Modell</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Hinterlegte Intervalle</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase text-center">Garantie</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase">Aktion</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($confirmedTemplates as $template)
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $template->manufacturer }}</div>
                                        <div class="text-xs text-gray-500">{{ $template->model_series }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            @foreach($template->items as $item)
                                                <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200 w-fit">
                                                    <strong>{{ $item->task_name }}:</strong> {{ number_format($item->interval_km, 0, ',', '.') }} km / {{ $item->interval_months }} Mon.
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-700">{{ $template->warranty_months }} Monate</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                        <button class="text-blue-600 hover:text-blue-900 font-bold uppercase">Bearbeiten</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 italic">
                                        Noch keine verifizierten Wartungspläne vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function addMaintenanceRow(templateId) {
            const container = document.getElementById('item-container-' + templateId);

            // Die erste Zeile als Vorlage nehmen
            const firstRow = container.querySelector('.flex');
            const newRow = firstRow.cloneNode(true);

            // Inputs in der neuen Zeile leeren
            newRow.querySelectorAll('input').forEach(input => {
                input.value = '';
            });

            // Neue Zeile hinzufügen
            container.appendChild(newRow);
        }
    </script>
</x-app-layout>
