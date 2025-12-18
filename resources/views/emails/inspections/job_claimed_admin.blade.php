@component('mail::message')
# Job Claimed

This is a notification that an inspection has been claimed and is now scheduled.

**Inspection ID:** {{ $inspection->id }}
**Homeowner:** {{ $inspection->homeowner->name }}
**Address:** {{ $inspection->home->address_line1 }}

**Claimed By:** {{ $advisor->name }} ({{ $advisor->email }})

The inspection status has been updated to **Scheduled**. No further action is required from you at this time.

@component('mail::button', ['url' => route('admin.inspections.show', $inspection->id)])
View Inspection Details
@endcomponent

Thanks,  

{{ config('app.name') }}
@endcomponent
