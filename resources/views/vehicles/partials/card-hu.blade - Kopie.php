<div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100 h-full">
    <h4 class="text-xs font-bold text-blue-600 uppercase border-b pb-2 mb-4">HU & Gesetzliche Prüfungen</h4>

    <div class="mb-4 bg-gray-50 p-3 rounded text-center border border-gray-200">
        <span class="block text-[10px] text-gray-500 uppercase font-bold">Nächste HU</span>
        @php
            $huColor = 'text-gray-800';
            if ($vehicle->next_hu_date && $vehicle->next_hu_date->isPast()) {
                $huColor = 'text-red-600';
            }
        @endphp
        <span class="text-xl font-bold {{ $huColor }}">
            {{ $vehicle->next_hu_date ? $vehicle->next_hu_date->format('d.m.Y') : '??.??.????' }}
        </span>
    </div>

    <div class="min-h-[100px] flex flex-col justify-between">
        @if($vehicle->huReports->isEmpty())
            <p class="text-gray-400 italic text-sm py-4 text-center">Keine Berichte vorhanden.</p>
        @else
            <ul class="space-y-3 mb-4">
                @foreach($vehicle->huReports->take(3) as $report)
                    <li class="text-sm border-b border-gray-100 pb-2 last:border-0">
                        <div class="flex justify-between items-start">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800">{{ $report->inspection_date->format('d.m.Y') }}</span>
                                <span class="text-[10px] text-gray-500 uppercase">{{ $report->organization }}</span>
                            </div>
                            <div>
                                @if($report->result == 'pass')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-[10px] font-bold border border-green-200">OK</span>
                                @elseif($report->result == 'minor')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-[10px] font-bold border border-yellow-200">GM</span>
                                @elseif($report->result == 'major')
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-[10px] font-bold border border-red-200">EM</span>
                                @elseif($report->result == 'unsafe')
                                    <span class="bg-red-600 text-white px-2 py-1 rounded-full text-[10px] font-bold border border-red-800">VU</span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="text-center mt-2 border-t pt-2">
            <button type="button" @click="showHuModal = true" class="text-[10px] text-blue-600 font-bold uppercase hover:underline">
                + Bericht erfassen
            </button>
        </div>
    </div>
</div>
