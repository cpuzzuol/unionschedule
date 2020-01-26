<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VacationRequestUpdate extends Mailable
{
    use Queueable, SerializesModels;

    private $requester;
    private $requestDate;
    private $requestStatus;
    private $noteAttachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($requester, $requestDate, $requestStatus, $noteAttachment = '')
    {
        $this->requester = $requester;
        $this->requestDate = $requestDate;
        $this->requestStatus = $requestStatus;
        $this->noteAttachment = $noteAttachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('[USA Manager Portal] PTO Request Update')
            ->markdown('emails.vacationrequestupdate')
            ->with(['requester' => $this->requester, 'requestDate' => $this->requestDate, 'requestStatus' => $this->requestStatus, 'noteAttachment' => $this->noteAttachment]);
    }
}
