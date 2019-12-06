@component('mail::message')
# Notice of vacation allotment change

You are receiving this message because your number of vacation days for the calendar year has been adjusted from {{ $origVacationDays }} to <strong>{{ $user->vacation_days }} days</strong>.

Please contact management if you have any questions.

Thank you.

<span style="font-size: 0.8rem;">(This message was automatically generated from the {{ config('app.name') }})</span>
@endcomponent
