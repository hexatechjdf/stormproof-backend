@component('mail::message')
# New Inspection Opportunity

A new inspection is available in your area.

**Address:** {{ $inspection->home->address_line1 }}
**Homeowner:** {{ $inspection->homeowner->name }}

If you are interested, please log in to your portal to claim this job. This opportunity is offered on a first-come, first-served basis.

@component('mail::button', ['url' => route('advisor.dashboard')])
View in Portal
@endcomponent

Thanks,  

{{ config('app.name') }}
@endcomponent
