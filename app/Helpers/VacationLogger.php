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
        if($log->save()) {
            return $log->description;
        }
        return 'Error saving log.';
    }

    public function logVacationDayAllotmentChange($affectedUser, $origDays, $newDays) {
        $log = new ActionLog();
        $log->affected_user = $affectedUser;
        $log->description = "Changed PTO days from $origDays to $newDays";
        $log->action_by = auth()->user()->id;
        return $log->save();
    }
}
