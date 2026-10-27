@extends('layouts.app')

@section('title', 'Connexion — Brio Campus')

@section('content')
<div class="split">
    <aside class="left">
        <a class="brand" href="{{ route('home') }}">BRIO CAMPUS</a>
        <h1>Vérifiez. Certifiez.<br>Recrutez en confiance.</h1>
        <p>La plateforme de référence pour vérifier les diplômes auprès des établissements qui les ont délivrés.</p>
        <svg viewBox="0 0 320 340" role="img"
            aria-label="Illustration : une diplômée en toge et toque tenant son diplôme, accompagné d’une coche de vérification">
            <circle cx="160" cy="200" r="140" fill="#fff" opacity=".08" />
            <path d="M40 340C40 262 98 236 160 236S280 262 280 340Z" fill="#2F54E0" />
            <path d="M128 238 160 296 192 238Z" fill="#fff" />
            <rect x="146" y="204" width="28" height="38" rx="12" fill="#6B3E26" />
            <circle cx="118" cy="170" r="8" fill="#6B3E26" />
            <circle cx="202" cy="170" r="8" fill="#6B3E26" />
            <ellipse cx="160" cy="166" rx="42" ry="50" fill="#7A4A2F" />
            <path d="M118 150C118 112 202 112 202 150 190 134 130 134 118 150Z" fill="#15110F" />
            <path d="M128 124v24c17 10 47 10 64 0v-24Z" fill="#0F1F5C" />
            <path d="M78 114 160 84 242 114 160 144Z" fill="#0A1140" />
            <path d="M160 114 230 122V168" stroke="#FBBF24" stroke-width="3" fill="none" />
            <circle cx="230" cy="172" r="5" fill="#FBBF24" />
            <circle cx="145" cy="170" r="3" fill="#1a1010" />
            <circle cx="175" cy="170" r="3" fill="#1a1010" />
            <path d="M146 188Q160 201 174 188" stroke="#1a1010" stroke-width="3" fill="none" stroke-linecap="round" />
            <g transform="rotate(-12 215 290)">
                <rect x="168" y="278" width="98" height="26" rx="13" fill="#F8FAFC" />
                <rect x="208" y="278" width="9" height="26" fill="#2563EB" />
            </g>
            <circle cx="172" cy="296" r="9" fill="#7A4A2F" />
            <circle cx="262" cy="276" r="9" fill="#7A4A2F" />
            <circle cx="256" cy="214" r="22" fill="#22C55E" />
            <path d="M246 214l8 8 14-16" stroke="#fff" stroke-width="5" fill="none" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </aside>
    <main class="right">
        <div class="box">
            <h2>Bienvenue</h2>
            <p>Connectez-vous à votre espace Université, RH ou Super administrateur.</p>
            <form method="POST" action="{{ route('auth.login.submit') }}" novalidate>
                @csrf
                <label for="em"><i class="fa-solid fa-envelope"></i> Email professionnel</label>
                <input id="em" name="email" type="email" autocomplete="email"
                    value="{{ old('email') }}" placeholder="nom@organisation.cd" required>
                <label for="pw"><i class="fa-solid fa-lock"></i> Mot de passe</label>
                <input id="pw" name="password" type="password" autocomplete="current-password"
                    placeholder="••••••••" required>
                <div class="opts">
                    <label><input type="checkbox" name="remember" value="1" checked> Se souvenir de moi</label>
                    <a href="{{ route('auth.forgot') }}">Mot de passe oublié ?</a>
                </div>
                <button class="btn block" type="submit">
                    Se connecter <i class="fa-solid fa-arrow-right"></i>
                </button>
                @if ($errors->any())
                    <div class="msg" id="msg" aria-live="polite">{{ $errors->first() }}</div>
                @endif
            </form>
            <div class="demo">
                <div class="in"><b>Comptes démo</b> — pré-remplir :
                    <button type="button" data-e="admin@briocampus.cd" data-p="Admin123!">Super admin : admin@briocampus.cd</button>
                    <button type="button" data-e="a.dupont@kintalents.cd" data-p="demo1234">RH : a.dupont@kintalents.cd</button>
                    <button type="button" data-e="registre@unikin.cd" data-p="demo1234">Université : registre@unikin.cd</button>
                    <small>Admin : Admin123! · RH / Université : demo1234</small>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    const em = document.getElementById('em'), pw = document.getElementById('pw');
    document.querySelectorAll('.demo button').forEach(b => b.onclick = () => {
        em.value = b.dataset.e;
        pw.value = b.dataset.p;
    });
</script>
@endpush
