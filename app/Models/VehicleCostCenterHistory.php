<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCostCenterHistory extends Model
{
    use HasFactory;

    // Erlaubt das Massen-Zuweisen von Attributen (wichtig für create())
    protected $guarded = [];

    // Damit Laravel weiß, dass diese Felder Datumswerte sind (Carbon)
    protected $casts = [
        'assigned_from' => 'date',
        'assigned_until' => 'date',
    ];

    /**
     * Rückverknüpfung zum Fahrzeug
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Verknüpfung zur Kostenstelle
     * Das ist die Methode, die gefehlt hat!
     */
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
}
