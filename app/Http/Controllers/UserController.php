<?php

namespace App\Http\Controllers;

use App\ActionLog;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // The create() method is used within the Laravel app. The others are consumed by the API.
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
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
        //
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
        // return response()->json($request);
        $user = User::find($id);
        if(!$user) {
            return response()->json('No matching user found', 404);
        }
        $origVacationDays = $user->vacation_days; // Original number of vacation days

        $user->name = $request->input('user')['name'];
        $user->email = $request->input('user')['email'];
        $user->vacation_days = $request->input('user')['vacation_days'];
        $user->save();

        $response['user'] = $user;
        $response['sentResetPasswordLink'] = false;
        $response['vacationChangeLogged'] = false;

        // Log if the user's vacation days have changed. Also, email the user.
        if($origVacationDays != $user->vacation_days) {
            $currentUser = auth()->user();

            $logVacationChange = new ActionLog();
            $logVacationChange->affected_user = $user->id;
            $logVacationChange->description = "Changed vacation days from $origVacationDays to {$user->vacation_days}";
            $logVacationChange->action_by = $currentUser->id;
            if($logVacationChange->save()) {
                $response['vacationChangeLogged'] = true;
                Mail::raw("Your vacation amount for the current year was updated by {$currentUser->name}. Your allotment changed from days $origVacationDays to {$user->vacation_days} days.", function ($message) use ($user){
                    $message->to($user->email);
                    $message->subject('Your vacation allotment has changed.');
                });
            }
        }

        // Check if the user should have a password reset link sent to them.
        if($request->input('sendResetPasswordLink')) {
            $passwordResetLink = Password::broker()->sendResetLink(['email'=>$request->input('user')['email']]);
            if($passwordResetLink == Password::RESET_LINK_SENT){
                $response['sentResetPasswordLink'] = true;
            }
        }

        return response()->json($response);
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
