@extends('layouts.app')

@section('title', 'Profil vérifié — Brio Campus')

@section('content')
<header class="tnav rh-topbar">
    <a class="brand brand-logo" href="{{ route('home') }}"><img src="{{ asset('assets/icons/brio-icon-mini.svg') }}" alt=""><span>BRIO CAMPUS</span></a>
    <span class="chip"><i class="fa-solid fa-briefcase"></i> Dossier vérifié</span>
    <nav class="links">
        <a href="{{ route('rh.index') }}">Dashboard</a>
        <a href="{{ route('rh.index') }}#search-history">Historique</a>
        <a href="{{ route('rh.profile') }}">Mon profil</a>
    </nav>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
    </form>
</header>

<main class="page rh-page profile-page">
    <div class="profile-actions">
        <a class="btn out" href="{{ route('rh.index') }}#search-history"><i class="fa-solid fa-arrow-left"></i> Retour à l’historique</a>
        <button class="btn" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimer le profil</button>
    </div>

    <article class="panel profile-report">
        <div class="report-heading">
            <span class="chip"><i class="fa-solid fa-circle-check"></i> Profil vérifié</span>
            <h1>{{ $profileSearch->graduate->name }}</h1>
            <p>Rapport de vérification Brio Campus</p>
        </div>

        <dl class="profile-details">
            <div>
                <dt>Matricule</dt>
                <dd class="mono">{{ $profileSearch->graduate->matricule }}</dd>
            </div>
            <div>
                <dt>Établissement</dt>
                <dd>{{ $profileSearch->university->name }}</dd>
            </div>
            <div>
                <dt>Faculté</dt>
                <dd>{{ $profileSearch->graduate->faculty }}</dd>
            </div>
            <div>
                <dt>Date d’obtention du diplôme</dt>
                <dd>{{ $profileSearch->graduate->diploma_date?->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt>Date de vérification</dt>
                <dd>{{ $profileSearch->created_at?->format('d/m/Y à H:i') }}</dd>
            </div>
            <div>
                <dt>Vérifié par</dt>
                <dd>{{ $user->name }}</dd>
            </div>
        </dl>

        <footer class="report-footer">
            <span>Document généré depuis l’espace Ressources humaines de Brio Campus.</span>
            <span>Référence du dossier : BC-RH-{{ str_pad((string) $profileSearch->id, 6, '0', STR_PAD_LEFT) }}</span>
        </footer>
    </article>
</main>

@push('styles')
<style>
    .profile-page { max-width: 1000px; }
    .profile-actions { display:flex; justify-content:space-between; gap:12px; margin-bottom:20px; }
    .profile-report { overflow:hidden; padding:0; }
    .report-heading { padding:34px; background:linear-gradient(135deg,#102A75,#1E3FCB); color:#fff; }
    .report-heading .chip { background:rgba(255,255,255,.12); border-color:rgba(255,255,255,.2); color:#fff; }
    .report-heading h1 { margin:18px 0 5px; color:#fff; font-size:clamp(26px,4vw,36px); }
    .report-heading p { margin:0; color:#DCE4FF; }
    .profile-details { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0; margin:0; padding:10px 34px; }
    .profile-details div { padding:18px 10px 18px 0; border-bottom:1px solid var(--bc-border); }
    .profile-details dt { color:var(--bc-muted); font-size:12px; }
    .profile-details dd { margin:6px 0 0; font-weight:600; }
    .report-footer { display:flex; justify-content:space-between; gap:20px; padding:20px 34px; color:var(--bc-muted); font-size:12px; }
    @media(max-width:620px) {
        .profile-actions, .report-footer { align-items:stretch; flex-direction:column; }
        .profile-actions .btn { justify-content:center; }
        .profile-details { grid-template-columns:1fr; padding-inline:22px; }
        .report-heading { padding:26px 22px; }
    }
    @media print {
        .rh-topbar, .profile-actions { display:none !important; }
        .profile-page { width:100%; max-width:none; margin:0; padding:0; }
        .profile-report { border:0; box-shadow:none; }
        .report-heading { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    }
</style>
@endpush
@endsection

@if ($autoPrint)
    @push('scripts')
    <script>
        window.addEventListener('load', () => window.print());
    </script>
    @endpush
@endif
