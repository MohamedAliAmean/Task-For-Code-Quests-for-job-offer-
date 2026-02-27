<x-mail::message>
# Welcome, {{ $user->name }}!

Thanks for joining {{ config('app.name') }}.

<x-mail::button :url="url('/')">
Browse courses
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
