<?php

namespace App;

use App\Notifications\ResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use CanResetPassword;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'vacation_days', 'is_admin', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'is_admin'
    ];

    /**
     * Overrides the standard password reset notification.
     * TUTORIAL: https://stackoverflow.com/questions/40574001/how-to-change-reset-password-email-subject-in-laravel
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function vacationRequests() {
        return $this->hasMany('App\VacationRequest', 'requested_by');
    }

    public function actionLogs() {
        return $this->hasMany('App\ActionLogs', 'affected_user');
    }
}
