@extends('layouts.app')

@section('title', 'Espace Super administrateur — Brio Campus')

@section('content')
<div class="shell">
    <aside class="side">
        <a class="brand" href="{{ route('home') }}">BRIO CAMPUS</a>
        <div class="pill"><i class="fa-solid fa-shield-halved"></i> Super admin / Plateforme</div>
        <div class="grp">Pilotage</div>
        <button class="nav on" data-tab="ov"><i class="fa-solid fa-chart-pie"></i> Vue d’ensemble</button>
        <div class="grp">Gestion</div>
        <button class="nav" data-tab="co"><i class="fa-solid fa-users"></i> Comptes <span class="cnt">{{ $stats['users'] }}</span></button>
        <button class="nav" data-tab="un"><i class="fa-solid fa-building-columns"></i> Universités <span class="cnt">{{ $stats['universities_active'] }}/{{ $stats['universities'] }}</span></button>
        <button class="nav" data-tab="ca"><i class="fa-solid fa-tags"></i> Catégories <span class="cnt">{{ $categories->count() }}</span></button>
        <button class="nav" data-tab="ro"><i class="fa-solid fa-user-shield"></i> Rôles <span class="cnt">{{ $roles->count() }}</span></button>
        <div class="grp">Opérations</div>
        <button class="nav" data-tab="ve"><i class="fa-solid fa-magnifying-glass-chart"></i> Vérifications <span class="cnt">{{ $stats['searches'] }}</span></button>
        <button class="nav" data-tab="lo"><i class="fa-solid fa-clock-rotate-left"></i> Journaux <span class="cnt">{{ $logs->count() }}</span></button>
        <div class="me">
            <span class="av">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div>
                <b>{{ auth()->user()->name }}</b>
                <small>Super administrateur</small>
            </div>
        </div>
    </aside>
    <div class="main">
        <div class="bar">
            <span class="chip"><b>Espace Admin</b> — <span id="bc">Vue d’ensemble</span></span>
            <a class="btn out sm" href="{{ route('account.edit') }}"><i class="fa-solid fa-user-gear"></i> Compte</a>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
            </form>
        </div>
        <div class="page">
            <section data-sec="ov">
                <div class="crumb">Admin › Vue d’ensemble</div>
                <h1>Pilotage de la plateforme</h1>
                <p class="lead">Comptes, recruteurs, universités et vérifications — calculés depuis les données existantes.</p>
                <div class="stats">
                    <div class="panel stat"><span>Comptes ({{ $stats['users_active'] }} actifs)</span><b>{{ $stats['users'] }}</b></div>
                    <div class="panel stat"><span>Recruteurs actifs</span><b>{{ $stats['rh_active'] }}<i>/ {{ $stats['rh_total'] }}</i></b></div>
                    <div class="panel stat"><span>Universités connectées</span><b>{{ $stats['universities_active'] }}<i>/ {{ $stats['universities'] }}</i></b></div>
                    <div class="panel stat"><span>Vérifications</span><b>{{ $stats['searches'] }}</b></div>
                    <div class="panel stat"><span>Tickets en circulation</span><b>{{ $stats['tickets'] }}</b></div>
                </div>
                <div class="panel">
                    <div class="ph"><h3><i class="fa-solid fa-bolt"></i> Dernières activités</h3></div>
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Acteur</th>
                                    <th>Détail</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>{{ $log->user?->email ?? '—' }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td><span class="badge ok">{{ $log->action }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="empty">Aucune activité récente</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="co" hidden>
                <div class="crumb">Admin › Comptes</div>
                <h1>Comptes</h1>
                <p class="lead">Activez ou suspendez l’accès d’un compte.</p>
                <div class="panel">
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Compte</th>
                                    <th>Nom</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $account)
                                    <tr>
                                        <td>{{ $account->email }}</td>
                                        <td>{{ $account->name }}</td>
                                        <td>{{ $account->role?->name }}</td>
                                        <td>
                                            @if ($account->status)
                                                <span class="badge ok">Actif</span>
                                            @else
                                                <span class="badge gray">Suspendu</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($account->isAdmin())
                                                —
                                            @else
                                                <form method="POST" action="{{ route('admin.users.toggle', $account) }}">
                                                    @csrf
                                                    <button class="btn out sm" type="submit">
                                                        {{ $account->status ? 'Suspendre' : 'Réactiver' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="un" hidden>
                <div class="crumb">Admin › Universités</div>
                <h1>Universités</h1>
                <p class="lead">Établissements émetteurs raccordés à la plateforme.</p>
                <div class="panel" style="margin-bottom:16px">
                    <div class="ph"><h3><i class="fa-solid fa-plus"></i> Ajouter une université</h3></div>
                    <form method="POST" action="{{ route('admin.universities.store') }}" style="padding:16px;display:grid;gap:10px;max-width:520px">
                        @csrf
                        <label>Nom</label>
                        <input name="name" required>
                        <label>Adresse</label>
                        <input name="address" required>
                        <label>Agent (utilisateur)</label>
                        <select name="agent_id" required>
                            @foreach ($users->filter(fn ($u) => $u->isUniversity() || $u->isAdmin()) as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }} — {{ $agent->email }}</option>
                            @endforeach
                        </select>
                        <label>Statut</label>
                        <select name="status">
                            <option value="active">active</option>
                            <option value="pending">pending</option>
                            <option value="inactive">inactive</option>
                        </select>
                        <button class="btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                    </form>
                </div>
                <div class="panel">
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Établissement</th>
                                    <th>Adresse</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($universities as $uni)
                                    <tr>
                                        <td>{{ $uni->name }}</td>
                                        <td>{{ $uni->address }}</td>
                                        <td>
                                            @if ($uni->status === 'active')
                                                <span class="badge ok">Connectée</span>
                                            @elseif ($uni->status === 'pending')
                                                <span class="badge warn">En attente</span>
                                            @else
                                                <span class="badge gray">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="empty">Aucune université</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="ca" hidden>
                <div class="crumb">Admin › Catégories</div>
                <h1>Catégories de tickets</h1>
                <p class="lead">Packs de tickets proposés aux recruteurs.</p>
                <div class="panel" style="margin-bottom:16px">
                    <form method="POST" action="{{ route('admin.categories.store') }}" style="padding:16px;display:grid;gap:10px;max-width:520px">
                        @csrf
                        <label>Nom</label>
                        <input name="name" required>
                        <label>Description</label>
                        <input name="description">
                        <label>Quantité de tickets</label>
                        <input name="quantity" type="number" min="1" value="5">
                        <button class="btn" type="submit"><i class="fa-solid fa-plus"></i> Créer</button>
                    </form>
                </div>
                <div class="panel">
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Quantité</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td>{{ $category->quantity }}</td>
                                        <td><span class="badge ok">{{ $category->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="ro" hidden>
                <div class="crumb">Admin › Rôles</div>
                <h1>Rôles</h1>
                <p class="lead">Définissez les rôles attribuables aux comptes.</p>
                <div class="panel" style="margin-bottom:16px">
                    <form method="POST" action="{{ route('admin.roles.store') }}" style="padding:16px;display:flex;gap:12px;align-items:end;flex-wrap:wrap">
                        @csrf
                        <div>
                            <label for="role_name">Nom du rôle</label>
                            <input id="role_name" name="name" required placeholder="ex. moderator">
                        </div>
                        <button class="btn" type="submit"><i class="fa-solid fa-plus"></i> Créer</button>
                    </form>
                </div>
                <div class="panel">
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            @if (! in_array(strtolower($role->name), ['admin', 'rh', 'university'], true))
                                                <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn out sm" type="submit">Supprimer</button>
                                                </form>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="ve" hidden>
                <div class="crumb">Admin › Vérifications</div>
                <h1>Vérifications</h1>
                <p class="lead">Recherches de profils effectuées par les RH.</p>
                <div class="panel">
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>RH</th>
                                    <th>Diplômé</th>
                                    <th>Établissement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($searches as $search)
                                    <tr>
                                        <td>{{ $search->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>{{ $search->rhUser?->email }}</td>
                                        <td>{{ $search->graduate?->name }}</td>
                                        <td>{{ $search->university?->name }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="empty">Aucune vérification</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="lo" hidden>
                <div class="crumb">Admin › Journaux</div>
                <h1>Journaux d’activité</h1>
                <p class="lead">Trace des actions importantes sur la plateforme.</p>
                <div class="panel">
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>{{ $log->user?->email }}</td>
                                        <td><span class="badge ok">{{ $log->action }}</span></td>
                                        <td>{{ $log->description }}</td>
                                        <td class="mono">{{ $log->ip_address }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="empty">Aucun journal</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    BC.tabs();
    document.querySelectorAll('[data-tab]').forEach(b => b.addEventListener('click', () => {
        document.getElementById('bc').textContent = b.textContent.replace(/\d+.*/, '').trim();
    }));
</script>
@endpush
