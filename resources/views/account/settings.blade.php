@extends('layouts.app')

@section('title', 'Mon compte — Brio Campus')

@section('content')
<header class="tnav">
    <a class="brand brand-logo" href="{{ route('home') }}"><img src="{{ asset('assets/icons/brio-icon-mini.svg') }}" alt=""><span>BRIO CAMPUS</span></a>
    <span class="chip"><i class="fa-solid fa-user-gear"></i> Paramètres du compte</span>
    <nav class="links">
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.index') }}">Admin</a>
        @elseif (auth()->user()->isRh())
            <a href="{{ route('rh.index') }}">Espace RH</a>
        @elseif (auth()->user()->isUniversity())
            <a href="{{ route('university.index') }}">Université</a>
        @endif
        <a href="{{ route('logs.index') }}">Mes journaux</a>
    </nav>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
    </form>
</header>

<main class="page settings-page account-settings">
    <div class="settings-heading">
        <div>
            <div class="crumb">Compte › Paramètres</div>
            <h1>Paramètres du compte</h1>
            <p class="lead">Gérez vos informations personnelles, votre sécurité et votre mot de passe.</p>
        </div>
        @if ($user->isRh())
            <a class="btn out" href="{{ route('rh.index') }}"><i class="fa-solid fa-arrow-left"></i> Retour à l’espace RH</a>
        @elseif ($user->isAdmin())
            <a class="btn out" href="{{ route('admin.index') }}"><i class="fa-solid fa-arrow-left"></i> Retour à l’administration</a>
        @elseif ($user->isUniversity())
            <a class="btn out" href="{{ route('university.index') }}"><i class="fa-solid fa-arrow-left"></i> Retour à l’université</a>
        @endif
    </div>

    <div class="settings-layout">
        <aside class="account-summary">
            <div class="account-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <span class="role-pill"><i class="fa-solid fa-shield-halved"></i> {{ ucfirst($user->role?->name ?? 'Utilisateur') }}</span>
            <div class="account-security"><i class="fa-solid fa-lock"></i><div><strong>Compte sécurisé</strong><span>Mot de passe modifiable</span></div></div>
        </aside>

        <section class="panel settings-card">
            <div class="settings-card-heading">
                <div class="settings-card-icon"><i class="fa-solid fa-id-card"></i></div>
                <div><h2>Informations du compte</h2><p>Mettez à jour vos coordonnées et vos identifiants.</p></div>
            </div>

            <form method="POST" action="{{ route('account.update') }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="field">
                        <label for="name"><i class="fa-solid fa-id-card"></i> Nom complet</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="email"><i class="fa-solid fa-envelope"></i> Adresse email</label>
                        <input id="email" type="email" value="{{ $user->email }}" disabled>
                    </div>
                    <div class="field field-full">
                        <label for="phone"><i class="fa-solid fa-phone"></i> Téléphone</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Ex. +243 810 000 000">
                    </div>
                </div>

                <div class="security-divider"><span>Modifier le mot de passe</span></div>
                <div class="form-grid">
                    <div class="field">
                        <label for="password"><i class="fa-solid fa-lock"></i> Nouveau mot de passe</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" placeholder="Laisser vide pour ne pas changer">
                    </div>
                    <div class="field">
                        <label for="password_confirmation"><i class="fa-solid fa-lock"></i> Confirmation</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Confirmez le mot de passe">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="msg form-message">{{ $errors->first() }}</div>
                @endif

                <div class="form-actions">
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
    .account-summary { min-height:300px; border-radius:16px; padding:28px 22px; text-align:center; background:#fff; border:1px solid var(--bc-border); box-shadow:var(--shadow-card); }
    .account-avatar { width:82px; height:82px; margin:0 auto 14px; border:5px solid var(--bc-soft); border-radius:50%; background:linear-gradient(145deg,#E3E9FF,#CBD6F7); color:var(--bc-blue); display:grid; place-items:center; font:800 30px Archivo; }
    .account-summary h2 { font-size:20px; overflow-wrap:anywhere; }
    .account-summary > p { margin:6px 0 10px; color:var(--bc-muted); font-size:13px; overflow-wrap:anywhere; }
    .role-pill { display:inline-flex; align-items:center; gap:7px; border-radius:99px; padding:7px 11px; background:var(--bc-soft); color:var(--bc-blue); font-size:12px; font-weight:700; text-transform:capitalize; }
    .account-security { display:flex; align-items:center; gap:10px; margin-top:24px; padding:14px; text-align:left; border-radius:12px; background:#F8FAFE; }
    .account-security > i { width:34px; height:34px; border-radius:9px; display:grid; place-items:center; background:var(--bc-ok-bg); color:var(--bc-ok); }
    .account-security strong, .account-security span { display:block; }
    .account-security strong { color:var(--bc-ink); font-size:13px; }
    .account-security span { margin-top:2px; color:var(--bc-muted); font-size:11px; }
    .settings-card { margin:0; padding-bottom:22px; }
    .settings-card-heading { display:flex; align-items:center; gap:12px; padding:22px; border-bottom:1px solid var(--bc-border); }
    .settings-card-heading h2 { font-size:18px; }
    .settings-card-heading p { margin:3px 0 0; color:var(--bc-muted); font-size:13px; }
    .settings-card-icon { width:42px; height:42px; border-radius:11px; display:grid; place-items:center; color:var(--bc-blue); background:var(--bc-soft); }
    .settings-card form { padding:22px; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .field label { margin-top:0; }
    .field-full { grid-column:1/-1; }
    .security-divider { display:flex; align-items:center; gap:12px; margin:26px 0 18px; color:var(--bc-muted); font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .security-divider::after { content:""; height:1px; flex:1; background:var(--bc-border); }
    .form-message { padding:10px 12px; border-radius:8px; background:#FEF2F2; }
    .form-actions { display:flex; justify-content:flex-end; margin-top:22px; padding-top:20px; border-top:1px solid var(--bc-border); }
    @media(max-width:900px) { .settings-layout { grid-template-columns:1fr; } .account-summary { min-height:auto; } }
    @media(max-width:620px) { .settings-heading { align-items:flex-start; flex-direction:column; } .form-grid { grid-template-columns:1fr; } .field-full { grid-column:auto; } .form-actions .btn { justify-content:center; width:100%; } }
</style>
@endpush
@endsection
