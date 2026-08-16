<x-mail::message>
# New Contact Enquiry

You have received a new contact enquiry from **{{ $enquiry->name }}**.

**Email:** {{ $enquiry->email }}  
**Mobile:** {{ $enquiry->mobile ?? 'N/A' }}  
**Subject:** {{ $enquiry->subject }}  

**Message:**  
{{ $enquiry->message }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
