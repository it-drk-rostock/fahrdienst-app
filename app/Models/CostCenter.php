<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model {
    protected $guarded = [];

    public function area() {
        return $this->belongsTo(Area::class);
    }

    // Hilfsattribut für Dropdowns: "Logistik | Lager 4050"
    public function getFullNameAttribute() {
        return ($this->area->name ?? '???') . ' | ' . $this->name . ' (' . $this->code . ')';
    }
}
