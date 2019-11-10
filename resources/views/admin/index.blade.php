@extends('layouts.app')

@section('content')
    <v-row>
        <v-col cols="12" sm="12">
            <h1>Administrator Home Page</h1>
        </v-col>
        <v-col
            cols="12"
            xs="12"
            sm="6"
            md="4"
        >
            <v-card>
                <v-card-title>Pending Requests</v-card-title>
                <v-card-text>
                    <span class="error--text"><strong>{{ count($outstandingRequests) }}</strong></span> Requests require attention<br>
                    <admin-pending-requests-modal :pending-requests="{{ $outstandingRequests }}" :user="{{ auth()->user() }}"></admin-pending-requests-modal>
                </v-card-text>
            </v-card>
        </v-col>
        <v-col
            cols="12"
            xs="12"
            sm="6"
            md="4"
        >
            <v-card>
                <v-card-title>Restricted Dates</v-card-title>
                <v-card-text>
                    <span class="error--text"><strong>{{ count($restrictedDates) }}</strong></span> Dates are restricted from being requested<br><v-btn text color="info">Manage</v-btn>
                </v-card-text>
            </v-card>
        </v-col>
        <v-col
            cols="12"
            xs="12"
            sm="12"
        >
            <v-card>
                <v-card-title>Vacation Overview</v-card-title>
                <v-card-text>
                    <p>Click on a date to view the vacation requests for that date.</p>
                    <admin-overview-calendar :restricted-dates="{{ $restrictedDates }}" :user="{{ auth()->user() }}"></admin-overview-calendar>
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>
@endsection
