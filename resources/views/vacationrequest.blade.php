@extends('layouts.app')

@section('content')

    @if(Session::has('error'))
        <v-alert type="error" dense>
            {{ Session::get('error') }}
        </v-alert>
    @endif
    <h1>Request Vacation Days</h1>
    <vacation-selection remaining-days="{{ $user->vacation_days }}"></vacation-selection>

    {{ $user }}
@endsection
