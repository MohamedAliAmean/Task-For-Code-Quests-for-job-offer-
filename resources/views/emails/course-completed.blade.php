<x-mail::message>
# Course completed!

Congrats {{ $certificate->user->name }} — you completed **{{ $certificate->course->title }}**.

<x-mail::button :url="url('/')">
View courses
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
