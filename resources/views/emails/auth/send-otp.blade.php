<x-mail::message>
# Your Shopcalm Verification Code

Use the code below to verify your email address.

<x-mail::panel>
**{{ $otp }}**
</x-mail::panel>

This code will expire in 10 minutes.

If you did not request this, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
