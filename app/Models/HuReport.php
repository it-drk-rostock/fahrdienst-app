<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HuReport extends Model
{
    use HasFactory;

    protected $guarded = []; // Erlaubt Speichern

    protected $casts = [
        'inspection_date' => 'date',
        'reinspection_deadline' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
