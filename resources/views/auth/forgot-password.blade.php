@extends('layouts.app')

@section('title', 'Mot de passe oublié — Brio Campus')

@section('content')
<div class="split">
    <aside class="left">
        <a class="brand" href="{{ route('home') }}">BRIO CAMPUS</a>
        <h1>Réinitialisez<br>votre accès.</h1>
        <p>Un lien sécurisé pourra être envoyé à votre adresse professionnelle une fois le module e-mail activé.</p>
    </aside>
    <main class="right">
        <div class="box">
            <h2><i class="fa-solid fa-key"></i> Mot de passe oublié</h2>
            <p>Indiquez l’e-mail de votre compte. Pour la démo locale, contactez l’administrateur ou utilisez un compte démo.</p>
            <form method="POST" action="{{ route('auth.login') }}" onsubmit="event.preventDefault(); BC.toast('Réinitialisation : branchez le service e-mail pour activer l’envoi OTP.');">
                @csrf
                <label for="email"><i class="fa-solid fa-envelope"></i> Email professionnel</label>
                <input id="email" type="email" name="email" required placeholder="nom@organisation.cd">
                <button class="btn block" type="submit">
                    Envoyer le lien <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
            <p style="margin-top:18px"><a href="{{ route('auth.login') }}"><i class="fa-solid fa-arrow-left"></i> Retour à la connexion</a></p>
        </div>
    </main>
</div>
@endsection
