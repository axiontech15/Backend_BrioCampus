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
                <p class="lead">Formats : CSV ou Excel (.xlsx). Colonnes : <code>matricule</code>, <code>full_name</code>, <code>faculty</code>, <code>diploma_date</code> (YYYY-MM-DD).</p>
                <div class="panel" style="max-width:560px">
                    <form method="POST" action="{{ route('university.palmares.import') }}" enctype="multipart/form-data" style="padding:16px">
                        @csrf
                        <label for="file"><i class="fa-solid fa-file-csv"></i> Fichier CSV / Excel</label>
                        <input id="file" type="file" name="file" accept=".csv,.txt,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        @error('file')
                            <div class="msg">{{ $message }}</div>
                        @enderror
                        <button class="btn" type="submit" style="margin-top:12px"><i class="fa-solid fa-upload"></i> Importer</button>
                    </form>
                    @if (session('import_errors'))
                        <ul class="hist" style="padding:0 16px 16px">
                            @foreach (session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="panel" style="margin-top:16px">
                    <div class="ph"><h3>Imports précédents</h3></div>
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
                                        <td class="mono">{{ $item->file }}</td>
                                        <td><span class="badge ok">{{ $item->status }}</span></td>
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
    const q = document.getElementById('q');
    if (q) q.oninput = e => {
        const v = e.target.value.toLowerCase();
        document.querySelectorAll('#t2 tbody tr[data-q]').forEach(r => {
            r.hidden = v && !r.dataset.q.includes(v);
        });
    };
</script>
@endpush
