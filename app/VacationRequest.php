<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VacationRequest extends Model
{
    protected $fillable = [
      'requested_by', 'date_requested', 'decision', 'decision_date', 'decision_by'
    ];

    /**
     * Get the user to which this PTO request belongs
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester() {
        return $this->belongsTo('App\User', 'requested_by');
    }

    public function requesterBasicInfo() {
        return $this->belongsTo('App\User', 'requested_by')->select(['first_name', 'last_name', 'email']);
    }

    public function logs() {
        return $this->hasMany('App\RequestLog', 'request_id')->orderBy('created_at', 'DESC');
    }
}
