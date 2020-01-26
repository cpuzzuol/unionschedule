<?php

namespace App\Http\Controllers\api;

use App\Helpers\VacationLoggers;
use App\Http\Controllers\Controller;
use App\RequestLog;
use App\RestrictedDate;
use App\VacationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all the data for the admin home vue component.
     *
     * @return \Illuminate\Http\Response
     */
    public function getHomeData()
    {
        // 'with' vacationRequests is set as a 'hasMany' relationship inside the RestrictedDate model.
        $response['restrictedDates'] = RestrictedDate::select('date')->whereYear('date', '=', date('Y'))->orderBy('date', 'ASC')->get();

        // Requests for the year
        $response['allVacationRequests'] = DB::table('vacation_requests')
            ->select(DB::raw('count(id) AS date_count, date_requested'))
            ->whereYear('date_requested', '=', date('Y'))
            ->where('decision', '<>', 'denied')
            ->groupBy('date_requested')
            ->get();

        // Get outstanding requests for THIS YEAR ONLY and get user info along with it
        $response['outstandingRequests'] = VacationRequest::with('requester')->where([
            ['decision', '=', 'pending'],
            ['date_requested', '>=', date('Y-01-01')]
        ])->orderBy('date_requested', 'ASC')->get();

        return response()->json($response);
    }
}
