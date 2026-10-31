@extends('layouts.app')

@section('title', 'Espace RH — Brio Campus')

@section('content')
<header class="tnav rh-topbar">
    <a class="brand brand-logo" href="{{ route('home') }}"><img src="{{ asset('assets/icons/brio-icon-mini.svg') }}" alt=""><span>BRIO CAMPUS</span></a>
    <span class="chip"><i class="fa-solid fa-briefcase"></i> Espace Ressources Humaines</span>
    <nav class="links">
        <a class="on" href="{{ route('rh.index') }}">Dashboard</a>
        <a href="{{ route('rh.profile') }}">Mon profil</a>
        <a href="{{ route('account.edit') }}">Compte</a>
    </nav>
    <button class="btn" type="button" data-open><i class="fa-solid fa-user-check"></i> Vérifier un candidat</button>
    <span class="av">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
    </form>
</header>

<main class="page rh-page">
    <section class="hero">
        <div>
            <span class="chip">Réseau inter-universitaire connecté</span>
            <h1>Bonjour, {{ explode(' ', $user->name)[0] }}</h1>
            <p>Prêt à sécuriser un nouveau recrutement ? Lancez une vérification auprès des établissements qui ont délivré les diplômes.</p>
        </div>
        <div class="glass">
            <small>SOLDE ACTIF</small>
            <b><span>{{ $searches->count() }}</span> rapports prêts à l’emploi</b>
            <em>Chaque rapport est daté et référencé</em>
        </div>
    </section>

    <section class="cards3">
        <article class="panel ac">
            <div class="ic"><i class="fa-solid fa-user-check"></i></div>
            <h3>Vérifier un candidat</h3>
            <p>Interrogez les universités et écoles partenaires pour confirmer diplômes et parcours.</p>
            <button class="btn" type="button" data-open>Lancer une vérification <i class="fa-solid fa-arrow-right"></i></button>
        </article>
        <article class="panel ac">
            <div class="ic"><i class="fa-solid fa-ticket"></i></div>
            <h3>Tickets disponibles</h3>
            <div class="big">{{ $balance }}</div>
            <p>1 ticket = 1 vérification, sans expiration.</p>
            <button class="btn out" type="button" id="gt">Gérer mes tickets <i class="fa-solid fa-arrow-right"></i></button>
        </article>
        <article class="panel ac">
            <div class="ic"><i class="fa-solid fa-chart-simple"></i></div>
            <h3>Statistiques</h3>
            <p>Tickets restants : <b>{{ $balance }}</b> · Profils consultés : <b>{{ $searches->count() }}</b></p>
            <a class="btn out" href="{{ route('account.edit') }}">Paramètres du compte <i class="fa-solid fa-arrow-right"></i></a>
        </article>
    </section>

    <dialog id="tickets-dialog" class="tickets-dialog">
        <div class="tickets-dialog-header">
            <div>
                <span class="dialog-kicker"><i class="fa-solid fa-ticket"></i> Centre des tickets</span>
                <h3>Gérer mes tickets</h3>
                <p>Solde actuel : <strong>{{ $balance }}</strong> vérification{{ $balance > 1 ? 's' : '' }} disponible{{ $balance > 1 ? 's' : '' }}</p>
            </div>
            <button class="dialog-close" type="button" data-close-tickets aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="tickets-dialog-body">
            <section class="ticket-section">
                <div class="ticket-section-title">
                    <div><h4>Recharger mon solde</h4><p>Choisissez un pack professionnel.</p></div>
                </div>
                <form method="POST" action="{{ route('rh.tickets.buy') }}" class="ticket-packs">
                    @csrf
                    @forelse ($categories as $category)
                        <label class="ticket-pack">
                            <input type="radio" name="category_id" value="{{ $category->id }}" required {{ $loop->first ? 'checked' : '' }}>
                            <span class="ticket-pack-check"><i class="fa-solid fa-check"></i></span>
                            <span class="ticket-pack-icon"><i class="fa-solid fa-ticket"></i></span>
                            <strong>{{ $category->quantity }} tickets</strong>
                            <small>{{ $category->name }}</small>
                            <b>{{ number_format(($category->quantity ?: 1) * 5, 0, ',', ' ') }} USD</b>
                        </label>
                    @empty
                        <p class="empty">Aucun pack disponible actuellement.</p>
                    @endforelse
                    @if ($categories->isNotEmpty())
                        <button class="btn ticket-buy" type="submit"><i class="fa-solid fa-credit-card"></i> Acheter le pack sélectionné</button>
                    @endif
                </form>
            </section>

            <section class="ticket-section">
                <div class="ticket-section-title">
                    <div><h4>Mes tickets</h4><p>Historique et documents téléchargeables.</p></div>
                </div>
                <div class="ticket-history">
                    @forelse ($tickets as $ticket)
                        <article class="ticket-row">
                            <div class="ticket-row-icon"><i class="fa-solid fa-file-lines"></i></div>
                            <div class="ticket-row-info">
                                <strong>{{ $ticket->category?->name ?? 'Pack Brio Campus' }}</strong>
                                <span>{{ $ticket->created_at?->format('d/m/Y') }} · {{ $ticket->quantity }} ticket{{ $ticket->quantity > 1 ? 's' : '' }}</span>
                            </div>
                            <span class="badge ok">Approuvé</span>
                            <a class="btn out sm ticket-download" href="{{ route('rh.tickets.download', $ticket) }}"><i class="fa-solid fa-download"></i> Télécharger PDF</a>
                        </article>
                    @empty
                        <div class="ticket-empty"><i class="fa-solid fa-ticket"></i><p>Aucun ticket généré pour le moment.</p></div>
                    @endforelse
                </div>
            </section>
        </div>
    </dialog>

    <section class="panel">
        <div class="ph">
            <div>
                <h3>Profils consultés</h3>
                <p>{{ $user->name }} · {{ $searches->count() }} dossiers</p>
            </div>
            <input class="search" id="q" placeholder="Candidat, matricule, établissement…" aria-label="Rechercher">
        </div>
        <div class="tw">
            <table id="tab">
                <thead>
                    <tr>
                        <th>Candidat</th>
                        <th>Matricule</th>
                        <th>Faculté</th>
                        <th>Établissement</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($searches as $search)
                        <tr data-q="{{ strtolower($search->graduate?->name.' '.$search->graduate?->matricule.' '.$search->university?->name) }}">
                            <td>{{ $search->graduate?->name }}</td>
                            <td class="mono">{{ $search->graduate?->matricule }}</td>
                            <td>{{ $search->graduate?->faculty }}</td>
                            <td>{{ $search->university?->name }}</td>
                            <td>{{ $search->created_at?->format('d/m/Y H:i') }}</td>
                            <td><span class="badge ok">Vérifié</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Aucune recherche enregistrée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>

<dialog id="dg">
    <form method="POST" action="{{ route('rh.search') }}" id="nf">
        @csrf
        <h3><i class="fa-solid fa-magnifying-glass"></i> Nouvelle vérification</h3>
        <label for="nn">Nom ou matricule</label>
        <input id="nn" name="query" required placeholder="Ex. Mwamba Kalala ou UNK-19-0412">
        <label for="ne">Établissement (optionnel)</label>
        <select id="ne" name="university_id">
            <option value="">Tous les établissements</option>
            @foreach ($universities as $university)
                <option value="{{ $university->id }}">{{ $university->name }}</option>
            @endforeach
        </select>
        <div class="acts">
            <button class="btn out" type="button" onclick="document.getElementById('dg').close()">Annuler</button>
            <button class="btn" type="submit">Ouvrir le dossier <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </form>
</dialog>

@push('styles')
<style>
    .rh-topbar {
        position: sticky;
        top: 0;
        z-index: 30;
        min-height: 72px;
        padding-inline: clamp(18px, 4vw, 48px);
        background: rgba(255, 255, 255, .94);
        backdrop-filter: blur(14px);
        box-shadow: 0 1px 0 rgba(217, 224, 242, .9);
    }

    .rh-topbar .brand {
        font-size: 16px;
        letter-spacing: .08em;
    }

    .rh-topbar > .chip {
        background: var(--bc-soft);
        border-color: transparent;
        color: var(--bc-blue);
        white-space: nowrap;
    }

    .rh-topbar > .chip:last-of-type {
        display: none;
    }

    .rh-topbar form {
        flex: 0 0 auto;
    }

    .rh-topbar .links {
        align-self: stretch;
        align-items: center;
        gap: 26px;
    }

    .rh-topbar .links a {
        height: 100%;
        display: inline-flex;
        align-items: center;
        border-bottom: 2px solid transparent;
        color: var(--bc-slate);
        font-weight: 500;
    }

    .rh-topbar .links a.on {
        color: var(--bc-blue);
        border-bottom-color: var(--bc-blue);
    }

    .rh-page {
        width: 100%;
        max-width: 1320px;
        margin-inline: auto;
        padding: 36px clamp(18px, 4vw, 48px) 64px;
    }

    .rh-page .hero {
        position: relative;
        overflow: hidden;
        min-height: 210px;
        border-radius: 20px;
        padding: clamp(28px, 5vw, 44px);
        background:
            radial-gradient(circle at 85% 20%, rgba(111, 142, 255, .38), transparent 26%),
            linear-gradient(135deg, #102A75 0%, #1E3FCB 100%);
        box-shadow: 0 16px 42px rgba(30, 63, 203, .16);
    }

    .rh-page .hero::after {
        content: "";
        position: absolute;
        right: -64px;
        bottom: -100px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, .12);
        box-shadow: 0 0 0 38px rgba(255, 255, 255, .025), 0 0 0 76px rgba(255, 255, 255, .018);
    }

    .rh-page .hero > * {
        position: relative;
        z-index: 1;
    }

    .rh-page .hero h1 {
        margin-top: 18px;
        font-size: clamp(28px, 4vw, 40px);
    }

    .rh-page .hero p {
        max-width: 560px;
        font-size: 15px;
        line-height: 1.65;
    }

    .rh-page .glass {
        max-width: 360px;
        justify-self: end;
        width: 100%;
        border-radius: 16px;
        background: rgba(255, 255, 255, .09);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
    }

    .cards3 {
        gap: 16px;
    }

    .cards3 .ac {
        min-height: 220px;
        border-radius: 16px;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .cards3 .ac:hover {
        transform: translateY(-3px);
        border-color: #BBC8EF;
        box-shadow: 0 14px 34px rgba(15, 31, 92, .11);
    }

    .cards3 .ac .ic {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--bc-soft), #F8FAFF);
    }

    .cards3 .ac h3 {
        font-size: 17px;
    }

    .cards3 .ac p {
        line-height: 1.6;
    }

    .rh-page .panel {
        border-radius: 16px;
        border-color: #E1E6F3;
    }

    .rh-page .ph {
        padding: 20px 22px;
    }

    .rh-page .ph h3 {
        font-size: 18px;
    }

    .rh-page .search {
        min-width: 280px;
        padding-left: 40px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237B86A6' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 13px center;
        background-size: 17px;
    }

    .rh-page table {
        min-width: 760px;
    }

    .rh-page th {
        height: 44px;
        background: #F8FAFE;
    }

    .rh-page tbody tr {
        transition: background .15s ease;
    }

    .rh-page tbody tr:hover {
        background: #FAFBFF;
    }

    .tickets-dialog {
        width: min(920px, calc(100vw - 32px));
        max-height: calc(100vh - 32px);
        padding: 0;
        overflow: hidden;
        color: var(--bc-slate);
    }

    .tickets-dialog::backdrop {
        background: rgba(11, 27, 94, .64);
        backdrop-filter: blur(5px);
    }

    .tickets-dialog-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 24px 28px;
        color: #fff;
        background: linear-gradient(135deg, #102A75, #1E3FCB);
    }

    .tickets-dialog-header h3 {
        margin: 7px 0 5px;
        color: #fff;
        font-size: 25px;
    }

    .tickets-dialog-header p {
        margin: 0;
        color: #DCE4FF;
    }

    .dialog-kicker {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .09em;
        text-transform: uppercase;
        color: #C5D0FF;
    }

    .dialog-close {
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 50%;
        background: rgba(255,255,255,.1);
        color: #fff;
        cursor: pointer;
    }

    .tickets-dialog-body {
        max-height: calc(100vh - 170px);
        overflow-y: auto;
        padding: 24px 28px 28px;
        background: #F8FAFE;
    }

    .ticket-section {
        border: 1px solid var(--bc-border);
        border-radius: 14px;
        padding: 18px;
        background: #fff;
    }

    .ticket-section + .ticket-section {
        margin-top: 16px;
    }

    .ticket-section-title { margin-bottom: 14px; }
    .ticket-section-title h4 { margin: 0; color: var(--bc-ink); font: 800 16px Archivo; }
    .ticket-section-title p { margin: 3px 0 0; color: var(--bc-muted); font-size: 12px; }

    .ticket-packs {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .ticket-pack {
        position: relative;
        min-height: 168px;
        margin: 0;
        border: 1px solid var(--bc-border);
        border-radius: 13px;
        padding: 18px;
        cursor: pointer;
        background: #fff;
        transition: .2s ease;
    }

    .ticket-pack:hover { border-color: #AFC0EF; transform: translateY(-2px); }
    .ticket-pack input { position:absolute; opacity:0; pointer-events:none; }
    .ticket-pack:has(input:checked) { border-color: var(--bc-blue); background: #F7F9FF; box-shadow: 0 0 0 2px rgba(30,63,203,.1); }
    .ticket-pack-check { position:absolute; right:12px; top:12px; width:22px; height:22px; border-radius:50%; display:grid; place-items:center; border:1px solid var(--bc-border); color:transparent; font-size:10px; background:#fff; }
    .ticket-pack:has(input:checked) .ticket-pack-check { border-color:var(--bc-blue); background:var(--bc-blue); color:#fff; }
    .ticket-pack-icon { width:38px; height:38px; margin-bottom:12px; border-radius:10px; display:grid; place-items:center; color:var(--bc-blue); background:var(--bc-soft); }
    .ticket-pack strong, .ticket-pack small, .ticket-pack b { display:block; }
    .ticket-pack strong { color:var(--bc-ink); font-size:15px; }
    .ticket-pack small { margin-top:4px; color:var(--bc-muted); }
    .ticket-pack b { margin-top:12px; color:var(--bc-blue); font-family:Archivo; }

    .ticket-buy { grid-column:1/-1; justify-content:center; }
    .ticket-history { display:grid; gap:8px; }
    .ticket-row { display:grid; grid-template-columns:42px minmax(0,1fr) auto auto; align-items:center; gap:12px; padding:12px; border:1px solid var(--bc-border); border-radius:11px; }
    .ticket-row-icon { width:42px; height:42px; border-radius:10px; display:grid; place-items:center; color:var(--bc-blue); background:var(--bc-soft); }
    .ticket-row-info strong, .ticket-row-info span { display:block; }
    .ticket-row-info strong { color:var(--bc-ink); font-size:13px; }
    .ticket-row-info span { margin-top:2px; color:var(--bc-muted); font-size:12px; }
    .ticket-empty { text-align:center; padding:20px; color:var(--bc-muted); }
    .ticket-empty i { font-size:28px; color:#BAC3DD; }
    .ticket-empty p { margin:8px 0 0; }

    @media (max-width: 700px) {
        .tickets-dialog { width: calc(100vw - 20px); }
        .tickets-dialog-header { padding:20px; }
        .tickets-dialog-body { padding:16px; }
        .ticket-packs { grid-template-columns:1fr; }
        .ticket-buy { grid-column:auto; }
        .ticket-row { grid-template-columns:42px minmax(0,1fr); }
        .ticket-row .badge, .ticket-download { grid-column:2; justify-self:start; }
    }

    .rh-page dialog {
        color: var(--bc-slate);
    }

    .rh-page dialog form {
        margin: 0;
    }

    @media (max-width: 1100px) {
        .rh-topbar > .chip:first-of-type {
            display: none;
        }

        .rh-topbar .links {
            margin-left: auto;
        }
    }

    @media (max-width: 900px) {
        .rh-topbar {
            position: relative;
            gap: 10px;
        }

        .rh-topbar .links {
            order: 5;
            width: 100%;
            height: 44px;
            margin: 0;
            border-top: 1px solid var(--bc-border);
        }

        .rh-topbar .links a {
            height: 44px;
        }

        .rh-page .hero {
            grid-template-columns: 1fr;
        }

        .rh-page .glass {
            justify-self: stretch;
            max-width: none;
            text-align: left;
        }

        .cards3 {
            grid-template-columns: 1fr;
        }

        .cards3 .ac {
            min-height: auto;
        }

        .rh-page .search {
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 560px) {
        .rh-topbar {
            padding-inline: 14px;
        }

        .rh-topbar > .chip:last-of-type {
            margin-left: auto;
        }

        .rh-topbar > [data-open] {
            width: 100%;
            justify-content: center;
            order: 6;
        }

        .rh-topbar form,
        .rh-topbar .av {
            display: none;
        }

        .rh-page {
            padding-inline: 14px;
        }

        .rh-page .hero {
            padding: 24px;
        }

        .rh-page .ph {
            align-items: stretch;
        }

        .rh-page .ph input {
            flex-basis: 100%;
        }
    }
</style>
@endpush
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-open]').forEach(b => b.onclick = () => {
        @if ($balance < 1)
            BC.toast('Aucun ticket disponible');
            return;
        @endif
        document.getElementById('dg').showModal();
    });
    const ticketsDialog = document.getElementById('tickets-dialog');
    document.getElementById('gt').onclick = () => ticketsDialog.showModal();
    document.querySelector('[data-close-tickets]').onclick = () => ticketsDialog.close();
    ticketsDialog.addEventListener('click', event => {
        if (event.target === ticketsDialog) {
            ticketsDialog.close();
        }
    });
    document.getElementById('q').oninput = e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#tab tbody tr[data-q]').forEach(r => {
            r.hidden = q && !r.dataset.q.includes(q);
        });
    };
</script>
@endpush
