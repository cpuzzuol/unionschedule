<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RestrictedDate extends Model
{
    protected $fillable = ['date'];

    public function vacationRequests() {
        return $this->hasMany('App\VacationRequest', 'date_requested', 'date');
    }
}
