# Répartition des tâches

## Tâches communes

- [x] Définir le modèle de données et les relations entre utilisateurs, rôles, universités, profils, tickets et journaux.
- [x] Définir les règles d'autorisation pour les trois rôles : administrateur, RH et université.
- [x] Mettre en place l'authentification, la gestion de session et la protection des routes.
- [x] Ajouter la validation des données, la gestion des erreurs et la documentation des endpoints.
- [ ] Préparer les tests des fonctionnalités communes et des permissions.

## Henock - Administrateur

- [x] Implémenter la gestion des rôles : création, consultation, modification et suppression.
- [x] Implémenter la gestion des utilisateurs : création, consultation, modification et suppression.
- [x] Implémenter la gestion des catégories de tickets.
- [x] Implémenter la gestion des universités : création, consultation et suppression.
- [x] Implémenter la consultation des journaux d'activité.
- [x] Mettre en place l'archivage automatique des journaux et des utilisateurs selon la règle de conservation définie.
- [x] Implémenter la suppression des anciennes données archivées.
- [ ] Ajouter les tests d'autorisation et les tests CRUD du rôle administrateur.

## Tshibaak - Ressources humaines

- [x] Implémenter l'achat de tickets et le suivi du solde disponible.
- [x] Implémenter la recherche d'un profil diplômé auprès des universités.
- [x] Enregistrer une recherche uniquement lorsqu'un profil est trouvé.
- [x] Implémenter la gestion des profils utilisateurs.
- [x] Décrémenter un ticket après une recherche valide, selon la règle métier retenue.
- [x] Implémenter les statistiques RH : tickets restants et profils consultés.
- [ ] Ajouter les tests de recherche, de consommation des tickets et de permissions RH.

## Bradley - Université

- [x] Implémenter la consultation et la mise à jour du profil universitaire.
- [x] Implémenter l'import du palmarès des diplômés.
- [x] Valider les champs importés : matricule étudiant, nom complet, faculté et date d'obtention du diplôme.
- [x] Gérer les doublons, les lignes invalides et le retour détaillé après import.
- [x] Implémenter les statistiques de l'université : RH ayant consulté le palmarès et nombre d'étudiants diplômés.
- [ ] Ajouter les tests d'import, de mise à jour et de permissions du rôle université.

## Finalisation

- [ ] Vérifier les parcours complets administrateur, RH et université.
- [ ] Vérifier la cohérence des permissions entre API et interface.
- [x] Mettre à jour la documentation avec les variables d'environnement et les instructions de lancement.
