<?php

namespace App\Http\Controllers\api;

use App\Helpers\VacationLoggers;
use App\Http\Controllers\Controller;
use App\RequestLog;
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
    public function homedata()
    {
        $response['restrictedDates'] = DB::table('restricted_dates')
            ->select('date')
            ->whereYear('date', '=', date('Y'))
            ->get();
        // Get outstanding requests in the FUTURE only and get user info along with it
        $response['outstandingRequests'] = VacationRequest::with('requester')->where([
            ['decision', '=', 'pending'],
            ['date_requested', '>=', date('Y-m-d')]
        ])->orderBy('date_requested', 'ASC')->get();

        return response()->json($response);
    }
}
