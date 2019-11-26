@extends('layouts.app')

@section('content')

    @if(Session::has('error'))
        <v-alert type="error" dense>
            {{ Session::get('error') }}
        </v-alert>
    @endif

    <v-row>
        <v-col xs="4">Beer</v-col>
        <v-col xs="4">Teer</v-col>
        <v-col xs="4">Reer</v-col>
        <v-col xs="12" cols="12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            You are logged in!
        </v-col>
    </v-row>
@endsection
