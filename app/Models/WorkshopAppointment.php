<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopAppointment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'planned_end_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'services' => 'array',
        'is_transport_organized' => 'boolean',
        'is_pickup_needed' => 'boolean',
        'has_rental_car' => 'boolean', // WICHTIG: Typumwandlung für Checkbox
        'transport_start_time' => 'datetime',
        'transport_end_time' => 'datetime',
    ];

    protected $fillable = [
        'vehicle_id',
        'service_provider_id',
        'workshop_name',
        'start_time',
        'planned_end_time',
        'actual_end_time',
        'status',
        'services',
        'notes', // <--- WICHTIG: Das Textfeld muss erlaubt sein!

        // Logistik Hinfahrt
        'is_transport_organized',
        'transport_method',
        'transport_driver_name',
        'transport_driver_status',
        'transport_billing_department',
        'transport_start_time',
        'has_rental_car', // <--- WICHTIG: Der Leihwagen muss erlaubt sein!

        // Logistik Rückfahrt
        'is_pickup_needed',
        'pickup_method', // <--- WICHTIG: Rückfahrt-Methode
        'pickup_driver_name',
        'pickup_driver_status',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function damages()
    {
        return $this->hasMany(Damage::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
