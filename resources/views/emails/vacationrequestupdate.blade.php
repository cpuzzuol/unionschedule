@component('mail::message')
# Notice of vacation request update

Your vacation request for {{ $requestDate }} is <strong>{{ $requestStatus }}</strong>. You have {{ $requester->vacation_days }} available vacation days this year.

{{ $noteAttachment }}

Please contact management if you have any questions.

Thank you.

<span style="font-size: 0.8rem;">(This message was automatically generated from the {{ config('app.name') }})</span>
@endcomponent
