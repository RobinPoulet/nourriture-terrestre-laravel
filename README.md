# Nourriture Terrestre (Laravel)

Portage sur Laravel 13 de l'application [nourriture-terrestre](https://github.com/RobinPoulet/nourriture-terrestre) :
menu de la semaine récupéré depuis le WordPress de Nourriture Terrestre, prise de commande le lundi matin,
récapitulatif envoyé par SMS, vote et classement des plats, administration.

L'application utilise **la même base de données** que l'ancienne version : les tables et colonnes sont identiques
(colonnes historiques en majuscules lues en minuscules via `PDO::ATTR_CASE`, voir `config/database.php`).

## Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate
# renseigner DB_*, API_KEY_SMS, SMS_DEVICE_ID, SMS_RECIPIENT, PUSHER_* dans .env
php artisan migrate        # base vierge : crée les tables ; base existante : les migrations sont ignorées
php artisan serve
```

Pour une base locale vierge : `php artisan db:seed` crée quelques utilisateurs.

## Commandes

| Commande | Rôle (ancien script) |
|---|---|
| `php artisan sms:send-orders` | Envoie le récapitulatif des commandes du jour par SMS et notifie la page via Pusher (`cron/send_sms_cron.php`) |
| `php artisan admin:password "<nom>"` | Définit le mot de passe d'un utilisateur et le passe admin (`scripts/set_admin_password.php`) |
| `php artisan dishes:fix-categories [--dry-run]` | Régularise positions et catégories des plats depuis l'historique WordPress (`scripts/fix_dish_categories.php`) |
| `php artisan test` | Tests (SQLite en mémoire, WordPress simulé) |

## Déploiement

- La racine web doit pointer sur le dossier `public/`.
- `storage/` et `bootstrap/cache/` doivent être accessibles en écriture ; `public/assets/IMG/` reçoit les images des menus.
- En production : `APP_ENV=production`, `APP_DEBUG=false`, puis `php artisan config:cache route:cache view:cache`.
- SMS automatique : une tâche cron chaque minute `php artisan schedule:run` envoie le SMS le lundi
  à l'heure réglée dans l'admin (*Paramètres → Heure d'envoi du SMS*).
  Alternative : garder une tâche cron à heure fixe qui lance directement `php artisan sms:send-orders` (pas les deux).
- Le cookie d'appareil `selected_user` n'est pas chiffré par Laravel, pour que les appareils déjà identifiés
  par l'ancienne application le restent.

## Organisation

- `app/Http/Controllers` : pages (accueil, commandes, votes, admin)
- `app/Services` : `MenuService` (menu de la semaine, cache 4 h en base), `WordPressClient`/`WordPressPost`,
  `DeviceAuth` (identification de l'appareil par cookie), `SmsSender` (passerelle textbee.dev)
- `app/Support/MenuCalendar.php` : règles horaires (commande le lundi jusqu'à 11h15, vote du lundi 12h45 au samedi)
- `resources/views` : vues Blade (Tailwind, Flowbite et Bootstrap Icons via CDN, comme l'ancienne version)
- `public/js` : scripts front
