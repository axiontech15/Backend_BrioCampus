@extends('layouts.app')

@section('title', 'Espace Université — Brio Campus')

@section('content')
<div class="shell">
    <aside class="side">
        <a class="brand" href="{{ route('home') }}">BRIO CAMPUS</a>
        <div class="pill"><i class="fa-solid fa-building-columns"></i> Université / Registre</div>
        <div class="grp">Registre</div>
        <button class="nav on" data-tab="dem"><i class="fa-solid fa-inbox"></i> Consultations <span class="cnt">{{ $stats['searches'] }}</span></button>
        <button class="nav" data-tab="reg"><i class="fa-solid fa-graduation-cap"></i> Diplômés <span class="cnt">{{ $stats['graduates'] }}</span></button>
        <button class="nav" data-tab="imp"><i class="fa-solid fa-file-csv"></i> Import palmarès <span class="cnt">{{ $stats['palmares'] }}</span></button>
        <button class="nav" data-tab="pro"><i class="fa-solid fa-pen-to-square"></i> Profil établissement</button>
        <div class="me">
            <span class="av">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            <div>
                <b>{{ $user->name }}</b>
                <small>{{ $university->name }}</small>
            </div>
        </div>
    </aside>
    <div class="main">
        <div class="bar">
            <span class="chip"><b>{{ $university->name }}</b> — Espace registre</span>
            <a class="btn out sm" href="{{ route('logs.index') }}"><i class="fa-solid fa-clock-rotate-left"></i> Mes journaux</a>
            <a class="btn out sm" href="{{ route('account.edit') }}"><i class="fa-solid fa-user-gear"></i> Compte</a>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
            </form>
        </div>
        <div class="page">
            <section data-sec="dem">
                <div class="crumb">Registre › Consultations</div>
                <h1>Consultations RH</h1>
                <p class="lead">RH ayant interrogé le palmarès de votre établissement.</p>
                <div class="stats">
                    <div class="panel stat"><span>Diplômés</span><b>{{ $stats['graduates'] }}</b></div>
                    <div class="panel stat"><span>RH distincts</span><b>{{ $stats['rh_consultations'] }}</b></div>
                    <div class="panel stat"><span>Consultations</span><b>{{ $stats['searches'] }}</b></div>
                    <div class="panel stat"><span>Imports</span><b>{{ $stats['palmares'] }}</b></div>
                </div>
                <div class="panel">
                    <div class="ph"><h3>Historique des consultations</h3></div>
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>RH</th>
                                    <th>Diplômé</th>
                                    <th>Matricule</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($searches as $search)
                                    <tr>
                                        <td>{{ $search->created_at?->format('d/m/Y H:i') }}</td>
                                        <td>{{ $search->rhUser?->email }}</td>
                                        <td>{{ $search->graduate?->name }}</td>
                                        <td class="mono">{{ $search->graduate?->matricule }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="empty">Aucune consultation</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="reg" hidden>
                <div class="crumb">Registre › Diplômés</div>
                <h1>Diplômés enregistrés</h1>
                <p class="lead">Extrait du registre numérisé de l’établissement.</p>
                <div class="panel">
                    <div class="ph">
                        <h3>Registre</h3>
                        <input class="search" id="q" placeholder="Nom, faculté, matricule…" aria-label="Rechercher">
                    </div>
                    <div class="tw">
                        <table id="t2">
                            <thead>
                                <tr>
                                    <th>Diplômé</th>
                                    <th>Faculté</th>
                                    <th>Date diplôme</th>
                                    <th>Matricule</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($graduates as $graduate)
                                    <tr data-q="{{ strtolower($graduate->name.' '.$graduate->faculty.' '.$graduate->matricule) }}">
                                        <td>{{ $graduate->name }}</td>
                                        <td>{{ $graduate->faculty }}</td>
                                        <td>{{ $graduate->diploma_date?->format('Y-m-d') }}</td>
                                        <td class="mono">{{ $graduate->matricule }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="empty">Aucun diplômé — importez un palmarès CSV</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="imp" hidden>
                <div class="crumb">Registre › Import</div>
                <h1>Import du palmarès</h1>
                <p class="lead">Formats : CSV ou Excel (.xlsx), jusqu’à 500 diplômés. Colonnes obligatoires : <code>matricule</code>, <code>full_name</code>, <code>faculty</code>, <code>diploma_date</code> (YYYY-MM-DD). L’import est traité en arrière-plan.</p>
                <div class="panel" style="max-width:560px">
                    <form method="POST" action="{{ route('university.palmares.import') }}" enctype="multipart/form-data" style="padding:16px">
                        @csrf
                        <label for="file"><i class="fa-solid fa-file-csv"></i> Fichier CSV / Excel</label>
                        <input id="file" type="file" name="file" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        @error('file')
                            <div class="msg">{{ $message }}</div>
                        @enderror
                        <button class="btn" type="submit" style="margin-top:12px"><i class="fa-solid fa-upload"></i> Importer</button>
                    </form>
                </div>
                <div class="panel" style="max-width:560px; margin-top:16px">
                    <div class="ph"><h3>Ajouter un diplômé manuellement</h3></div>
                    <form method="POST" action="{{ route('university.graduates.store') }}" style="padding:16px">
                        @csrf
                        <label for="graduate-matricule">Matricule</label>
                        <input id="graduate-matricule" name="matricule" value="{{ old('matricule') }}" maxlength="255" required>
                        @error('matricule')
                            <div class="msg">{{ $message }}</div>
                        @enderror
                        <label for="graduate-name">Nom complet</label>
                        <input id="graduate-name" name="full_name" value="{{ old('full_name') }}" maxlength="255" required>
                        @error('full_name')
                            <div class="msg">{{ $message }}</div>
                        @enderror
                        <label for="graduate-faculty">Faculté</label>
                        <input id="graduate-faculty" name="faculty" value="{{ old('faculty') }}" maxlength="255" required>
                        @error('faculty')
                            <div class="msg">{{ $message }}</div>
                        @enderror
                        <label for="graduate-date">Date de diplôme</label>
                        <input id="graduate-date" type="date" name="diploma_date" value="{{ old('diploma_date') }}" required>
                        @error('diploma_date')
                            <div class="msg">{{ $message }}</div>
                        @enderror
                        <button class="btn" type="submit" style="margin-top:12px"><i class="fa-solid fa-user-plus"></i> Ajouter au registre</button>
                    </form>
                </div>
                <div class="panel" style="margin-top:16px">
                    <div class="ph">
                        <div>
                            <h3>Imports précédents</h3>
                            @if ($palmares->contains('status', 'pending'))
                                <p id="queue-status" role="status">Import en attente ou en cours. Cette page s’actualisera automatiquement à la fin. Si le statut reste en attente, démarrez le worker avec <code>php artisan queue:work</code>.</p>
                            @endif
                        </div>
                    </div>
                    <div class="tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fichier</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($palmares as $item)
                                    <tr>
                                        <td class="mono">{{ $item->file ? basename($item->file) : 'Saisie manuelle' }}</td>
                                        <td>
                                            @if ($item->status === 'pending')
                                                <span class="badge gray">En attente</span>
                                            @elseif ($item->status === 'rejected')
                                                <span class="badge warn">Échec</span>
                                            @else
                                                <span class="badge ok">Terminé</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->uploaded_at?->format('d/m/Y H:i') ?? $item->created_at?->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="empty">Aucun import</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section data-sec="pro" hidden>
                <div class="crumb">Registre › Profil</div>
                <h1>Profil de l’université</h1>
                <p class="lead">Mettez à jour les informations visibles sur la plateforme.</p>
                <div class="panel" style="max-width:560px">
                    <form method="POST" action="{{ route('university.profile.update') }}" enctype="multipart/form-data" style="padding:16px">
                        @csrf
                        @method('PUT')
                        <label for="name"><i class="fa-solid fa-building"></i> Nom</label>
                        <input id="name" name="name" value="{{ old('name', $university->name) }}" required>
                        <label for="address"><i class="fa-solid fa-location-dot"></i> Adresse</label>
                        <input id="address" name="address" value="{{ old('address', $university->address) }}" required>
                        <label for="logo"><i class="fa-solid fa-image"></i> Logo</label>
                        <input id="logo" type="file" name="logo" accept="image/*">
                        <button class="btn" type="submit" style="margin-top:12px"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    BC.tabs();
    let pendingImportCount = {{ $palmares->where('status', 'pending')->count() }};
    if (pendingImportCount > 0) {
        const statusUrl = @json(route('university.palmares.status'));
        window.setInterval(async () => {
            try {
                const response = await fetch(statusUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    throw new Error(`Impossible de vérifier l’état des imports (${response.status}).`);
                }

                const result = await response.json();
                if (result.pending !== pendingImportCount) {
                    window.location.reload();
                }
            } catch (error) {
                console.error(error);
            }
        }, 5000);
    }

    const q = document.getElementById('q');
    if (q) q.oninput = e => {
        const v = e.target.value.toLowerCase();
        document.querySelectorAll('#t2 tbody tr[data-q]').forEach(r => {
            r.hidden = v && !r.dataset.q.includes(v);
        });
    };
</script>
@endpush
