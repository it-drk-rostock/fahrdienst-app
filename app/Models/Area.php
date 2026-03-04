<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


  class Area extends Model
{
    protected $guarded = [];

    // Ein Bereich hat viele Kostenstellen
    public function costCenters()
    {
        return $this->hasMany(CostCenter::class);
    }
}
