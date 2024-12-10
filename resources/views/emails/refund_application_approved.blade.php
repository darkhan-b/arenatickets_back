@component('mail::message')
# Здравствуйте, {{ $name }}!
<p>Ваша заявка на возврат по заказу № {{ $orderId }} <b>одобрена</b>.</p>
<br><br>С уважением,<br>
Администрация {{ env('APP_NAME') }}
@endcomponent
