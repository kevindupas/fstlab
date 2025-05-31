# FST-LAB

FST-LAB est une plateforme web dédiée à la création et à la gestion d'expériences de recherche impliquant des médias visuels et sonores. Cette application permet aux chercheurs de créer des expériences interactives, de collecter des données et d'analyser les résultats.

## 🚀 Fonctionnalités

### Interface publique
- **Catalogue d'expériences** : Navigation et recherche dans les expériences disponibles
- **Sessions d'expérience** : Interface interactive pour les participants
- **Support multimédia** : Gestion d'images et de sons
- **Interface multilingue** : Support français et anglais
- **Responsive design** : Compatible desktop et tablette

### Panel d'administration
- **Gestion des utilisateurs** : Système de rôles hiérarchiques
- **Création d'expériences** : Interface complète de configuration
- **Analyse des données** : Visualisation et export des résultats
- **Gestion des permissions** : Contrôle d'accès granulaire

### Système de rôles
- **Superviseur** : Gestion globale des utilisateurs
- **Expérimentateur principal** : Création et gestion d'expériences
- **Expérimentateur secondaire** : Conduite d'expériences déléguées

## 🛠️ Stack technique

- **Backend** : Laravel 11 (PHP 8.2+)
- **Frontend** : React 18 + Vite
- **Base de données** : MySQL/MariaDB/PostgreSQL/SQLite
- **Styling** : Tailwind CSS
- **Admin Panel** : Filament 3
- **Authentification** : Laravel Sanctum
- **Permissions** : Spatie Permission
- **Media** : Spatie Media Library
- **Internationalisation** : React i18next + Laravel Chained Translator

## 📋 Prérequis

- PHP >= 8.2
- Node.js >= 18
- Composer
- Base de données (MySQL/MariaDB/PostgreSQL/SQLite)

## 🔧 Installation

### 1. Cloner le repository

```bash
git clone <repository-url>
cd fst-lab
```

### 2. Installation des dépendances PHP

```bash
composer install
```

### 3. Installation des dépendances Node.js

```bash
npm install
```

### 4. Configuration de l'environnement

```bash
cp .env.example .env
```

Éditer le fichier `.env` avec vos configurations :

```env
APP_NAME="FST-LAB"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fst_lab
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Configuration mail (optionnel)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@fst-lab.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Génération de la clé d'application

```bash
php artisan key:generate
```

### 6. Configuration de la base de données

```bash
php artisan migrate
php artisan db:seed
```

### 7. Création du lien symbolique pour le stockage

```bash
php artisan storage:link
```

### 8. Compilation des assets

```bash
npm run build
# ou pour le développement
npm run dev
```

## 🚀 Lancement

### Développement

```bash
# Terminal 1 - Serveur Laravel
php artisan serve

# Terminal 2 - Build frontend en mode watch
npm run dev
```

### Production

```bash
npm run build
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📁 Structure du projet

```
fst-lab/
├── app/
│   ├── Filament/           # Interface d'administration
│   ├── Http/Controllers/   # Contrôleurs API et web
│   ├── Models/            # Modèles Eloquent
│   └── ...
├── database/
│   ├── migrations/        # Migrations de base de données
│   ├── seeders/          # Données de test
│   └── factories/        # Factories pour les tests
├── resources/
│   ├── js/               # Application React
│   │   ├── Components/   # Composants React
│   │   ├── Pages/        # Pages de l'application
│   │   ├── Contexts/     # Contextes React
│   │   └── Utils/        # Utilitaires
│   ├── css/              # Styles CSS
│   └── views/            # Vues Blade
├── routes/
│   ├── api.php           # Routes API
│   └── web.php           # Routes web
└── ...
```

## 🔑 Comptes par défaut

Après avoir exécuté les seeders :

- **Superviseur** : 
  - Email : `dupas.kevin@gmail.com`
  - Mot de passe : `password`

## 🎯 Utilisation

### Création d'une expérience

1. Connectez-vous au panel d'administration (`/admin`)
2. Naviguez vers "Expériences" → "Créer"
3. Configurez les paramètres de l'expérience
4. Uploadez les médias (images/sons)
5. Activez l'expérience

### Conduite d'une expérience

1. Les participants accèdent via le lien public
2. Enregistrement automatique avec ID participant
3. Interface interactive de catégorisation
4. Sauvegarde automatique des données

### Analyse des résultats

1. Accès via le panel d'administration
2. Visualisation des sessions et données
3. Export CSV/Excel des résultats

## 🔧 Configuration avancée

### Cache

```bash
# Redis (recommandé en production)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue

```bash
# Database queue
QUEUE_CONNECTION=database

# Lancer le worker
php artisan queue:work
```

### Mail

```bash
# SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

## 🧪 Tests

```bash
# Tests PHPUnit
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## 📊 Export de données

Les données peuvent être exportées via :
- Interface d'administration Filament
- Route API dédiée (`/admin/export-sessions`)
- Commandes Artisan personnalisées

## 🌍 Internationalisation

### Ajouter une nouvelle langue

1. Créer les fichiers de traduction dans `lang/`
2. Mettre à jour la configuration dans `config/filament-translation-manager.php`
3. Ajouter la langue dans `FloatingLanguageButton.jsx`

### Gérer les traductions

Interface de gestion via Filament Translation Manager accessible dans le panel d'administration.

## 🔒 Sécurité

- Authentification via Laravel Sanctum
- Validation CSRF
- Sanitisation des entrées utilisateur
- Contrôle d'accès basé sur les rôles (RBAC)
- Protection contre l'inspection des éléments en mode expérience

## 📈 Performance

### Optimisations recommandées

```bash
# Optimisation Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimisation base de données
php artisan migrate --force
```

### Mise en cache

- Cache des traductions
- Cache des permissions
- Cache des configurations Laravel

## 🚨 Dépannage

### Problèmes courants

1. **Erreur de permissions de fichiers**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

2. **Problème de clé d'application**
   ```bash
   php artisan key:generate
   ```

3. **Erreurs de migration**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Assets non compilés**
   ```bash
   npm run build
   ```

## 🤝 Contribution

1. Fork du projet
2. Création d'une branche feature (`git checkout -b feature/amazing-feature`)
3. Commit des changements (`git commit -m 'Add amazing feature'`)
4. Push vers la branche (`git push origin feature/amazing-feature`)
5. Ouverture d'une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Pour toute question ou problème :
- Créer une issue sur GitHub
- Consulter la documentation Filament : https://filamentphp.com
- Documentation Laravel : https://laravel.com/docs

## 🔄 Changelog

Voir `/changelog` sur l'application pour les dernières mises à jour et fonctionnalités.

---

**FST-LAB** - Plateforme de recherche expérimentale développée avec ❤️ par l'équipe de recherche.
