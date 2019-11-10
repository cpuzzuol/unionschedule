<?php

namespace App\Http\Controllers\api;

use App\Helpers\VacationLoggers;
use App\Http\Controllers\Controller;
use App\RequestLog;
use App\VacationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VacationRequestController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Get vacation requests for a specific date
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function requestsByDate(Request $request, $date) {
        $requests = VacationRequest::with('requester')->where('date_requested', $date)->get();
        return response()->json($requests);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
//        $user = auth()->user();
//        $response['restrictedDates'] = DB::table('restricted_dates')
//            ->select('date')
//            ->whereYear('date', '=', date('Y'))
//            ->get();
//
//        // Requests for the year (user will not be able to select these)
//        $response['previousRequests'] = DB::table('vacation_requests')
//            ->where('requested_by', $user->id)
//            ->whereYear('date_requested', '=', date('Y'))
//            ->get();
//
//        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userID = $request->post('userID');
        $requestedDates = $request->input('requestedDates');
        $response = [];
        foreach($requestedDates as $requestedDate) {
            // https://laravel.com/docs/6.x/eloquent#other-creation-methods
            $newRequest = VacationRequest::firstOrCreate(
                ['requested_by' => $userID, 'date_requested' => $requestedDate],
                ['decision' => 'pending']
            );
            if($newRequest->wasRecentlyCreated){
                // New request
                $response[] = $requestedDate . ' has been requested.';
                // Make a log of this event
                $logger = new VacationLogger();
                $logger->logAction($newRequest->id);
            } else {
                $response[] = $requestedDate . ' was already requested.';
            }
        }
        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
