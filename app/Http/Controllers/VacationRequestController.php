<?php

namespace App\Http\Controllers;

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
        // The create() method is used within the Laravel app. The others are consumed by the API.
        $this->middleware('auth:api', ['except' => ['create']]);
        $this->middleware('auth', ['only' => ['index', 'create']]);
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();
        $restrictedDates = DB::table('restricted_dates')
            ->select('date')
            ->whereYear('date', '=', date('Y'))
            ->get();

        // Requests for the year (user will not be able to select these)
        $previousRequests = DB::table('vacation_requests')
            ->where('requested_by', $user->id)
            ->whereYear('date_requested', '=', date('Y'))
            ->get();

        return view('vacationrequest', ['user' => $user, 'restrictedDates' => $restrictedDates, 'previousRequests' => $previousRequests]);
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
        $requestedDates = $request->post('requestedDates');
        $response = [];
        foreach($requestedDates as $requestedDate) {
            // https://laravel.com/docs/6.x/eloquent#other-creation-methods
            $newRequest = VacationRequest::firstOrCreate(
                ['requested_by' => $userID, 'date_requested' => '2019-11-07'],
                ['decision' => 'pending']
            );
            if($newRequest->wasRecentlyCreated){
                // New request
                $response[] = $requestedDate . ' has been requested.';
                // TODO: Make a log of this event
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
