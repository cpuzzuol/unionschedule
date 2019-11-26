<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'request_id', 'description', 'action_by'
    ];

    /**
     * Get the request to which this log item belongs
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request() {
        return $this->belongsTo('App\VacationRequest' ,'requested_id');
    }

    public function actionBy() {
        return $this->hasOne('App\User', 'id', 'action_by')->select(['first_name', 'last_name', 'id']);
    }
}
