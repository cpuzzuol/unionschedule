<?php

namespace App\Http\Controllers\api;

use App\ActionLog;
use App\Helpers\VacationLogger;
use App\Mail\AllotmentChanged;
use App\Mail\UserCreated;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller {

    public $successStatus = 200;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        // The create() method is used within the Laravel app. The others are consumed by the API.
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $users = User::withCount(['vacationRequests as outstanding_requests' => function ($count) {
            $count->where([['decision', 'pending'], ['date_requested', '>=', date('Y-01-01')]]);
        }])->orderBy('last_name')->get();
        return response()->json($users);
    }

    public function actionLogsByUser($user) {
        $actionLogs = ActionLog::with('user', 'actionBy')->where('affected_user', $user)->orderBy('created_at', 'DESC')->get();
        return response()->json($actionLogs);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $userID = auth()->user()->id;
        $response['restrictedDates'] = DB::table('restricted_dates')
            ->select('date')
            ->whereYear('date', '=', date('Y'))
            ->get();

        // Requests for the year (user will not be able to select these)
        $response['previousRequests'] = DB::table('vacation_requests')
            ->where('requested_by', $userID)
            ->whereYear('date_requested', '=', date('Y'))
            ->get();

        $user = User::findOrFail($id);
        $response['userDaysLeft'] = $user->vacation_days;

        return response()->json($response);
    }

    /**
     * Get the number of vacation days left
     * @param $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDaysLeft($user) {
        $user = User::findOrFail($user);
        $response = $user->vacation_days;

        return response()->json($response);
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
        $user = User::find($id);
        if(!$user){
            return response()->json('No matching user found', 404);
        }
        $validator = Validator::make($request->input('user'), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'vacation_days' => 'required|numeric'
        ]);
        if ($validator->fails()){
            return response()->json(['error' => $validator->errors()], 422);
        }

        $origVacationDays = $user->vacation_days; // Original number of vacation days

        $input = $request->input('user');
        $user->fill($input);
        $user->save();

        $response['user'] = $user;
        $response['sentResetPasswordLink'] = false;
        $response['vacationChangeLogged'] = false;

        // Log if the user's vacation days have changed. Also, email the user.
        if($origVacationDays != $user->vacation_days){
            $currentUser = auth()->user();

            $logVacationChange = new ActionLog();
            $logVacationChange->affected_user = $user->id;
            $logVacationChange->description = "Changed PTO days from $origVacationDays to {$user->vacation_days}";
            $logVacationChange->action_by = $currentUser->id;

            $log = new VacationLogger();
            $isVacationChangeLogged = $log->logVacationDayAllotmentChange($user->id, $origVacationDays, $user->vacation_days);
            if($isVacationChangeLogged){
                $response['vacationChangeLogged'] = true;
                Mail::to($user->email)->send(new AllotmentChanged($user, $origVacationDays));
            }
        }

        // Check if the user should have a password reset link sent to them.
        if($request->input('sendResetPasswordLink')){
            $passwordResetLink = Password::broker()->sendResetLink(['email' => $request->input('user')['email']]);
            if($passwordResetLink == Password::RESET_LINK_SENT){
                $response['sentResetPasswordLink'] = true;
            }
        }

        return response()->json($response);
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->input('user'), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'vacation_days' => 'required|numeric',
            'password' => 'required',
            'password_confirm' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 422);
        }
        $input = $request->input('user');
        $input['password'] = bcrypt($input['password']);
        $input['api_token'] = Str::random(60);
        $user = User::create($input);

        // Send a confirmation email to the user.
        Mail::to($user->email)->send(new UserCreated($user, $input['password_confirm']));

        return response()->json(['user'=>$user], $this->successStatus);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $user = User::find($id)->delete();
        if(!$user){
            return response()->json('No matching user found', 404);
        }
        return response()->json('User deleted', 200);
    }
}

