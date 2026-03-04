<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTemplate extends Model
{
    protected $fillable = ['manufacturer', 'model_series', 'interval_km', 'interval_months', 'warranty_months', 'is_confirmed'];

    // DIESE FUNKTION HAT WAHRSCHEINLICH GEFEHLT:
    public function items()
    {
        return $this->hasMany(MaintenanceItem::class);
    }
}
