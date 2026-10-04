# Nourriture Terrestre (Laravel)

Portage sur Laravel 13 de l'application [nourriture-terrestre](https://github.com/RobinPoulet/nourriture-terrestre) :
menu de la semaine récupéré depuis le WordPress de Nourriture Terrestre, prise de commande le lundi matin,
récapitulatif envoyé par SMS, vote et classement des plats, administration.

Les données viennent de la base de l'ancienne version (alwaysdata), reprise telle quelle : colonnes historiques
en majuscules lues en minuscules via `PDO::ATTR_CASE` (voir `config/database.php`). Seule la table `users`
évolue : connexion par email + mot de passe à la place du cookie d'appareil.

## Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate
# renseigner DB_*, MAIL_*, API_KEY_SMS, SMS_DEVICE_ID, SMS_RECIPIENT, PUSHER_* dans .env
php artisan migrate        # base vierge : crée les tables ; base importée : crée seulement ce qui manque
php artisan serve
```

Pour une base locale vierge : `php artisan db:seed` crée quelques utilisateurs (`alice@example.com`… / `password`).

## Comptes utilisateurs

- Pas d'inscription libre : l'admin crée l'utilisateur (nom + email) puis clique sur l'enveloppe pour lui envoyer
  une **invitation** (lien valable 72 h pour choisir son mot de passe). Le lien s'affiche aussi dans l'admin,
  pour le transmettre autrement si l'email n'arrive pas.
- Connexion par email + mot de passe, « rester connecté » coché par défaut ; 5 essais puis blocage temporaire.
- « Mot de passe oublié » envoie le même type de lien.
- Utilisateurs repris de l'ancienne base : ils gardent leur historique (commandes, votes) mais n'ont ni email
  ni mot de passe : renseigner leur email dans l'admin et leur envoyer l'invitation.
- Premier admin : `php artisan admin:password "<nom>" --email=<email>`.

## Commandes

| Commande | Rôle (ancien script) |
|---|---|
| `php artisan sms:send-orders` | Envoie le récapitulatif des commandes du jour par SMS et notifie la page via Pusher (`cron/send_sms_cron.php`) |
| `php artisan admin:password "<nom>" [--email=]` | Définit l'email et le mot de passe d'un utilisateur et le passe admin (`scripts/set_admin_password.php`) |
| `php artisan dishes:fix-categories [--dry-run]` | Régularise positions et catégories des plats depuis l'historique WordPress (`scripts/fix_dish_categories.php`) |
| `php artisan test` | Tests (SQLite en mémoire, WordPress simulé) |

## Déploiement

- La racine web doit pointer sur le dossier `public/`.
- `storage/` et `bootstrap/cache/` doivent être accessibles en écriture ; `public/assets/IMG/` reçoit les images des menus.
- En production : `APP_ENV=production`, `APP_DEBUG=false`, puis `php artisan config:cache route:cache view:cache`.
- SMS automatique : une tâche cron chaque minute `php artisan schedule:run` envoie le SMS le lundi
  à l'heure réglée dans l'admin (*Paramètres → Heure d'envoi du SMS*).
  Alternative : garder une tâche cron à heure fixe qui lance directement `php artisan sms:send-orders` (pas les deux).
- Emails (invitations, mot de passe oublié) : configurer un vrai service d'envoi (`MAIL_MAILER`, `MAIL_HOST`…,
  `MAIL_FROM_ADDRESS`), par exemple Resend, Postmark ou Mailgun. Avec `MAIL_MAILER=log`, les emails sont seulement
  écrits dans `storage/logs/laravel.log`.

### Reprise des données alwaysdata sur un serveur Forge (MySQL 8)

Les dumps sont dans `storage/app/private/dumps/` (non versionnés) : `*-data.sql` (toutes les tables sauf `users`)
et `*-users.sql`. Les variantes `*-mysql8.sql` remplacent `DEFAULT current_timestamp()` sur les colonnes `DATE`
(syntaxe MariaDB refusée par MySQL 8) par `DEFAULT (curdate())`.

```bash
# depuis le poste : copier les dumps sur le serveur
scp storage/app/private/dumps/*-mysql8.sql forge@<ip>:~/
# sur le serveur
mysql -u forge -p <base> < alwaysdata-AAAAMMJJ-data-mysql8.sql
mysql -u forge -p <base> < alwaysdata-AAAAMMJJ-users-mysql8.sql
cd <site> && php artisan migrate --force   # ajoute email / remember_token, table password_reset_tokens…
php artisan admin:password "<nom>" --email=<email>
```

## Organisation

- `app/Http/Controllers` : pages (accueil, commandes, votes, admin)
- `app/Http/Controllers/Auth` : connexion, mot de passe oublié, choix du mot de passe (invitation)
- `app/Services` : `MenuService` (menu de la semaine, cache 4 h en base), `WordPressClient`/`WordPressPost`,
  `SmsSender` (passerelle textbee.dev)
- `app/Support/MenuCalendar.php` : règles horaires (commande le lundi jusqu'à 11h15, vote du lundi 12h45 au samedi)
- `resources/views` : vues Blade (Tailwind, Flowbite et Bootstrap Icons via CDN, comme l'ancienne version)
- `public/js` : scripts front
