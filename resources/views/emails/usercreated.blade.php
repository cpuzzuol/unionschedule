@component('mail::message')
# Account Created

An account has been created for you on the Union Sorters Manager Portal.

Email/User Name: {{ $user->email }}<br>
Password: {{ $pw }}

This application will allow you to request time off and manage your PTO days for the year.

@component('mail::button', ['url' => env('APP_URL') . '/login'])
Login
@endcomponent

Please contact management if you have any questions.

Thank you.

<span style="font-size: 0.8rem;">(This message was automatically generated from the {{ config('app.name') }})</span>
@endcomponent
