@extends('layouts.app')

@section('title', 'Mon profil RH — Brio Campus')

@section('content')
<header class="tnav">
    <a class="brand" href="{{ route('home') }}">BRIO CAMPUS</a>
    <span class="chip"><i class="fa-solid fa-id-badge"></i> Mon profil</span>
    <nav class="links">
        <a href="{{ route('rh.index') }}">Dashboard</a>
        <a class="on" href="{{ route('rh.profile') }}">Mon profil</a>
    </nav>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
    </form>
</header>

<main class="page">
    <div class="crumb">RH › Profil</div>
    <h1>Profil recruteur</h1>
    <p class="lead">Informations associées à votre compte ressources humaines.</p>

    <div class="panel" style="max-width:560px">
        <form method="POST" action="{{ route('rh.profile.update') }}">
            @csrf
            @method('PUT')
            <label for="name"><i class="fa-solid fa-user"></i> Nom</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required>

            <label for="email"><i class="fa-solid fa-envelope"></i> Email</label>
            <input id="email" type="email" value="{{ $user->email }}" disabled>

            <label for="phone"><i class="fa-solid fa-phone"></i> Téléphone</label>
            <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}">

            <p class="lead">Tickets restants : <b>{{ $user->ticketBalance() }}</b> · Profils consultés : <b>{{ $user->profileSearches()->count() }}</b></p>

            @if ($errors->any())
                <div class="msg">{{ $errors->first() }}</div>
            @endif

            <button class="btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
        </form>
    </div>
</main>
@endsection
