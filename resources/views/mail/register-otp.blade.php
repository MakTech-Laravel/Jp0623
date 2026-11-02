<x-mail::message>
# Verify your email

Use this 6-digit code to finish creating your operator account. It expires in 10 minutes.

<x-mail::panel>
**{{ $code }}**
</x-mail::panel>

If you did not request this code, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
