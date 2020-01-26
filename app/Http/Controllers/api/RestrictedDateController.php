<?php

namespace App\Http\Controllers\api;

use App\ActionLog;
use App\Helpers\BulkRequestStatusHandler;
use App\Helpers\VacationLogger;
use App\RestrictedDate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RestrictedDateController extends Controller {

    public $successStatus = 200;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $restrictedDates = RestrictedDate::where('date', '>=', date('Y-m-d'))->get();
        return response()->json($restrictedDates);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $dates = $request->input('newRestrictedDates');
        $bulkActions = $request->input('bulkActions');
        $notifyOfBulk = $request->input('notifyOfBulk');

        // Make the new restrictions
        foreach($dates as $date) {
            // Make sure date is unique
            $existingDate = RestrictedDate::where('date', $date)->get();
            if(count($existingDate) == 0) {
                // Either save all of the transactions, or save none of them (e.g. if one is unsuccessful)
                try{
                    DB::beginTransaction();
                    $newRestriction = new RestrictedDate();
                    $newRestriction->date = $date;
                    $newRestriction->save();

                    // Handle bulk actions on existing PTO requests for this date
                    if($bulkActions){
                        $bulkHandler = new BulkRequestStatusHandler($bulkActions, $date);
                        $bulkHandler->bulkStatusUpdateOnDate($notifyOfBulk);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }
        }
        return response()->json(['dates' => $dates, 'bulk' => $bulkActions], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $date (Y-m-d)
     * @return \Illuminate\Http\Response
     */
    public function destroy($date){
        $restrictedDate = RestrictedDate::where('date', $date)->delete();
        if(!$restrictedDate){
            return response()->json('No matching restricted found', 404);
        }
        return response()->json('Restricted date deleted', 200);
    }
}

