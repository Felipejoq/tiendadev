@component('mail::message')
    # Hola {{$user->name}}!

    Gracias por registrarte, por favor verifica tu cuenta en el siguiente botón:

    @component('mail::button', ['url' => route('verify', $user->verification_token)])
        Confirmar mi cuenta
    @endcomponent

    Gracias,<br>
    {{ config('app.name') }}
@endcomponent