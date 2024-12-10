@component('mail::message')
# Здравствуйте!
<p>Пользователь {{ $organizer->fullName }} создал событие <b>{{ $show->title }}</b></p>
<p>Ознакомиться можно по <a href="{{ env('APP_URL') }}/admin#/organizer_show">ссылке</a></p>
<br><br>С уважением,<br>
Администрация {{ env('APP_NAME') }}
@endcomponent
