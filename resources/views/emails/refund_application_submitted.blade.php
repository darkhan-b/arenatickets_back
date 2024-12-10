@component('mail::message')
# Здравствуйте, {{ $name }}!
<p>Ваша заявка на возврат по заказу № {{ $orderId }} принята в обработку.</p>
<br><br>С уважением,<br>
Администрация {{ env('APP_NAME') }}
@endcomponent
