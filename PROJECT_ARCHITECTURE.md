# 🌾 FarmAI - Architecture Complète du Projet

## 📋 Vue d'ensemble
**Type**: Application Symfony 6.4 (PHP 8.1+)  
**Base de données**: MySQL/MariaDB  
**Authentification**: Form-based + Face Recognition  
**IA**: Groq API (LLaMA/Mixtral) + Python API (Port 5000)  
**Météo**: OpenWeather API

---

## 🏗️ Architecture des Entités

### Hiérarchie Relationnelle

```
User (1) ──→ (N) Ferme
             ├─→ (N) Plante
             │   └─→ (N) Arrosage
             └─→ (N) Animal
                 └─→ (N) SuiviSante

Analyse (1) ──→ (N) Conseil
```

### Entités Principales

#### 1. **User** (Utilisateur)
- **Rôles**: ROLE_ADMIN, ROLE_EXPERT, ROLE_AGRICOLE, ROLE_FOURNISSEUR
- **Champs clés**:
  - `id_user` (PK)
  - `email`, `password`, `nom`, `prenom`
  - `cin`, `telephone`, `adresse`
  - `latitude`, `longitude` (géolocalisation)
  - `imageUrl` (photo de profil)
  - `createdAt`, `updatedAt`
  - `resetCode`, `resetCodeExpiresAt` (réinitialisation mot de passe)
- **Relations**:
  - `fermes` (OneToMany) → Ferme
  - `userFaces` (OneToMany) → UserFace (reconnaissance faciale)
  - `userLogs` (OneToMany) → UserLog (audit)
  - `analyses` (OneToMany) → Analyse (technicien)

#### 2. **Ferme** (Farm - Entité Centrale)
- **Champs clés**:
  - `id_ferme` (PK)
  - `nom_ferme` (nom de la ferme)
  - `lieu` (localisation)
  - `surface` (hectares)
  - `latitude`, `longitude` (coordonnées GPS)
- **Relations**:
  - `user` (ManyToOne) → User (propriétaire)
  - `plantes` (OneToMany) → Plante
  - `animals` (OneToMany) → Animal
  - `analyses` (OneToMany) → Analyse

#### 3. **Plante** (Plant)
- **Champs clés**:
  - `id_plante` (PK)
  - `nom_espece` (espèce)
  - `cycle_vie` (cycle de vie)
  - `quantite` (nombre de plants)
- **Relations**:
  - `ferme` (ManyToOne) → Ferme
  - `arrosages` (OneToMany) → Arrosage (cascade delete)

#### 4. **Arrosage** (Watering)
- **Champs clés**:
  - `id_arrosage` (PK)
  - `dateArrosage` (date/heure d'arrosage)
- **Relations**:
  - `plante` (ManyToOne) → Plante

#### 5. **Animal** (Animal)
- **Champs clés**:
  - `id_animal` (PK)
  - `espece` (espèce)
  - `etat_sante` (état de santé)
  - `date_naissance` (date de naissance)
- **Relations**:
  - `ferme` (ManyToOne) → Ferme

#### 6. **SuiviSante** (Health Monitoring)
- **Champs clés**:
  - `id` (PK)
  - `dateConsultation` (date de consultation)
  - `diagnostic` (diagnostic)
  - `etatAuMoment` (état au moment)
  - `type` (type de suivi)
- **Relations**:
  - `animal` (ManyToOne) → Animal
  - `performedBy` (ManyToOne) → User (qui a effectué)

#### 7. **Analyse** (Analysis - Module Expert)
- **Champs clés**:
  - `id_analyse` (PK)
  - `dateAnalyse` (date de l'analyse)
  - `resultatTechnique` (résultat technique)
  - `imageUrl` (image de l'analyse)
  - `statut` (en_attente, en_cours, terminee, annulee)
  - `descriptionDemande` (description de la demande)
  - `weatherData` (données météo JSON)
  - `weatherFetchedAt` (date de récupération météo)
  - **AI Diagnosis Fields**:
    - `aiDiagnosisResult` (résultat du diagnostic IA)
    - `aiDiagnosisDate` (date du diagnostic IA)
    - `aiConfidenceScore` (score de confiance)
    - `diagnosisMode` (mode de diagnostic)
- **Relations**:
  - `ferme` (ManyToOne) → Ferme
  - `technicien` (ManyToOne) → User (technicien assigné)
  - `demandeur` (ManyToOne) → User (qui a demandé)
  - `animalCible` (ManyToOne) → Animal (animal ciblé)
  - `planteCible` (ManyToOne) → Plante (plante ciblée)
  - `conseils` (OneToMany) → Conseil (cascade delete)

#### 8. **Conseil** (Advice/Recommendation - Module Expert)
- **Champs clés**:
  - `id_conseil` (PK)
  - `descriptionConseil` (description du conseil)
  - `prioriteRaw` (HAUTE, MOYENNE, BASSE)
- **Relations**:
  - `analyse` (ManyToOne) → Analyse

#### 9. **Notification**
- Système de notifications pour les utilisateurs

#### 10. **UserFace** (Face Recognition)
- Données de reconnaissance faciale

#### 11. **UserLog** (Audit)
- Logs d'activité des utilisateurs

---

## 📁 Structure du Projet

```
src/
├── Controller/
│   ├── AnalyseController.php          # CRUD Analyse
│   ├── ConseilController.php          # CRUD Conseil
│   ├── AnimalController.php           # CRUD Animal
│   ├── PlanteController.php           # CRUD Plante
│   ├── FermeController.php            # CRUD Ferme
│   ├── ArrosageController.php         # CRUD Arrosage
│   ├── ChatbotController.php          # Chatbot IA
│   ├── Api/
│   │   ├── ApiSanteController.php     # API Santé
│   │   ├── PlanteApiController.php    # API Plante
│   │   ├── UserController.php         # API User
│   │   └── AuthController.php         # API Auth
│   ├── Admin/                         # Contrôleurs Admin
│   ├── Web/                           # Contrôleurs Web
│   └── ERP/                           # Contrôleurs ERP
├── Entity/
│   ├── User.php
│   ├── Ferme.php
│   ├── Plante.php
│   ├── Animal.php
│   ├── Arrosage.php
│   ├── SuiviSante.php
│   ├── Analyse.php
│   ├── Conseil.php
│   ├── Notification.php
│   ├── UserFace.php
│   ├── UserLog.php
│   └── ERP/
├── Service/
│   ├── GroqService.php                # Diagnostic IA (vision + texte)
│   ├── GroqChatService.php            # Chatbot IA
│   ├── WeatherService.php             # Météo
│   ├── PlantService.php               # Service Plante
│   ├── FermeManager.php               # Gestion Ferme
│   ├── UserManager.php                # Gestion User
│   ├── FaceEnrollmentService.php      # Enrôlement facial
│   ├── PythonFaceRecognitionService.php # Reconnaissance faciale
│   ├── NotificationService.php        # Notifications
│   ├── ReportService.php              # Rapports
│   ├── UserLogService.php             # Audit
│   ├── AuthService.php                # Authentification
│   ├── EmailSecurityService.php       # Email sécurisé
│   ├── CaptchaService.php             # CAPTCHA
│   ├── PerenualService.php            # API Plantes
│   ├── FarmPredictor.php              # Prédictions
│   ├── OpenAIChatService.php          # Chat OpenAI
│   └── ERP/
├── Repository/
│   ├── AnalyseRepository.php
│   ├── ConseilRepository.php
│   ├── AnimalRepository.php
│   ├── PlanteRepository.php
│   ├── FermeRepository.php
│   ├── ArrosageRepository.php
│   ├── SuiviSanteRepository.php
│   ├── UserRepository.php
│   └── ERP/
├── Form/
│   ├── AnalyseType.php
│   ├── ConseilType.php
│   ├── AnimalType.php
│   ├── PlanteType.php
│   ├── FermeType.php
│   ├── RegistrationFormType.php
│   ├── UserAdminType.php
│   └── ERP/
├── Enum/
│   ├── Priorite.php                   # HAUTE, MOYENNE, BASSE
│   └── StatutAnalyse.php              # en_attente, en_cours, terminee, annulee
├── Security/
│   ├── LoginSuccessHandler.php
│   ├── LoginFailureHandler.php
│   └── LegacyPasswordHasher.php
└── Kernel.php

templates/
├── conseil/
│   ├── index.html.twig                # Liste des conseils
│   ├── show.html.twig                 # Détail d'un conseil
│   ├── new.html.twig                  # Créer un conseil
│   └── edit.html.twig                 # Modifier un conseil
├── analyse/
│   ├── index.html.twig
│   ├── show.html.twig
│   ├── new.html.twig
│   └── edit.html.twig
├── animal/
├── plante/
├── ferme/
├── layouts/
│   ├── admin.html.twig
│   ├── base.html.twig
│   └── app_layout.html.twig
└── ...

config/
├── packages/
│   ├── doctrine.yaml                  # Configuration Doctrine
│   ├── security.yaml                  # Configuration Sécurité
│   ├── framework.yaml
│   └── ...
├── services.yaml                      # Configuration Services
└── routes.yaml                        # Configuration Routes

migrations/
├── Version20260408110343.php
├── Version20260408110851.php
└── ... (19 migrations)

tests/
├── Unit/
├── Functional/
├── Repository/
├── Service/
├── Entity/
└── Staging/
    ├── ExpertAIConnectionTest.php
    └── ExpertModuleHandshakeTest.php
```

---

## 🔄 Flux de Travail du Module Expert

### 1. **Création d'une Analyse**
```
User (Agriculteur) → Crée une Analyse
  ├─ Sélectionne une Ferme
  ├─ Sélectionne Animal/Plante cible (optionnel)
  ├─ Décrit le problème
  └─ Télécharge une image (optionnel)
```

### 2. **Traitement par IA (GroqService)**
```
Analyse → GroqService.generateVisionDiagnostic() ou generateTextDiagnostic()
  ├─ Récupère les données météo (WeatherService)
  ├─ Envoie à Groq API (LLaMA/Mixtral)
  ├─ Reçoit le diagnostic IA
  └─ Stocke: aiDiagnosisResult, aiConfidenceScore, diagnosisMode
```

### 3. **Génération de Conseils**
```
Analyse (avec diagnostic IA) → Conseils générés automatiquement
  ├─ Conseil 1: Description + Priorité HAUTE
  ├─ Conseil 2: Description + Priorité MOYENNE
  └─ Conseil 3: Description + Priorité BASSE
```

### 4. **Affichage**
```
ConseilController.index() → Liste tous les conseils
  ├─ Filtrage par priorité
  ├─ Recherche par description
  └─ Statistiques par priorité
```

---

## 🔐 Sécurité & Authentification

### Rôles
- **ROLE_ADMIN**: Accès complet
- **ROLE_EXPERT**: Accès au module expert (Analyse/Conseil)
- **ROLE_AGRICOLE**: Accès aux fermes et analyses
- **ROLE_FOURNISSEUR**: Accès limité

### Authentification
- **Form Login**: Email + Mot de passe
- **Face Recognition**: Via Python API (port 5000)
- **CSRF Protection**: Tokens CSRF sur tous les formulaires

### Access Control
```yaml
/api/signup, /api/login, /captcha, /login, /signup → PUBLIC_ACCESS
/admin/dashboard → ROLE_ADMIN
/expert/dashboard → ROLE_EXPERT
/agricole/dashboard → ROLE_AGRICOLE
/fournisseur/dashboard → ROLE_FOURNISSEUR
```

---

## 🗄️ Base de Données

### Configuration
- **Type**: MySQL/MariaDB
- **Charset**: utf8mb4
- **Timezone**: UTC (synchronisé PHP ↔ MySQL)
- **URL**: `mysql://root:@127.0.0.1:3306/farmai?serverVersion=10.4.32-MariaDB&charset=utf8mb4`

### Migrations
- 19 migrations appliquées (Version20260408110343 → Version20260506150156)
- Gestion des relations OneToMany, ManyToOne
- Cascade delete pour les relations critiques

---

## 🤖 Services IA

### 1. **GroqService** (Diagnostic IA)
```php
// Vision Diagnosis (avec image)
$result = $groqService->generateVisionDiagnostic($imageUrl, $contextData);

// Text Diagnosis (sans image)
$result = $groqService->generateTextDiagnostic($observation, $contextData);

// Executive Summary
$summary = $groqService->generateExecutiveSummary($analysisData);
```

**Retour**: `DiagnosisResult` avec:
- `diagnosis` (diagnostic)
- `confidence` (score de confiance)
- `recommendations` (recommandations)
- `mode` (vision/text)

### 2. **GroqChatService** (Chatbot)
```php
$response = $groqChatService->generateResponse($userMessage);
// Maintient l'historique de conversation en session
```

### 3. **WeatherService** (Météo)
```php
$weather = $weatherService->getWeatherData($latitude, $longitude);
// Retourne: température, humidité, précipitations, etc.
```

---

## 📊 Enums

### **Priorite**
```php
enum Priorite: string {
    case HAUTE = 'HAUTE';      // 🔴 Danger
    case MOYENNE = 'MOYENNE';  // 🟡 Warning
    case BASSE = 'BASSE';      // 🟢 Success
}
```

### **StatutAnalyse**
```php
enum StatutAnalyse: string {
    case EN_ATTENTE = 'en_attente';
    case EN_COURS = 'en_cours';
    case TERMINEE = 'terminee';
    case ANNULEE = 'annulee';
}
```

---

## 🔌 Configuration Environnement

### `.env` Requis
```env
# Symfony
APP_ENV=dev
APP_SECRET=...

# Database
DATABASE_URL=mysql://root:@127.0.0.1:3306/farmai?serverVersion=10.4.32-MariaDB&charset=utf8mb4

# Groq AI
GROQ_API_KEY=your_groq_key
GROQ_MODEL=meta-llama/llama-4-scout-17b-16e-instruct

# Weather
OPENWEATHER_API_KEY=your_openweather_key
OPENWEATHER_URL=https://api.openweathermap.org/data/2.5

# Python API (Face Recognition)
PYTHON_API_URL=http://localhost:5000

# Messenger
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0

# Mailer
MAILER_DSN=null://null
```

---

## 🚀 Commandes Utiles

### Installation
```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Démarrage
```bash
# Serveur PHP
php -S localhost:8000 -t public public/router.php

# Python API (Face Recognition)
cd python_api && source venv/bin/activate && python app.py
```

### Tests
```bash
# Tous les tests
php bin/phpunit

# Tests Staging (nécessite Python API sur port 5000)
php bin/phpunit tests/Staging/

# Test unique
php bin/phpunit tests/Staging/ExpertAIConnectionTest.php
```

### Migrations
```bash
# Créer une migration
php bin/console make:migration

# Appliquer les migrations
php bin/console doctrine:migrations:migrate

# Rollback
php bin/console doctrine:migrations:migrate prev
```

---

## 📈 Statistiques du Projet

- **Entités**: 11 principales
- **Contrôleurs**: 10+ (Web + API + Admin + ERP)
- **Services**: 18+
- **Repositories**: 13+
- **Formulaires**: 9+
- **Templates**: 30+
- **Migrations**: 19
- **Tests**: Unit + Functional + Staging

---

## 🎯 Points Clés

1. **Module Expert Central**: Analyse → Diagnostic IA → Conseils
2. **Hiérarchie Ferme**: Ferme → Plantes/Animaux → Suivi
3. **IA Intégrée**: Groq (diagnostic) + Python (reconnaissance faciale)
4. **Sécurité Multi-couches**: Rôles, CSRF, Face Recognition
5. **Audit Complet**: UserLog pour tracer toutes les actions
6. **Météo Intégrée**: Données météo stockées avec chaque analyse
7. **Timezone UTC**: Synchronisation PHP ↔ MySQL

---

## 🔗 Relations Clés

```
User (1) ──→ (N) Ferme (1) ──→ (N) Plante (1) ──→ (N) Arrosage
                    ├─→ (N) Animal (1) ──→ (N) SuiviSante
                    └─→ (N) Analyse (1) ──→ (N) Conseil

User (Technicien) ──→ Analyse ←── User (Demandeur)
                         ├─→ Ferme
                         ├─→ Animal/Plante (cible)
                         └─→ Conseils
```

---

**Dernière mise à jour**: 6 mai 2026  
**Version Symfony**: 6.4  
**PHP**: 8.1+  
**Base de données**: MySQL/MariaDB 10.4.32
