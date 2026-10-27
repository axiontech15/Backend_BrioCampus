<header class="hero" id="accueil">
    <nav aria-label="Navigation principale">
      <a class="logo" href="{{ route('home') }}#accueil" aria-label="Brio Campus - accueil">
        <img class="logo-full" src="{{ asset('assets/icons/brio-main-logo.svg') }}" alt="">
        <img class="logo-mini" src="{{ asset('assets/icons/brio-icon-mini.svg') }}" alt="">
        <span class="sr-only">Brio Campus</span>
      </a>
      <div class="links mono">
        <a href="#accueil">Accueil</a><a href="#apropos">À propos</a><a href="#publics">Publics</a><a
          href="#parcours">Parcours</a>
      </div>
      @auth
        @if (auth()->user()->isAdmin())
          <a class="btn" href="{{ route('admin.index') }}"><i class="fa-solid fa-gauge"></i> Mon espace</a>
        @elseif (auth()->user()->isRh())
          <a class="btn" href="{{ route('rh.index') }}"><i class="fa-solid fa-gauge"></i> Mon espace</a>
        @elseif (auth()->user()->isUniversity())
          <a class="btn" href="{{ route('university.index') }}"><i class="fa-solid fa-gauge"></i> Mon espace</a>
        @else
          <a class="btn" href="{{ route('account.edit') }}">Mon compte</a>
        @endif
      @else
        <a class="btn" href="{{ route('auth.login') }}">Se connecter</a>
      @endauth
    </nav>

    <div class="hero-in">
      <h1>Votre diplôme, vérifié à la source.</h1>
      <p>Brio Campus vérifie les diplômes délivrés en RDC auprès des établissements qui les ont émis, et délivre un
        rapport daté, référencé et contrôlable. Les recruteurs décident plus vite ; les diplômés n’ont plus à refaire la
        démarche à chaque candidature.</p>
      <div class="cta">
        <a class="btn ghost" href="#parcours">Voir le parcours</a>
        <a class="btn" href="#contact">Ouvrir un dossier <span class="ar">↗</span></a>
      </div>
    </div>

    <div class="deck" aria-hidden="true">
      <div class="tile t1">
        <div class="mono">Contrôle</div>
        <div class="big">4/4</div>
        <div>pièces recevables</div>
      </div>
      <div class="tile t2">
        <div class="top"><span>Dossier</span><span>En cours</span></div>
        <div class="big">3/5</div>
        <div class="chips"><span>Dépôt</span><span>Contrôle</span><span>Source</span></div>
      </div>
      <div class="tile t3"><b>+</b>Dépôt<small>Joignez vos pièces</small></div>
      <div class="tile t4">
        <div class="mono" style="color:#93C5FD">Attestation</div>
        <p style="margin:8px 0 0;font-size:11px">Rapport <em>daté</em>, code du dossier et lien de contrôle public.</p>
      </div>
      <div class="tile t5">
        <div class="mono">Vérification</div>
        <div>à la source</div>
        <div class="bars"><i style="height:30%"></i><i style="height:45%"></i><i style="height:40%"></i><i
            style="height:70%"></i><i style="height:100%"></i></div>
      </div>
    </div>
    <div class="rate">Chaque écran répond à une seule question : où en est ce dossier, et sur quelle preuve ?</div>
  </header>
