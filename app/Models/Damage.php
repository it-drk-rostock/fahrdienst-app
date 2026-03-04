<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Damage extends Model
{
    use HasFactory;

    protected $guarded = [];

    // DIESER TEIL HAT GEFEHLT:
    protected $casts = [
        'images' => 'array',        // Wandelt JSON automatisch in Array um
        'resolved_at' => 'date',    // Datum formatieren
        'insurance_cover' => 'boolean',
    ];

    // Relation zum Fahrzeug
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relation zum Benutzer (Melder)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation zum Werkstatt-Termin
    public function workshopAppointment()
    {
        return $this->belongsTo(WorkshopAppointment::class);
    }
}
