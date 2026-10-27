<footer>
    <div class="wrap">
      <div class="foot-grid">
        <div class="foot-brand">
          <img src="{{ asset('assets/icons/brio-footer-logo.svg') }}" alt="Brio Campus">
          <p>Plateforme de vérification des diplômes à la source pour diplômés, recruteurs, universités et institutions.
          </p>
          <div class="foot-social" aria-label="Réseaux et contacts">
            <a href="mailto:contact@briocampus.cd" aria-label="Nous écrire"><i class="fa-solid fa-envelope"></i></a>
            <a href="#publics" aria-label="Voir les publics"><i class="fa-solid fa-circle"></i></a>
            <a href="#parcours" aria-label="Voir le parcours"><i class="fa-solid fa-check"></i></a>
            <a href="{{ route('auth.login') }}" aria-label="Accéder à la plateforme"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
          </div>
        </div>

        <div class="foot-col">
          <h3>Plateforme</h3>
          <a href="#apropos">Notre mission</a>
          <a href="#publics">Publics servis</a>
          <a href="#parcours">Parcours</a>
          <a href="#contact">Ouvrir un dossier</a>
        </div>

        <div class="foot-col">
          <h3>Accès</h3>
          <a href="{{ route('auth.login') }}">Connexion</a>
          <a href="{{ route('rh.index') }}">Espace recruteur</a>
          <a href="{{ route('university.index') }}">Espace université</a>
          <a href="{{ route('admin.index') }}">Administration</a>
        </div>

        <div class="newsletter">
          <h3>Notifications</h3>
          <p class="foot-note">Recevez les mises à jour de la plateforme : nouveaux établissements raccordés, délais de
            traitement et preuves disponibles.</p>
          <form class="newsletter-form" action="#contact">
            <label class="sr-only" for="newsletter-email">Adresse e-mail</label>
            <input id="newsletter-email" type="email" name="email" placeholder="Votre e-mail">
            <button type="submit">OK</button>
          </form>
        </div>
      </div>

      <div class="foot-bottom">
        <span>© {{ date('Y') }} Brio Campus. Tous droits réservés.</span>
        <span>Votre diplôme, vérifié à la source.</span>
      </div>
    </div>
    <a class="to-top" href="#accueil" aria-label="Retour en haut"><i class="fa-solid fa-arrow-up"></i></a>
  </footer>
