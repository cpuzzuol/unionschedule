<?php


namespace App\Helpers;


use App\Mail\VacationRequestUpdate;
use App\VacationRequest;
use App\Helpers\VacationLogger;
use Illuminate\Support\Facades\Mail;

class BulkRequestStatusHandler{
    private $status;
    private $date;

    public function __construct($status, $date){
        $this->status = $status;
        $this->date = $date;
    }

    /**
     * On a given date, update the status of all requests and notify affected users (if necessary)
     * @param $sendEmail
     * @return mixed
     */
    public function bulkStatusUpdateOnDate($sendEmail = false) {
        $now = date('Y-m-d H:i:s');

        if($this->status == 'denyAll') {
            $vacationRequests = VacationRequest::where([['date_requested', $this->date]])->get();
        } else if ($this->status == 'denyPending' || $this->status == 'approvePending') {
            $vacationRequests = VacationRequest::where([['date_requested', $this->date], ['decision', 'pending']])->get();
        } else {
            return 'invalid status'; // invalid status passed
        }

        if($this->status == 'denyAll' || $this->status == 'denyPending') {
            $vstatus = 'denied';
        } else {
            $vstatus = 'approved';
        }

        foreach($vacationRequests as $vacationRequest) {
            $origStatus = $vacationRequest->getOriginal('decision');
            $vacationRequest->decision = $vstatus;
            $vacationRequest->decision_date = $now;
            $vacationRequest->decision_by = auth()->user()->id;

            $requester = $vacationRequest->requester;
            // give the requester a vacation day back since they're pending or previously-approved request is now denied
            if(($origStatus == 'pending' || $origStatus == 'approved') && $vstatus == 'denied') {
                $requester->vacation_days = $requester->vacation_days + 1;
                $requester->save();
            }

            if($vacationRequest->save()) {
                $log = new VacationLogger();
                $log->logAction($vacationRequest->id, "Changed from $origStatus to $vstatus");

                // Email all affected users (if necessary)
                if($sendEmail) {
                    $dateFormat = date('D, M j, Y', strtotime($vacationRequest->date_requested));
                    Mail::to($requester->email)->send(new VacationRequestUpdate($requester, $dateFormat, $vstatus));
                }
            }
        }
    }
}
