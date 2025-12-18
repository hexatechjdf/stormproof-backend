@component('mail::message')
# Your Inspection is Scheduled!

Hello {{ $inspection->homeowner->name }},

Great news! Your inspection has been scheduled.

**Advisor:** {{ $advisor->name }}
**Advisor Company:** {{ $advisor->agency->name }}

Your assigned advisor will contact you shortly to confirm one of your preferred dates and times.

If you have any questions, you can log in to your portal at any time.

@component('mail::button', ['url' => route('homeowner.dashboard')])
View My Portal
@endcomponent

Thank you for choosing StormProof.
@endcomponent
