<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerAudit extends Model
{
    // Wir erlauben das Speichern aller Felder, die wir validiert haben
    protected $guarded = [];

    // Das Datum soll immer als echtes Datum behandelt werden (für .format('d.m.Y'))
    protected $casts = [
        'checked_at' => 'date',
    ];

    // Rückverlinkung: Ein Audit gehört zu einem Fahrzeug
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
