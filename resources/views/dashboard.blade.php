@extends('layouts.app')

@section('content')

    @if(Session::has('error'))
        <v-alert type="error" dense>
            {{ Session::get('error') }}
        </v-alert>
    @endif

    <v-row>
        <v-col cols="12" xs="12">
            <h1>My Dashboard</h1>
            <user-dashboard :user="{{ auth()->user() }}"></user-dashboard>
        </v-col>
    </v-row>
@endsection
<script>
	import UserDashboard from "../js/components/UserDashboard";
	export default {
		components: { UserDashboard }
	}
</script>
