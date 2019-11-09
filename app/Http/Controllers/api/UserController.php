<?php

namespace App\Http\Controllers\api;

use App\ActionLog;
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
        //$users = User::orderBy('last_name')->get();
//        $users = DB::table('users')
//            ->leftJoin('vacation_requests', 'users.id', '=', 'vacation_requests.requested_by')
//            ->select('users.*', DB::raw('(SELECT COUNT(vacation_requests.date_requested) FROM vacation_requests WHERE decision = "pending") AS outstanding_requests'))
//            ->groupBy('id')
//            ->get();

        $users = User::withCount(['vacationRequests as outstanding_requests' => function ($count) {
            $count->where('decision', 'pending');
        }])->orderBy('last_name')->get();
        return response()->json($users);
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
            $logVacationChange->description = "Changed vacation days from $origVacationDays to {$user->vacation_days}";
            $logVacationChange->action_by = $currentUser->id;
            if($logVacationChange->save()){
                $response['vacationChangeLogged'] = true;
                Mail::raw("Your vacation amount for the current year was updated by {$currentUser->name}. Your allotment changed from days $origVacationDays to {$user->vacation_days} days.", function ($message) use ($user){
                    $message->to($user->email);
                    $message->subject('Your vacation allotment has changed.');
                });
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
        Mail::raw("Your account on the Union Sorters of America vacation request system has been created. Your username is {$user->email} and your password is {$input['password_confirm']}. Log in at http://unionschedule.test/login.", function ($message) use ($user){
            $message->to($user->email);
            $message->subject('Your account on Union Sorters Vacation Scheduler has been created.');
        });

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

