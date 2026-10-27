@extends('layouts.app')

@section('title', 'Mon compte — Brio Campus')

@section('content')
<header class="tnav">
    <a class="brand" href="{{ route('home') }}">BRIO CAMPUS</a>
    <span class="chip"><i class="fa-solid fa-user-gear"></i> Paramètres du compte</span>
    <nav class="links">
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.index') }}">Admin</a>
        @elseif (auth()->user()->isRh())
            <a href="{{ route('rh.index') }}">Espace RH</a>
        @elseif (auth()->user()->isUniversity())
            <a href="{{ route('university.index') }}">Université</a>
        @endif
    </nav>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
    </form>
</header>

<main class="page">
    <div class="crumb">Compte › Paramètres</div>
    <h1>Paramètres du compte</h1>
    <p class="lead">Mettez à jour vos informations personnelles et votre mot de passe.</p>

    <div class="panel" style="max-width:560px">
        <form method="POST" action="{{ route('account.update') }}">
            @csrf
            @method('PUT')
            <label for="name"><i class="fa-solid fa-id-card"></i> Nom complet</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required>

            <label for="email"><i class="fa-solid fa-envelope"></i> Email</label>
            <input id="email" type="email" value="{{ $user->email }}" disabled>

            <label for="phone"><i class="fa-solid fa-phone"></i> Téléphone</label>
            <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}">

            <label for="password"><i class="fa-solid fa-lock"></i> Nouveau mot de passe</label>
            <input id="password" type="password" name="password" autocomplete="new-password" placeholder="Laisser vide pour ne pas changer">

            <label for="password_confirmation">Confirmation</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">

            <p class="lead">Rôle : <b>{{ $user->role?->name }}</b></p>

            @if ($errors->any())
                <div class="msg">{{ $errors->first() }}</div>
            @endif

            <button class="btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
        </form>
    </div>
</main>
@endsection
