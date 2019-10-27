@extends('layouts.app')

@section('content')
    <h1>System Users</h1>
    <system-users :user="{{ auth()->user() }}"></system-users>

@endsection
