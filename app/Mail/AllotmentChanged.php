<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AllotmentChanged extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $origVacationDays;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $origVacationDays)
    {
        $this->user = $user;
        $this->origVacationDays = $origVacationDays;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('[USA Manager Portal] Vacation Days Adjustment')
            ->markdown('emails.allotment')
            ->with(['user' => $this->user, 'origVacationDays' => $this->origVacationDays]);
    }
}
