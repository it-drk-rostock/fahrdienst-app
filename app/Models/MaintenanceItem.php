<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceItem extends Model
{
    /**
     * ==========================================
     * KONFIGURATION
     * ==========================================
     */

    // Welche Felder dürfen massenhaft befüllt werden?
    protected $fillable = [
        'maintenance_template_id', // Fremdschlüssel zum Haupt-Plan
        'task_name',               // Name der Tätigkeit (z.B. Ölwechsel)
        'interval_km',             // Fälligkeit nach Kilometern
        'interval_months'          // Fälligkeit nach Zeit
    ];

    /**
     * ==========================================
     * RELATIONEN (VERKNÜPFUNGEN)
     * ==========================================
     */

    // Rückverlinkung: Ein Item gehört zu genau einem Template
    public function maintenanceTemplate()
    {
        return $this->belongsTo(MaintenanceTemplate::class);
    }
}
