<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RestrictedDate;
use App\VacationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        Mail::raw('FROM HOME CONTROLLER', function ($message){
            $message->to('contact@contact.com');
            $message->subject('LIVE!');
        });
        return view('home');
    }
}
