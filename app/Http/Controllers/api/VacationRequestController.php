<?php

namespace App\Http\Controllers\api;

use App\Helpers\VacationLogger;
use App\Http\Controllers\Controller;
use App\Mail\VacationRequestUpdate;
use App\RequestLog;
use App\User;
use App\VacationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
     * Get PTO requests for a specific date
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function requestsByDate(Request $request, $date) {
        $requests = VacationRequest::with('requester')->where('date_requested', $date)->get();
        return response()->json($requests);
    }

    /**
     * Get ALL PTO requests for a specific user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function requestsByUser(Request $request, $user) {
        $requests = VacationRequest::with('requester', 'logs', 'logs.actionBy')->where('requested_by', $user)->orderBy('date_requested', 'ASC')->get();
        return response()->json($requests);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userID = auth()->user()->id;
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
                $logger->logAction($newRequest->id, 'PTO request initiated.');

                // Decrement user's vacation allotment
                $user = User::find($userID);
                $user->vacation_days = $user->vacation_days - 1;
                $user->save();
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
        $status = $request->input('status'); // 'approve', 'deny', 'pending'
        $note = $request->input('note');
        $sendEmail = $request->input('sendEmail');

        if($status == '') {
            return response()->json(['error'=>'Invalid params'], 400);
        }

        $vacationRequest = VacationRequest::find($id);
        $requester = $vacationRequest->requester;
        $description = 'Changed from ';
        $origStatus = $vacationRequest->getOriginal('decision');

        if($status == 'approve') {
            // Either save all of the transactions, or save none of them (e.g. if one is unsuccessful)
            try{
                DB::beginTransaction();
                $vacationRequest->decision = 'approved';
                $vacationRequest->decision_date = date('Y-m-d H:i:s');
                $vacationRequest->decision_by = auth()->user()->id;
                $vacationRequest->save();

                if($origStatus == 'denied') {
                    $requester->vacation_days = $requester->vacation_days - 1;
                    $requester->save();
                }
                DB::commit();
                $description .= "$origStatus to approved";
                $emailStatus = 'approved';
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error'=>'Error performing this action.'], 400);
            }
        } else if($status == 'deny') {
            // Either save all of the transactions, or save none of them (e.g. if one is unsuccessful)
            try{
                DB::beginTransaction();
                $vacationRequest->decision = 'denied';
                $vacationRequest->decision_date = date('Y-m-d H:i:s');
                $vacationRequest->decision_by = auth()->user()->id;
                $vacationRequest->save();

                // If the original status was 'approved' or 'pending', add a day to the requester's bank.
                if($origStatus == 'approved' || $origStatus == 'pending') {
                    $requester->vacation_days = $requester->vacation_days + 1;
                    $requester->save();
                }
                DB::commit();
                $description .= "$origStatus to denied";
                $emailStatus = 'denied';
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error'=>'Error performing this action.'], 400);
            }
        } else if ($status == 'pending') {
            // Either save all of the transactions, or save none of them (e.g. if one is unsuccessful)
            try{
                DB::beginTransaction();
                $vacationRequest->decision = 'pending';
                $vacationRequest->decision_date = null; // clear decision date
                $vacationRequest->decision_by = null; // clear decision by
                $vacationRequest->save();

                if($origStatus == 'denied') {
                    $requester->vacation_days = $requester->vacation_days - 1;
                    $requester->save();
                }
                DB::commit();
                $description .= "$origStatus to pending";
                $emailStatus = 'pending';
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error'=>'Error performing this action.'], 400);
            }
        } else {
            return response()->json(['error'=>'Invalid action'], 400);
        }

        // Log the action (with optional note).
        if($note != '') {
            $description .= " with note: \"$note\"";
        }
        $logger = new VacationLogger();
        $response = $logger->logAction($id, $description);

        // Email the user notification of update (optional)
        if($sendEmail) {
            $dateFormat = date('D, M j, Y', strtotime($vacationRequest->date_requested));
            $userFirstName = auth()->user()->first_name;

            $noteAttachment = '';
            if($note != ''){
                $noteAttachment = "$userFirstName has attached the following note: \"$note\"";
            }

            Mail::to($requester->email)->send(new VacationRequestUpdate($requester, $dateFormat, $emailStatus, $noteAttachment));
        }

        return response()->json($response, 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $vacationRequest = VacationRequest::findOrFail($id);
        if($vacationRequest) {
            $vacationRequest->delete();
            // Increment the user's vacation allotment
            $user = User::find(auth()->user()->id);
            $user->vacation_days = $user->vacation_days + 1;
            $user->save();
            return response()->json(null, 204);
        }
        return response()->json('Bad request', 400);
    }
}
