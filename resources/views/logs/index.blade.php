@extends('layouts.app')

@section('title', 'Mes journaux — Brio Campus')

@section('content')
<header class="tnav">
    <a class="brand brand-logo" href="{{ route('home') }}"><img src="{{ asset('assets/icons/brio-icon-mini.svg') }}" alt=""><span>BRIO CAMPUS</span></a>
    <span class="chip"><i class="fa-solid fa-clock-rotate-left"></i> Mes journaux</span>
    <nav class="links">
        @if ($user->isAdmin())
            <a href="{{ route('admin.index') }}">Administration</a>
        @elseif ($user->isRh())
            <a href="{{ route('rh.index') }}">Espace RH</a>
        @elseif ($user->isUniversity())
            <a href="{{ route('university.index') }}">Université</a>
        @endif
        <a href="{{ route('account.edit') }}">Compte</a>
    </nav>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button class="btn red sm" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Quitter</button>
    </form>
</header>

<main class="page user-logs">
    <div class="logs-heading">
        <div>
            <div class="crumb">Compte › Journaux d’activité</div>
            <h1>Mes journaux d’activité</h1>
            <p class="lead">Historique des actions et événements associés à votre compte.</p>
        </div>
        <a class="btn out" href="{{ route('account.edit') }}"><i class="fa-solid fa-arrow-left"></i> Retour au compte</a>
    </div>

    <section class="panel">
        <div class="ph">
            <div>
                <h3>Événements émis par {{ $user->name }}</h3>
                <p>{{ $logs->total() }} événement(s)</p>
            </div>
        </div>
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td><span class="badge ok">{{ $log->action }}</span></td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Aucun événement enregistré pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="logs-pagination">{{ $logs->links() }}</div>
        @endif
    </section>
</main>

@push('styles')
<style>
    .user-logs { max-width:1200px; }
    .logs-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin-bottom:24px; }
    .logs-heading h1 { font-size:32px; }
    .logs-heading .lead { margin-bottom:0; }
    .logs-pagination { padding:18px 22px; }
    @media(max-width:620px) {
        .logs-heading { align-items:flex-start; flex-direction:column; }
        .logs-heading .btn { justify-content:center; width:100%; }
    }
</style>
@endpush
@endsection
