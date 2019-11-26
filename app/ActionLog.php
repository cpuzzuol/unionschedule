<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    protected $fillable = [
        'affected_user', 'description', 'action_by'
    ];

    /**
     * Get the affected user to which this log item belongs
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo('App\User' ,'affected_user');
    }

    public function actionBy() {
        return $this->hasOne('App\User', 'id', 'action_by');
    }
}
