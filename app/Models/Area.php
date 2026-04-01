<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Der übergeordnete Bereich (falls es ein Unterbereich ist)
    public function parent()
    {
        return $this->belongsTo(Area::class, 'parent_id');
    }

    // Die Unterbereiche (falls dieser Bereich aufgeteilt ist)
    public function children()
    {
        return $this->hasMany(Area::class, 'parent_id');
    }

    // Die direkten Kostenstellen in diesem Bereich
    public function costCenters()
    {
        return $this->hasMany(CostCenter::class);
    }
}
