@extends('layouts.app')

@section('title', 'Mon profil RH — Brio Campus')

@section('content')
<header class="tnav">
    <a class="brand brand-logo" href="{{ route('home') }}"><img src="{{ asset('assets/icons/brio-icon-mini.svg') }}" alt=""><span>BRIO CAMPUS</span></a>
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

<main class="page settings-page rh-settings">
    <div class="settings-heading">
        <div>
            <div class="crumb">RH › Profil</div>
            <h1>Profil recruteur</h1>
            <p class="lead">Gérez les informations associées à votre espace RH.</p>
        </div>
        <a class="btn out" href="{{ route('rh.index') }}"><i class="fa-solid fa-arrow-left"></i> Retour au dashboard</a>
    </div>

    <div class="settings-layout">
        <aside class="profile-summary">
            <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <h2>{{ $user->name }}</h2>
            <span class="role-pill"><i class="fa-solid fa-briefcase"></i> Ressources humaines</span>
            <div class="profile-stats">
                <div><strong>{{ $user->ticketBalance() }}</strong><span>Tickets</span></div>
                <div><strong>{{ $user->profileSearches()->count() }}</strong><span>Dossiers</span></div>
            </div>
        </aside>

        <section class="panel settings-card">
            <div class="settings-card-heading">
                <div class="settings-card-icon"><i class="fa-solid fa-user-pen"></i></div>
                <div><h2>Informations personnelles</h2><p>Ces informations sont utilisées sur votre espace.</p></div>
            </div>

            <form method="POST" action="{{ route('rh.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="field">
                        <label for="name"><i class="fa-solid fa-user"></i> Nom complet</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="email"><i class="fa-solid fa-envelope"></i> Email professionnel</label>
                        <input id="email" type="email" value="{{ $user->email }}" disabled>
                    </div>
                    <div class="field field-full">
                        <label for="phone"><i class="fa-solid fa-phone"></i> Téléphone</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Ex. +243 810 000 000">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="msg form-message">{{ $errors->first() }}</div>
                @endif

                <div class="form-actions">
                    <a class="btn out" href="{{ route('account.edit') }}"><i class="fa-solid fa-sliders"></i> Paramètres du compte</a>
                    <button class="btn" type="submit"><i class="fa-solid fa-check"></i> Enregistrer les modifications</button>
                </div>
            </form>
        </section>
    </div>
</main>

@push('styles')
<style>
    .brand-logo { display:inline-flex !important; align-items:center; gap:8px; }
    .brand-logo img { width:34px; height:34px; object-fit:contain; }
    .settings-page { max-width:1180px; }
    .settings-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin-bottom:24px; }
    .settings-heading h1 { font-size:32px; }
    .settings-heading .lead { margin-bottom:0; }
    .settings-layout { display:grid; grid-template-columns:280px minmax(0,1fr); gap:20px; align-items:start; }
    .profile-summary { min-height:300px; border-radius:16px; padding:28px 22px; text-align:center; background:linear-gradient(145deg,#102A75,#1E3FCB); color:#fff; box-shadow:0 16px 36px rgba(30,63,203,.16); }
    .profile-avatar { width:82px; height:82px; margin:0 auto 14px; border:4px solid rgba(255,255,255,.18); border-radius:50%; background:#fff; color:var(--bc-blue); display:grid; place-items:center; font:800 30px Archivo; }
    .profile-summary h2 { color:#fff; font-size:20px; }
    .role-pill { display:inline-flex; align-items:center; gap:7px; margin-top:9px; border-radius:99px; padding:7px 10px; background:rgba(255,255,255,.1); color:#E3E9FF; font-size:12px; }
    .profile-stats { display:grid; grid-template-columns:1fr 1fr; margin-top:24px; border-top:1px solid rgba(255,255,255,.14); }
    .profile-stats div { padding-top:18px; }
    .profile-stats div + div { border-left:1px solid rgba(255,255,255,.14); }
    .profile-stats strong, .profile-stats span { display:block; }
    .profile-stats strong { font:800 24px Archivo; }
    .profile-stats span { margin-top:3px; color:#C5CFF2; font-size:11px; text-transform:uppercase; letter-spacing:.08em; }
    .settings-card { margin:0; padding-bottom:22px; }
    .settings-card-heading { display:flex; align-items:center; gap:12px; padding:22px; border-bottom:1px solid var(--bc-border); }
    .settings-card-heading h2 { font-size:18px; }
    .settings-card-heading p { margin:3px 0 0; color:var(--bc-muted); font-size:13px; }
    .settings-card-icon { width:42px; height:42px; border-radius:11px; display:grid; place-items:center; color:var(--bc-blue); background:var(--bc-soft); }
    .settings-card form { padding:22px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .field label { margin-top:0; }
    .field-full { grid-column:1/-1; }
    .form-message { padding:10px 12px; border-radius:8px; background:#FEF2F2; }
    .form-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:22px; padding-top:20px; border-top:1px solid var(--bc-border); }
    @media(max-width:900px) { .settings-layout { grid-template-columns:1fr; } .profile-summary { min-height:auto; } }
    @media(max-width:620px) { .settings-heading { align-items:flex-start; flex-direction:column; } .form-grid { grid-template-columns:1fr; } .field-full { grid-column:auto; } .form-actions { flex-direction:column-reverse; } .form-actions .btn { justify-content:center; width:100%; } }
</style>
@endpush
@endsection
