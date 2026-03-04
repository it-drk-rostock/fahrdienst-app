@php
    $isResolved = $event->status == 'resolved';
    $borderClass = match($event->status) { 'active' => 'border-orange-500', 'resolved' => 'border-green-500', default => 'border-blue-400' };
    $bgColor = $isResolved ? '#f3f4f6' : ($event->calendar_color ?? '#e5e7eb');
    $textColor = $isResolved ? 'text-gray-400 line-through' : 'text-gray-900 font-bold';

    // Items
    $items = []; foreach($event->damages as $d) $items[] = $d->title;
    if(!empty($event->services)) foreach($event->services as $s) $items[] = $s;
    $typeString = implode(', ', array_unique($items)) ?: 'Termin';
@endphp

<div class="text-[10px] leading-tight px-2 py-1.5 rounded shadow-sm cursor-pointer transition hover:opacity-80 truncate border-l-8 {{ $borderClass }} {{ $textColor }}"
     style="background-color: {{ $bgColor }};"
     draggable="true"
     x-on:dragstart="dragStart($event, {{ $event->id }})"
     @click="editAppointment({
         id: {{ $event->id }},
         vehicle_id: {{ $event->vehicle_id }},
         title: '{{ $event->vehicle->license_plate ?? 'Unbekannt' }}',
         workshop: '{{ $event->serviceProvider->name ?? $event->workshop_name }}',
         start: '{{ $event->start_time }}',
         end: '{{ $event->planned_end_time }}',
         status: '{{ $event->status }}',
         actual_end: '{{ $event->actual_end_time }}',
         notes: '{{ str_replace(["\r", "\n"], " ", addslashes($event->notes ?? "")) }}',
         items: {{ json_encode($event->damages->map(fn($d) => ['type'=>'damage','id'=>$d->id,'label'=>$d->title])->merge(collect($event->services ?? [])->map(fn($s)=>['type'=>'service','id'=>$s,'label'=>$s]))) }},
         transport_organized: {{ $event->is_transport_organized ? 'true' : 'false' }},
         transport_method: '{{ $event->transport_method }}',
         has_rental_car: {{ $event->has_rental_car ? 'true' : 'false' }},
         driver_name: '{{ $event->transport_driver_name }}',
         driver_status: '{{ $event->transport_driver_status }}',
         pickup_needed: {{ $event->is_pickup_needed ? 'true' : 'false' }},
         pickup_method: '{{ $event->pickup_method }}',
         pickup_name: '{{ $event->pickup_driver_name }}',
         pickup_status: '{{ $event->pickup_driver_status }}',
         billing_dept: '{{ $event->transport_billing_department }}',
         vehicle_cost_center: '{{ $event->vehicle->costCenter->code ?? '' }}',
         suggestions: []
     })">
    <div class="flex justify-between font-bold">
        <span>{{ $event->vehicle->license_plate ?? '?' }}</span>
        @if($event->has_rental_car) 🚗 @endif
    </div>
    <div class="truncate opacity-80 font-normal">{{ $typeString }}</div>
</div>
