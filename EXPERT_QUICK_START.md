# 🚀 Expert Module - Quick Start Guide

## 🎯 Résumé en 30 secondes

Tu travailles **UNIQUEMENT** avec le module Expert quand tu te connectes avec le rôle **ROLE_EXPERT**.

**Flux**:
1. **Agriculteur** crée une **Analyse** (demande)
2. **Expert** reçoit la demande → Prend en charge
3. **Expert** effectue un diagnostic IA
4. **Expert** génère des **Conseils**
5. **Agriculteur** reçoit les conseils

---

## 🔐 Authentification Expert

### Login
```
Email: expert@example.com
Mot de passe: password
Rôle: ROLE_EXPERT
```

### Dashboard Expert
```
URL: http://localhost:8000/expert/dashboard
```

---

## 📊 Entités Principales

### **Analyse** (Demande d'analyse)
- **Créée par**: Agriculteur
- **Gérée par**: Expert
- **Statuts**: en_attente → en_cours → terminee
- **Champs clés**:
  - `descriptionDemande`: Description du problème
  - `imageUrl`: Image du problème
  - `demandeur`: Agriculteur qui demande
  - `technicien`: Expert assigné
  - `ferme`: Ferme concernée
  - `animalCible` / `planteCible`: Cible (optionnel)
  - `aiDiagnosisResult`: Résultat du diagnostic IA
  - `aiConfidenceScore`: Score de confiance
  - `weatherData`: Données météo

### **Conseil** (Recommandation)
- **Créé par**: Expert
- **Lié à**: Analyse
- **Champs clés**:
  - `descriptionConseil`: Description du conseil
  - `prioriteRaw`: HAUTE 🔴 / MOYENNE 🟡 / BASSE 🟢

---

## 🛣️ Routes Principales

### Dashboard
```
GET /expert/dashboard
    → Affiche les stats et actions rapides
```

### Demandes en Attente
```
GET /expert/demandes-en-attente
    → Liste les demandes en attente
    
POST /expert/demande/{id}/prendre-en-charge
    → Prendre en charge une demande
```

### Analyses
```
GET /expert/analyses
    → Liste toutes les analyses
    
GET /expert/analyse/{id}
    → Détails d'une analyse
    
POST /expert/analyse/{id}/status/{status}
    → Changer le statut
```

### Diagnostic IA
```
POST /expert/analyse/{id}/diagnose
    → Diagnostic IA avec image
    
POST /expert/analyse/{id}/diagnose-text
    → Diagnostic IA sans image
    
GET /expert/analyse/{id}/ai-result
    → Voir le résultat du diagnostic
```

### Conseils
```
GET /expert/conseils
    → Liste tous les conseils
    
GET /expert/conseil/{id}
    → Détails d'un conseil
    
POST /expert/analyse/{id}/conseil/new
    → Créer un conseil pour une analyse
```

---

## 🤖 Services IA

### GroqService (Diagnostic IA)
```php
// Vision Diagnosis (avec image)
$result = $groqService->generateVisionDiagnostic($imageUrl, $contextData);

// Text Diagnosis (sans image)
$result = $groqService->generateTextDiagnostic($observation, $contextData);

// Retour: DiagnosisResult
// - condition: Condition identifiée
// - symptoms: Symptômes
// - treatment: Traitement
// - prevention: Prévention
// - urgency: Urgence
// - confidence: Score de confiance
```

### WeatherService (Météo)
```php
$weather = $weatherService->getWeather($location);
// Retour: Array avec température, humidité, précipitations, etc.
```

---

## 📱 Contrôleurs

### ExpertAnalyseController
```php
// Affiche les analyses
GET /expert/analyses
    → expert_analyses_list()

// Affiche les détails
GET /expert/analyse/{id}
    → expert_analyse_show()

// Demandes en attente
GET /expert/demandes-en-attente
    → expert_pending_requests()

// Prendre en charge
POST /expert/demande/{id}/prendre-en-charge
    → expert_take_request()

// Créer conseil
POST /expert/analyse/{id}/conseil/new
    → expert_analyse_conseil_new()

// Exporter PDF
GET /expert/analyse/{id}/export/pdf
    → expert_analyse_export_pdf()
```

### ExpertAIController
```php
// Diagnostic avec image
POST /expert/analyse/{id}/diagnose
    → diagnose()

// Diagnostic sans image
POST /expert/analyse/{id}/diagnose-text
    → diagnoseText()

// Voir le résultat
GET /expert/analyse/{id}/ai-result
    → showAiResult()
```

### ExpertConseilController
```php
// Liste des conseils
GET /expert/conseils
    → list()

// Détails d'un conseil
GET /expert/conseil/{id}
    → show()

// Créer un conseil
POST /expert/conseil/new
    → new()

// Modifier un conseil
POST /expert/conseil/{id}/edit
    → edit()

// Supprimer un conseil
POST /expert/conseil/{id}/delete
    → delete()
```

---

## 📊 Repositories

### AnalyseRepository
```php
// Demandes en attente
$pending = $analyseRepo->findPendingRequests();

// Compter en attente
$count = $analyseRepo->countPendingRequests();

// Par technicien
$analyses = $analyseRepo->findByTechnicienId($id);

// Compter par technicien ce mois
$count = $analyseRepo->countByTechnicienThisMonth($id);

// Compter par technicien (total)
$count = $analyseRepo->countByTechnicien($id);
```

### ConseilRepository
```php
// Par expert
$conseils = $conseilRepo->findByExpert($technicienId, $search, $priorite);

// Compter par expert
$count = $conseilRepo->countByTechnicien($id);

// Par priorité
$conseils = $conseilRepo->findByPriorite($priorite);

// Stats
$stats = $conseilRepo->getPriorityStats();
```

---

## 🔄 Workflow Complet

### Étape 1: Agriculteur crée une Analyse
```
Agriculteur → /agricole/dashboard
           → Crée une Analyse
           → Sélectionne Ferme, Animal/Plante, Description, Image
           → Statut: "en_attente"
```

### Étape 2: Expert reçoit la notification
```
Expert → /expert/dashboard
      → Voit "X demandes en attente"
```

### Étape 3: Expert prend en charge
```
Expert → /expert/demandes-en-attente
      → Clique "Prendre en charge"
      → Statut: "en_attente" → "en_cours"
```

### Étape 4: Expert effectue le diagnostic IA
```
Expert → /expert/analyse/{id}
      → Clique "Diagnostiquer"
      → POST /expert/analyse/{id}/diagnose (avec image)
         OU
      → POST /expert/analyse/{id}/diagnose-text (sans image)
      
      → IA génère le diagnostic
      → Météo récupérée
      → Résultat stocké
```

### Étape 5: Expert crée des Conseils
```
Expert → /expert/analyse/{id}
      → Clique "Créer Conseil"
      → POST /expert/analyse/{id}/conseil/new
      → Crée 1+ Conseils avec Priorité
      → Statut: "en_cours" → "terminee"
```

### Étape 6: Agriculteur reçoit les Conseils
```
Agriculteur → Notification
           → Voit les Conseils
           → Lit les recommandations
```

---

## 🔐 Sécurité

### Authentification
- **Rôle requis**: `ROLE_EXPERT`
- **Vérification**: `#[IsGranted('ROLE_EXPERT')]`

### Autorisation
- **Expert ne peut voir que ses propres Analyses**:
  ```php
  if ($analyse->getTechnicien() !== $this->getUser()) {
      throw $this->createAccessDeniedException();
  }
  ```

- **Expert ne peut voir que ses propres Conseils**:
  ```php
  if ($conseil->getAnalyse()->getTechnicien() !== $this->getUser()) {
      throw $this->createAccessDeniedException();
  }
  ```

---

## 📋 Statuts et Priorités

### Statuts Analyse
| Statut | Description |
|--------|-------------|
| `en_attente` | Demande reçue, en attente d'expert |
| `en_cours` | Expert en train de traiter |
| `terminee` | Diagnostic et conseils générés |
| `annulee` | Demande annulée |

### Priorités Conseil
| Priorité | Emoji | Signification |
|----------|-------|---------------|
| `HAUTE` | 🔴 | Action immédiate requise |
| `MOYENNE` | 🟡 | À surveiller |
| `BASSE` | 🟢 | Informatif |

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

# Tests Expert
php bin/phpunit tests/Functional/Controller/AnalyseControllerTest.php

# Test unique
php bin/phpunit tests/Functional/Controller/AnalyseControllerTest.php::testCanCreateAnalyse
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

## 📁 Fichiers Clés

### Contrôleurs
```
src/Controller/Web/ExpertAnalyseController.php
src/Controller/Web/ExpertAIController.php
src/Controller/Web/ExpertConseilController.php
```

### Entités
```
src/Entity/Analyse.php
src/Entity/Conseil.php
src/Entity/User.php
src/Entity/Ferme.php
src/Entity/Animal.php
src/Entity/Plante.php
```

### Services
```
src/Service/GroqService.php
src/Service/WeatherService.php
src/Service/NotificationService.php
```

### Repositories
```
src/Repository/AnalyseRepository.php
src/Repository/ConseilRepository.php
```

### Templates
```
templates/portal/expert/index.html.twig
templates/portal/expert/pending_requests.html.twig
templates/portal/expert/analyses.html.twig
templates/portal/expert/analyse_show.html.twig
templates/portal/expert/conseils.html.twig
templates/portal/expert/diagnose_text.html.twig
```

### Enums
```
src/Enum/Priorite.php
src/Enum/StatutAnalyse.php
```

---

## 🔗 Relations

```
User (Expert)
  ├─ analyses (OneToMany) → Analyse (technicien)
  └─ conseils (via Analyse)

Analyse
  ├─ demandeur (ManyToOne) → User (Agriculteur)
  ├─ technicien (ManyToOne) → User (Expert)
  ├─ ferme (ManyToOne) → Ferme
  ├─ animalCible (ManyToOne) → Animal
  ├─ planteCible (ManyToOne) → Plante
  └─ conseils (OneToMany) → Conseil

Conseil
  └─ analyse (ManyToOne) → Analyse
```

---

## 💡 Conseils

1. **Toujours vérifier le statut** avant de modifier une Analyse
2. **Créer au moins 1 Conseil** pour chaque Analyse terminée
3. **Utiliser les priorités** pour classer les conseils par urgence
4. **Exporter en PDF** pour les rapports officiels
5. **Vérifier les données météo** pour le contexte
6. **Utiliser le diagnostic IA** pour gagner du temps
7. **Notifier l'agriculteur** une fois les conseils générés

---

## 🆘 Troubleshooting

### Pas de demandes en attente
```
→ Vérifier que des Analyses ont été créées par des Agriculteurs
→ Vérifier le statut: doit être "en_attente"
```

### Diagnostic IA échoue
```
→ Vérifier que GROQ_API_KEY est configurée
→ Vérifier que l'image est valide (si vision diagnosis)
→ Vérifier la connexion Internet
```

### Météo non disponible
```
→ Vérifier que OPENWEATHER_API_KEY est configurée
→ Vérifier que la ferme a une localisation (lieu)
```

### Erreur d'autorisation
```
→ Vérifier que tu es connecté avec ROLE_EXPERT
→ Vérifier que tu es le technicien assigné à l'Analyse
```

---

## 📞 Support

Pour plus d'informations, consulte:
- `PROJECT_ARCHITECTURE.md` - Architecture complète
- `EXPERT_MODULE_WORKFLOW.md` - Workflow détaillé
- Code source: `src/Controller/Web/Expert*.php`

---

**Dernière mise à jour**: 6 mai 2026  
**Version**: 1.0  
**Statut**: Production
