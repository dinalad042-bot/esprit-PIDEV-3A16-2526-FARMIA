# 🔬 Module Expert - Workflow Complet

## 📋 Vue d'ensemble

Le **Module Expert** est le cœur du système FarmAI. C'est un système de **demande-réponse** où:
- **Agriculteur** (ROLE_AGRICOLE) → Crée une **Analyse** (demande)
- **Expert** (ROLE_EXPERT) → Reçoit la demande → Effectue un diagnostic IA → Génère des **Conseils**

---

## 🔄 Flux Complet

```
┌─────────────────────────────────────────────────────────────────┐
│                    AGRICULTEUR (ROLE_AGRICOLE)                  │
│                                                                 │
│  1. Crée une Analyse (demande)                                  │
│     - Sélectionne sa Ferme                                      │
│     - Sélectionne Animal/Plante cible (optionnel)               │
│     - Décrit le problème                                        │
│     - Télécharge une image (optionnel)                          │
│     - Statut: "en_attente"                                      │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    EXPERT (ROLE_EXPERT)                         │
│                                                                 │
│  2. Reçoit la demande                                           │
│     - Voit "Demandes en attente" sur le dashboard               │
│     - Clique "Prendre en charge"                                │
│     - Statut: "en_cours"                                        │
│     - Expert assigné: $technicien = $user (Expert)              │
│                                                                 │
│  3. Effectue le diagnostic IA                                   │
│     - Vision Diagnosis (avec image)                             │
│     - Text Diagnosis (sans image)                               │
│     - Récupère données météo                                    │
│     - Stocke: aiDiagnosisResult, aiConfidenceScore              │
│                                                                 │
│  4. Génère des Conseils                                         │
│     - Crée 1+ Conseils liés à l'Analyse                         │
│     - Chaque Conseil a une Priorité (HAUTE/MOYENNE/BASSE)       │
│     - Statut: "terminee"                                        │
│                                                                 │
│  5. Envoie les Conseils à l'Agriculteur                         │
│     - Notification envoyée                                      │
│     - Agriculteur voit les Conseils                             │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Entités Principales du Module Expert

### 1. **Analyse** (Demande d'analyse)
**Créée par**: Agriculteur  
**Gérée par**: Expert

```php
class Analyse {
    // Identifiant
    private ?int $id = null;
    
    // Demande
    private ?string $descriptionDemande = null;      // Description du problème
    private ?string $imageUrl = null;                // Image du problème
    private ?User $demandeur = null;                 // Qui a demandé (Agriculteur)
    
    // Contexte
    private ?Ferme $ferme = null;                    // Ferme concernée
    private ?Animal $animalCible = null;             // Animal ciblé (optionnel)
    private ?Plante $planteCible = null;             // Plante ciblée (optionnel)
    
    // Traitement par Expert
    private ?User $technicien = null;                // Expert assigné
    private string $statut = 'en_attente';           // en_attente, en_cours, terminee, annulee
    
    // Diagnostic Technique
    private ?string $resultatTechnique = null;       // Résultat technique de l'expert
    
    // Diagnostic IA
    private ?string $aiDiagnosisResult = null;       // Résultat du diagnostic IA (JSON)
    private ?\DateTimeInterface $aiDiagnosisDate = null;
    private ?string $aiConfidenceScore = null;       // Score de confiance (0-100%)
    private ?string $diagnosisMode = null;           // 'vision' ou 'text'
    
    // Données Météo
    private ?array $weatherData = null;              // Données météo (JSON)
    private ?\DateTimeInterface $weatherFetchedAt = null;
    
    // Conseils générés
    private Collection $conseils;                    // OneToMany → Conseil
    
    // Timestamps
    private ?\DateTimeInterface $dateAnalyse = null;
}
```

### 2. **Conseil** (Recommandation)
**Créé par**: Expert  
**Lié à**: Analyse

```php
class Conseil {
    // Identifiant
    private ?int $id = null;
    
    // Contenu
    private ?string $descriptionConseil = null;      // Description du conseil
    private ?string $prioriteRaw = 'MOYENNE';        // HAUTE, MOYENNE, BASSE
    
    // Relation
    private ?Analyse $analyse = null;                // ManyToOne → Analyse
}
```

### 3. **Priorité** (Enum)
```php
enum Priorite: string {
    case HAUTE = 'HAUTE';           // 🔴 Danger - Action immédiate
    case MOYENNE = 'MOYENNE';       // 🟡 Warning - À surveiller
    case BASSE = 'BASSE';           // 🟢 Success - Informatif
}
```

### 4. **Statut Analyse** (Enum)
```php
enum StatutAnalyse: string {
    case EN_ATTENTE = 'en_attente';   // Demande reçue, en attente d'expert
    case EN_COURS = 'en_cours';       // Expert en train de traiter
    case TERMINEE = 'terminee';       // Diagnostic et conseils générés
    case ANNULEE = 'annulee';         // Demande annulée
}
```

---

## 🛣️ Routes Expert

### Dashboard
```
GET /expert/dashboard
    → Affiche les stats de l'expert
    → Analyses ce mois, Total, Conseils, Demandes en attente
```

### Demandes en Attente
```
GET /expert/demandes-en-attente
    → Liste toutes les Analyses avec statut "en_attente"
    → Affiche: Demandeur, Ferme, Animal/Plante, Description, Image
    
POST /expert/demande/{id}/prendre-en-charge
    → Expert prend en charge la demande
    → Statut: en_attente → en_cours
    → Technicien assigné: $analyse->setTechnicien($user)
```

### Analyses
```
GET /expert/analyses
    → Liste toutes les Analyses de l'expert
    → Filtrage par statut, recherche
    
GET /expert/analyse/{id}
    → Affiche les détails d'une Analyse
    → Boutons: Diagnostiquer (IA), Créer Conseil, Exporter PDF
    
GET /expert/analyse/{id}/edit
POST /expert/analyse/{id}/edit
    → Modifier une Analyse
    
POST /expert/analyse/{id}/delete
    → Supprimer une Analyse
    
POST /expert/analyse/{id}/status/{status}
    → Changer le statut (en_attente, en_cours, terminee, annulee)
```

### Diagnostic IA
```
POST /expert/analyse/{id}/diagnose
    → Diagnostic IA avec image (Vision)
    → Récupère données météo
    → Stocke: aiDiagnosisResult, aiConfidenceScore, aiDiagnosisDate
    
GET /expert/analyse/{id}/diagnose-text
POST /expert/analyse/{id}/diagnose-text
    → Diagnostic IA sans image (Text)
    → Demande une description des symptômes
    
GET /expert/analyse/{id}/ai-result
    → Affiche le résultat du diagnostic IA
    
POST /expert/analyse/{id}/diagnose/json
    → API JSON pour le diagnostic IA
```

### Conseils
```
GET /expert/conseils
    → Liste tous les Conseils de l'expert
    → Filtrage par priorité, recherche
    
GET /expert/conseil/{id}
    → Affiche les détails d'un Conseil
    
GET /expert/analyse/{id}/conseil/new
POST /expert/analyse/{id}/conseil/new
    → Créer un nouveau Conseil lié à une Analyse
    
GET /expert/conseil/{id}/edit
POST /expert/conseil/{id}/edit
    → Modifier un Conseil
    
POST /expert/conseil/{id}/delete
    → Supprimer un Conseil
```

### Export
```
GET /expert/analyse/{id}/export/pdf
    → Exporte l'Analyse en PDF
```

---

## 🔐 Sécurité

### Authentification
- **Rôle requis**: `ROLE_EXPERT`
- **Vérification**: `#[IsGranted('ROLE_EXPERT')]` sur tous les contrôleurs

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

## 🤖 Services IA

### 1. **GroqService** (Diagnostic IA)

#### Vision Diagnosis (avec image)
```php
$diagnosisResult = $groqService->generateVisionDiagnostic($imageUrl, $contextData);

// Retour: DiagnosisResult
// - condition: Condition identifiée
// - symptoms: Symptômes détectés
// - treatment: Traitement recommandé
// - prevention: Prévention
// - urgency: Niveau d'urgence
// - needsExpert: Besoin d'expert humain?
// - confidence: Score de confiance (0-100%)
// - rawResponse: Réponse brute de Groq
```

#### Text Diagnosis (sans image)
```php
$diagnosisResult = $groqService->generateTextDiagnostic($observation, $contextData);

// Même retour que Vision Diagnosis
```

#### Context Data
```php
$contextData = [
    'farm' => $analyse->getFerme()?->getNomFerme(),
    'location' => $analyse->getFerme()?->getLieu(),
    'target_plant' => $analyse->getPlanteCible()?->getNomEspece(),
    'target_animal' => $analyse->getAnimalCible()?->getEspece(),
];
```

### 2. **WeatherService** (Données Météo)
```php
$weather = $weatherService->getWeather($location);

// Retour: Array
// - temperature
// - humidity
// - precipitation
// - wind_speed
// - conditions
// - etc.
```

---

## 📊 Repositories

### AnalyseRepository
```php
// Trouver les demandes en attente
$pendingRequests = $analyseRepo->findPendingRequests();

// Compter les demandes en attente
$count = $analyseRepo->countPendingRequests();

// Trouver par technicien
$analyses = $analyseRepo->findByTechnicienId($technicienId);

// Compter par technicien ce mois
$count = $analyseRepo->countByTechnicienThisMonth($technicienId);

// Compter par technicien (total)
$count = $analyseRepo->countByTechnicien($technicienId);

// Trouver par demandeur
$analyses = $analyseRepo->findByDemandeur($userId);

// Trouver par ferme
$analyses = $analyseRepo->findByFermeId($fermeId);

// Rechercher
$analyses = $analyseRepo->search($term);
```

### ConseilRepository
```php
// Trouver par expert (technicien)
$conseils = $conseilRepo->findByExpert($technicienId, $search, $priorite);

// Compter par technicien
$count = $conseilRepo->countByTechnicien($technicienId);

// Compter par technicien et priorité
$count = $conseilRepo->countByTechnicienAndPriorite($technicienId, $priorite);

// Trouver par analyse
$conseils = $conseilRepo->findByAnalyseId($analyseId);

// Trouver par priorité
$conseils = $conseilRepo->findByPriorite($priorite);

// Rechercher
$conseils = $conseilRepo->search($term, $priorite);

// Stats par priorité
$stats = $conseilRepo->getPriorityStats();

// Récents
$conseils = $conseilRepo->findRecent($limit);
```

---

## 📱 Contrôleurs

### ExpertAnalyseController
```php
// Affiche les analyses de l'expert
GET /expert/analyses
    → expert_analyses_list()

// Affiche les détails d'une analyse
GET /expert/analyse/{id}
    → expert_analyse_show()

// Affiche les demandes en attente
GET /expert/demandes-en-attente
    → expert_pending_requests()

// Expert prend en charge une demande
POST /expert/demande/{id}/prendre-en-charge
    → expert_take_request()

// Crée une nouvelle analyse
GET /expert/analyse/new
POST /expert/analyse/new
    → expert_analyse_new()

// Modifie une analyse
GET /expert/analyse/{id}/edit
POST /expert/analyse/{id}/edit
    → expert_analyse_edit()

// Supprime une analyse
POST /expert/analyse/{id}/delete
    → expert_analyse_delete()

// Change le statut d'une analyse
POST /expert/analyse/{id}/status/{status}
    → expert_analyse_status()

// Crée un conseil pour une analyse
GET /expert/analyse/{id}/conseil/new
POST /expert/analyse/{id}/conseil/new
    → expert_analyse_conseil_new()

// Exporte une analyse en PDF
GET /expert/analyse/{id}/export/pdf
    → expert_analyse_export_pdf()
```

### ExpertAIController
```php
// Effectue un diagnostic IA avec image
POST /expert/analyse/{id}/diagnose
    → diagnose()

// Affiche le résultat du diagnostic IA
GET /expert/analyse/{id}/ai-result
    → showAiResult()

// API JSON pour le diagnostic IA
POST /expert/analyse/{id}/diagnose/json
    → diagnoseApi()

// Effectue un diagnostic IA sans image
GET /expert/analyse/{id}/diagnose-text
POST /expert/analyse/{id}/diagnose-text
    → diagnoseText()
```

### ExpertConseilController
```php
// Affiche les conseils de l'expert
GET /expert/conseils
    → list()

// Affiche les détails d'un conseil
GET /expert/conseil/{id}
    → show()

// Crée un nouveau conseil
GET /expert/conseil/new
POST /expert/conseil/new
    → new()

// Modifie un conseil
GET /expert/conseil/{id}/edit
POST /expert/conseil/{id}/edit
    → edit()

// Supprime un conseil
POST /expert/conseil/{id}/delete
    → delete()
```

---

## 📋 Templates

### Dashboard
```
templates/portal/expert/index.html.twig
    - Stats: Analyses ce mois, Total, Conseils, Demandes en attente
    - Actions rapides: Nouvelle Analyse, Créer Rapport
    - Notifications
```

### Demandes en Attente
```
templates/portal/expert/pending_requests.html.twig
    - Liste des demandes en attente
    - Infos: Demandeur, Ferme, Animal/Plante, Description, Image
    - Bouton: Prendre en charge
```

### Analyses
```
templates/portal/expert/analyses.html.twig
    - Liste des analyses de l'expert
    - Filtrage par statut, recherche
    
templates/portal/expert/analyse_show.html.twig
    - Détails d'une analyse
    - Boutons: Diagnostiquer (IA), Créer Conseil, Exporter PDF
    
templates/portal/expert/analyse_new.html.twig
    - Formulaire de création d'analyse
    
templates/portal/expert/analyse_edit.html.twig
    - Formulaire de modification d'analyse
```

### Diagnostic IA
```
templates/portal/expert/diagnose_text.html.twig
    - Formulaire pour diagnostic texte
    - Affichage du résultat IA
    
templates/portal/expert/ai_result.html.twig
    - Affichage du résultat du diagnostic IA
    
templates/portal/expert/diagnose_unified.html.twig
    - Interface unifiée pour diagnostic (vision + texte)
```

### Conseils
```
templates/portal/expert/conseils.html.twig
    - Liste des conseils de l'expert
    - Filtrage par priorité, recherche
    
templates/portal/expert/conseil_show.html.twig
    - Détails d'un conseil
    
templates/portal/expert/conseil_new.html.twig
    - Formulaire de création de conseil
    
templates/portal/expert/conseil_edit.html.twig
    - Formulaire de modification de conseil
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

## 📊 Statuts et Priorités

### Statuts Analyse
| Statut | Description | Qui peut changer |
|--------|-------------|------------------|
| `en_attente` | Demande reçue, en attente d'expert | Agriculteur → Expert |
| `en_cours` | Expert en train de traiter | Expert |
| `terminee` | Diagnostic et conseils générés | Expert |
| `annulee` | Demande annulée | Expert |

### Priorités Conseil
| Priorité | Emoji | Couleur | Signification |
|----------|-------|--------|---------------|
| `HAUTE` | 🔴 | Rouge | Action immédiate requise |
| `MOYENNE` | 🟡 | Orange | À surveiller |
| `BASSE` | 🟢 | Vert | Informatif |

---

## 🚀 Workflow Complet (Pas à Pas)

### 1. Agriculteur crée une Analyse
```
Agriculteur → /agricole/dashboard
           → Crée une Analyse
           → Sélectionne Ferme, Animal/Plante, Description, Image
           → Statut: "en_attente"
           → Demandeur: Agriculteur
           → Technicien: NULL (pas encore assigné)
```

### 2. Expert reçoit la notification
```
Expert → /expert/dashboard
      → Voit "X demandes en attente"
      → Clique sur "Demandes en attente"
      → Voit la liste des demandes
```

### 3. Expert prend en charge
```
Expert → POST /expert/demande/{id}/prendre-en-charge
      → Statut: "en_attente" → "en_cours"
      → Technicien: Expert
      → Redirige vers /expert/analyse/{id}
```

### 4. Expert effectue le diagnostic IA
```
Expert → POST /expert/analyse/{id}/diagnose (avec image)
      OU
      → POST /expert/analyse/{id}/diagnose-text (sans image)
      
      → GroqService génère le diagnostic
      → WeatherService récupère les données météo
      → Stocke: aiDiagnosisResult, aiConfidenceScore, aiDiagnosisDate
      → Redirige vers /expert/analyse/{id}
```

### 5. Expert crée des Conseils
```
Expert → GET /expert/analyse/{id}/conseil/new
      → POST /expert/analyse/{id}/conseil/new
      → Crée 1+ Conseils
      → Chaque Conseil: Description + Priorité
      → Statut Analyse: "en_cours" → "terminee"
```

### 6. Agriculteur reçoit les Conseils
```
Agriculteur → Notification: "Conseils disponibles"
           → Voit les Conseils sur son dashboard
           → Lit les recommandations
```

---

## 📈 Statistiques

### Dashboard Expert
```
- Analyses ce mois: countByTechnicienThisMonth()
- Analyses total: countByTechnicien()
- Conseils total: countByTechnicien()
- Demandes en attente: countPendingRequests()
```

### Conseils
```
- Par priorité: getPriorityStats()
- Par expert: countByTechnicien()
- Par expert et priorité: countByTechnicienAndPriorite()
```

---

## 🔧 Configuration

### .env
```env
GROQ_API_KEY=your_groq_key
GROQ_MODEL=meta-llama/llama-4-scout-17b-16e-instruct
OPENWEATHER_API_KEY=your_openweather_key
OPENWEATHER_URL=https://api.openweathermap.org/data/2.5
```

### services.yaml
```yaml
App\Service\GroqService:
    arguments:
        $apiKey: '%env(string:GROQ_API_KEY)%'
        $model: '%env(string:GROQ_MODEL)%'

App\Service\WeatherService:
    arguments:
        $weatherApiKey: '%app.weather_api_key%'
```

---

## 🎯 Points Clés

1. **Demande-Réponse**: Agriculteur demande → Expert répond
2. **IA Intégrée**: Groq pour diagnostic, OpenWeather pour météo
3. **Sécurité**: Expert ne voit que ses propres analyses
4. **Statuts**: Suivi du cycle de vie de l'analyse
5. **Priorités**: Conseils classés par urgence
6. **Notifications**: Agriculteur notifié des conseils
7. **Export**: Analyses exportables en PDF

---

**Dernière mise à jour**: 6 mai 2026  
**Version**: 1.0  
**Statut**: Production
