<?php

namespace App\Http\Controllers;

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
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show the admin home.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $restrictedDates = DB::table('restricted_dates')
            ->select('date')
            ->whereYear('date', '=', date('Y'))
            ->get();
        // Get outstanding requests in the FUTURE only and get user info along with it
        $outstandingRequests = VacationRequest::with('requester')->where([
            ['decision', '=', 'pending'],
            ['date_requested', '>=', date('Y-m-d')]
        ])->orderBy('date_requested', 'ASC')->get();
        return view('admin/index', ['outstandingRequests' => $outstandingRequests, 'restrictedDates' => $restrictedDates]);
    }

    public function users() {
        return view('admin/users/index');
    }
}
