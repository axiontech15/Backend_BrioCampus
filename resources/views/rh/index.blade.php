@extends('layouts.app')

@section('title', 'Espace RH — Brio Campus')

@section('content')
<header class="tnav">
    <a class="brand" href="{{ route('home') }}">BRIO CAMPUS</a>
    <span class="chip"><i class="fa-solid fa-briefcase"></i> Espace Ressources Humaines</span>
    <nav class="links">
        <a class="on" href="{{ route('rh.index') }}">Dashboard</a>
        <a href="{{ route('rh.profile') }}">Mon profil</a>
        <a href="{{ route('account.edit') }}">Compte</a>
    </nav>
    <span class="chip"><b>{{ $balance }}</b> tickets disponibles</span>
    <button class="btn" type="button" data-open><i class="fa-solid fa-user-check"></i> Vérifier un candidat</button>
    <span class="av">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
    </form>
</header>

<main class="page">
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

    <section class="panel" id="tickets-panel" hidden>
        <div class="ph">
            <div>
                <h3><i class="fa-solid fa-cart-shopping"></i> Acheter des tickets</h3>
                <p>Choisissez un pack pour recharger votre solde.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('rh.tickets.buy') }}" style="padding:16px;display:flex;gap:12px;flex-wrap:wrap;align-items:end">
            @csrf
            <div>
                <label for="category_id">Pack</label>
                <select id="category_id" name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->quantity }} tickets)</option>
                    @endforeach
                </select>
            </div>
            <button class="btn" type="submit"><i class="fa-solid fa-credit-card"></i> Acheter</button>
        </form>
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Pack</th>
                        <th>Quantité</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->created_at?->format('d/m/Y') }}</td>
                            <td>{{ $ticket->category?->name }}</td>
                            <td>{{ $ticket->quantity }}</td>
                            <td><span class="badge ok">{{ $ticket->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Aucun achat pour le moment</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

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
    document.getElementById('gt').onclick = () => {
        const p = document.getElementById('tickets-panel');
        p.hidden = !p.hidden;
    };
    document.getElementById('q').oninput = e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#tab tbody tr[data-q]').forEach(r => {
            r.hidden = q && !r.dataset.q.includes(q);
        });
    };
</script>
@endpush
