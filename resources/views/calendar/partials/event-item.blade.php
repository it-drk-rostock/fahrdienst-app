@php
    $isVirtual = $event->status === 'virtual';
    $isResolved = $event->status == 'resolved';

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
                // Zeige nur offene Mängel an
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

<div class="text-[10px] leading-tight px-1 py-1 rounded shadow-sm cursor-pointer transition hover:brightness-90 truncate {{ $borderClass }} {{ $textColor }}"
     style="background-color: {{ $bgColor }};"
     @if($isVirtual)
        @click="createAppointment('{{ $event->start_time->format('Y-m-d') }}', '08:00', {{ $event->vehicle_id }}, '{{ $event->virtual_label }}', '{{ $event->vehicle->license_plate ?? '' }}')"
     @else
        @click="editAppointment({{ json_encode($eventData) }})"
     @endif
     >

    <div class="flex flex-wrap items-center gap-1 mb-0.5">
        <span class="font-bold">{{ $event->vehicle->license_plate ?? '?' }}</span>

        @if(!$isVirtual)
            @if($event->has_rental_car)
                <span class="px-1 rounded-sm bg-yellow-300 text-black text-[9px] font-bold border border-yellow-400 leading-none">LW</span>
            @endif
            @if($event->transport_method == 'replacement')
                <span class="px-1 rounded-sm bg-purple-200 text-purple-900 text-[9px] font-bold border border-purple-300 leading-none">HOLT</span>
            @endif
            @if($event->pickup_method == 'workshop')
                <span class="px-1 rounded-sm bg-cyan-200 text-cyan-900 text-[9px] font-bold border border-cyan-300 leading-none">BRINGT</span>
            @endif
        @endif
    </div>

    <div class="truncate opacity-80 font-normal">{{ $typeString }}</div>

    @if(!$isVirtual && !$isResolved)
        @if($event->transport_driver_status == 'search_needed' && $event->transport_method != 'replacement')
            <div class="text-[9px] bg-red-100 text-red-700 font-bold px-1 rounded border border-red-300 text-center mt-0.5 animate-pulse">
                FAHRER HIN?
            </div>
        @endif
        @if($event->pickup_driver_status == 'search_needed' && $event->pickup_method != 'workshop')
            <div class="text-[9px] bg-orange-100 text-orange-700 font-bold px-1 rounded border border-orange-300 text-center mt-0.5 animate-pulse">
                ABHOLER?
            </div>
        @endif
    @endif
</div>
