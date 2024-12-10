@component('mail::message')
# Сәлеметсіз бе / Здравствуйте!
<p>Растау кодыңыз / Ваш код подтверждения:</p>
<div style="font-size: 30px; font-weight: bold; color: #4BB0FE">{{ $code }}</div>
<br><br>Құрметпен / С уважением,<br>
{{ env('APP_NAME') }}
@endcomponent
