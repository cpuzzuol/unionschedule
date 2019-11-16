@extends('layouts.app')

@section('content')
    <v-row>
        <v-col cols="12" sm="12">
            <h1>Administrator Home Page</h1>
            <admin-home :user="{{ auth()->user() }}"></admin-home>
        </v-col>
    </v-row>
@endsection
