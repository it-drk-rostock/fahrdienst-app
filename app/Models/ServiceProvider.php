<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    protected $guarded = [];

    public function appointments()
    {
        return $this->hasMany(WorkshopAppointment::class);
    }
}
