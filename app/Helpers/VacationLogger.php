<?php


namespace App\Helpers;


use App\ActionLog;
use App\RequestLog;

class VacationLogger{
    public function __construct(){

    }

    public function logAction($requestID, $description = '') {
        $log = new RequestLog();
        $log->request_id = $requestID;
        $log->description = $description;
        $log->action_by = auth()->user()->id;
        $log->save();
    }

    public function logVacationDayAllotmentChange($affectedUser, $origDays, $newDays) {
        $log = new ActionLog();
        $log->affected_user = $affectedUser;
        $log->description = "Changed vacation days from $origDays to $newDays";
        $log->action_by = auth()->user()->id;
        return $log->save();
    }
}
