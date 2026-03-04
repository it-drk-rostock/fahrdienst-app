@php
    $isVirtual = $event->status === 'virtual';
    $isResolved = $event->status == 'resolved';

    $currentDayStart = $dayDate->copy()->setTime(6, 0);
    $currentDayEnd = $dayDate->copy()->setTime(18, 0);
    $visualStart = $event->start_time->lt($currentDayStart) ? $currentDayStart : $event->start_time;
    $visualEnd = $event->planned_end_time->gt($currentDayEnd) ? $currentDayEnd : $event->planned_end_time;
    $startHour = (float)$visualStart->format('H') + ($visualStart->format('i')/60);
    $endHour = (float)$visualEnd->format('H') + ($visualEnd->format('i')/60);
    $displayStart = max(6, $startHour);
    $displayEnd = min(18, $endHour);
    $top = (($displayStart - 6) / 12) * 100;
    $height = (($displayEnd - $displayStart) / 12) * 100;
    if ($height < 2) $height = 2;

    if ($isVirtual) {
        $bgColor = '#fff1f2';
        $borderClass = 'border-rose-400 border-dashed border-2';
        $textColor = 'text-rose-700 font-bold italic';
        $typeString = '⚠️ ' . $event->virtual_label;
    } else {
        $bgColor = $isResolved ? '#f3f4f6' : ($event->calendar_color ?? '#e5e7eb');
        $borderClass = match($event->status) { 'active' => 'border-orange-500 border-l-4', 'resolved' => 'border-green-500 border-l-4', default => 'border-blue-400 border-l-4' };
        $textColor = $isResolved ? 'text-gray-400 line-through' : 'text-gray-900 font-bold';
        $items = $event->damages->toBase()->map(fn($d) => $d->title)->merge(collect($event->services ?? []));
        $typeString = $items->unique()->implode(', ') ?: 'Termin';
    }

    if (!$isVirtual) {
        // --- VORSCHLÄGE BERECHNEN ---
        $suggestions = [];
        if($event->vehicle && $event->vehicle->damages) {
            foreach($event->vehicle->damages as $d) {
                if($d->status == 'open') {
                    $suggestions[] = [
                        'value' => 'damage_id:'.$d->id,
                        'label' => '⚠️ '.$d->title,
                        'info' => 'Gemeldet: '.$d->created_at->format('d.m.')
                    ];
                }
            }
        }

        $eventData = [
            'id' => $event->id, 'vehicle_id' => $event->vehicle_id, 'title' => $event->vehicle->license_plate ?? 'Unbekannt', 'workshop' => $event->serviceProvider->name ?? $event->workshop_name, 'start' => $event->start_time->format('Y-m-d H:i'), 'end' => $event->planned_end_time->format('Y-m-d H:i'), 'status' => $event->status, 'actual_end' => $event->actual_end_time ? $event->actual_end_time->format('Y-m-d H:i') : null, 'notes' => $event->notes, 'items' => $event->damages->toBase()->map(fn($d) => ['type'=>'damage','id'=>$d->id,'label'=>$d->title])->merge(collect($event->services ?? [])->map(fn($s)=>['type'=>'service','id'=>$s,'label'=>$s]))->values(), 'transport_organized' => $event->is_transport_organized, 'transport_method' => $event->transport_method, 'has_rental_car' => $event->has_rental_car, 'driver_name' => $event->transport_driver_name, 'driver_status' => $event->transport_driver_status, 'pickup_needed' => $event->is_pickup_needed, 'pickup_method' => $event->pickup_method, 'pickup_name' => $event->pickup_driver_name, 'pickup_status' => $event->pickup_driver_status, 'billing_dept' => $event->transport_billing_department, 'vehicle_cost_center' => $event->vehicle->costCenter->code ?? '',
            'suggestions' => $suggestions // <--- Hier übergeben!
        ];
    }
@endphp

<div class="absolute z-10 overflow-hidden rounded shadow-sm hover:z-20 hover:brightness-95 transition-all cursor-pointer px-1 py-0.5 {{ $borderClass }} {{ $textColor }}"
     style="top: {{ $top }}%; height: {{ $height }}%; left: {{ $left }}%; width: {{ $width }}%;
            background-color: {{ $bgColor }};"
     @if($isVirtual)
        @click="createAppointment('{{ $event->start_time->format('Y-m-d') }}', '08:00', {{ $event->vehicle_id }}, '{{ $event->virtual_label }}', '{{ $event->vehicle->license_plate ?? '' }}')"
     @else
        @click="editAppointment({{ json_encode($eventData) }})"
     @endif
     >
    <div class="flex justify-between items-start font-bold">
        <span class="text-[9px] leading-tight mr-1">{{ $event->start_time->format('H:i') }} {{ $event->vehicle->license_plate ?? '?' }}</span>

        @if(!$isVirtual)
            <div class="flex flex-col gap-0.5 items-end">
                @if($event->has_rental_car)
                    <span class="px-1 rounded-sm bg-yellow-300 text-black text-[8px] font-bold border border-yellow-400 leading-none py-0.5">LW</span>
                @endif
                @if($event->transport_method == 'replacement')
                    <span class="px-1 rounded-sm bg-purple-200 text-purple-900 text-[8px] font-bold border border-purple-300 leading-none py-0.5">HOLT</span>
                @endif
                @if($event->pickup_method == 'workshop')
                    <span class="px-1 rounded-sm bg-cyan-200 text-cyan-900 text-[8px] font-bold border border-cyan-300 leading-none py-0.5">BRINGT</span>
                @endif
            </div>
        @endif
    </div>

    @if($height > 5)
        <div class="text-[8px] truncate opacity-80 mt-0.5">{{ $typeString }}</div>

        @if(!$isVirtual && !$isResolved)
            @if($event->transport_driver_status == 'search_needed' && $event->transport_method != 'replacement')
                <div class="text-[8px] bg-red-100 text-red-700 font-bold px-1 rounded border border-red-300 text-center mt-0.5 animate-pulse shadow-sm">
                    FAHRER HIN?
                </div>
            @endif
            @if($event->pickup_driver_status == 'search_needed' && $event->pickup_method != 'workshop')
                <div class="text-[8px] bg-orange-100 text-orange-700 font-bold px-1 rounded border border-orange-300 text-center mt-0.5 animate-pulse shadow-sm">
                    ABHOLER?
                </div>
            @endif
        @endif
    @endif
</div>
