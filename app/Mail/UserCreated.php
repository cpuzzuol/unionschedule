<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreated extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $pw;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $pw)
    {
        $this->user = $user;
        $this->pw = $pw;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('[USA Manager Portal] Account Created')
            ->markdown('emails.usercreated')
            ->with(['user' => $this->user, 'pw' => $this->pw]);
    }
}
