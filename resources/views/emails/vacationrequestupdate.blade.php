@component('mail::message')
# Notice of PTO request update

Your PTO request for {{ $requestDate }} is <strong>{{ $requestStatus }}</strong>. You have {{ $requester->vacation_days }} available PTO days this year.

{{ $noteAttachment }}

Please contact management if you have any questions.

Thank you.

<span style="font-size: 0.8rem;">(This message was automatically generated from the {{ config('app.name') }})</span>
@endcomponent
