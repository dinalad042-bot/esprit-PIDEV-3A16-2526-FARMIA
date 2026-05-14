# 🔬 Module Expert - Résumé Complet

## 📌 TL;DR (Too Long; Didn't Read)

Tu travailles **UNIQUEMENT** avec le module Expert quand tu te connectes avec **ROLE_EXPERT**.

**Flux simple**:
```
Agriculteur crée Analyse → Expert prend en charge → Expert fait diagnostic IA → Expert crée Conseils
```

---

## 🎯 Entité Principale: **Analyse**

### Qu'est-ce qu'une Analyse?
Une **Analyse** est une **demande d'aide** créée par un Agriculteur pour un problème agricole.

### Qui crée une Analyse?
- **Agriculteur** (ROLE_AGRICOLE)

### Qui gère une Analyse?
- **Expert** (ROLE_EXPERT)

### Cycle de vie d'une Analyse
```
1. Agriculteur crée → Statut: "en_attente"
2. Expert prend en charge → Statut: "en_cours"
3. Expert fait diagnostic IA → Statut: "en_cours"
4. Expert crée Conseils → Statut: "terminee"
```

---

## 📊 Entités Liées

### **Analyse** (Demande)
```
Champs:
- descriptionDemande: Description du problème
- imageUrl: Image du problème
- demandeur: Agriculteur qui demande
- technicien: Expert assigné
- ferme: Ferme concernée
- animalCible: Animal ciblé (optionnel)
- planteCible: Plante ciblée (optionnel)
- statut: en_attente, en_cours, terminee, annulee
- aiDiagnosisResult: Résultat du diagnostic IA
- aiConfidenceScore: Score de confiance
- weatherData: Données météo
```

### **Conseil** (Recommandation)
```
Champs:
- descriptionConseil: Description du conseil
- prioriteRaw: HAUTE, MOYENNE, BASSE
- analyse: Lié à une Analyse
```

### **Priorité** (Enum)
```
HAUTE 🔴 → Action immédiate
MOYENNE 🟡 → À surveiller
BASSE 🟢 → Informatif
```

### **Statut** (Enum)
```
en_attente → Demande reçue
en_cours → Expert en train de traiter
terminee → Diagnostic et conseils générés
annulee → Demande annulée
```

---

## 🛣️ Routes Principales

### Dashboard
```
GET /expert/dashboard
    → Affiche les stats et actions rapides
```

### Demandes
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
```

### Diagnostic IA
```
POST /expert/analyse/{id}/diagnose
    → Diagnostic IA avec image
    
POST /expert/analyse/{id}/diagnose-text
    → Diagnostic IA sans image
```

### Conseils
```
GET /expert/conseils
    → Liste tous les conseils
    
POST /expert/analyse/{id}/conseil/new
    → Créer un conseil
```

---

## 🤖 Services IA

### GroqService
```
generateVisionDiagnostic($imageUrl)
    → Diagnostic avec image
    
generateTextDiagnostic($observation)
    → Diagnostic sans image
```

### WeatherService
```
getWeather($location)
    → Récupère les données météo
```

---

## 📱 Contrôleurs

### ExpertAnalyseController
- Gère les Analyses
- Affiche les demandes en attente
- Permet de prendre en charge une demande

### ExpertAIController
- Effectue le diagnostic IA
- Affiche les résultats du diagnostic

### ExpertConseilController
- Gère les Conseils
- Crée, modifie, supprime les Conseils

---

## 🔐 Sécurité

### Authentification
- **Rôle requis**: `ROLE_EXPERT`

### Autorisation
- **Expert ne peut voir que ses propres Analyses**
- **Expert ne peut voir que ses propres Conseils**

---

## 📋 Workflow Complet

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. AGRICULTEUR CRÉE UNE ANALYSE                                 │
│    - Sélectionne Ferme                                          │
│    - Sélectionne Animal/Plante (optionnel)                      │
│    - Décrit le problème                                         │
│    - Télécharge une image (optionnel)                           │
│    - Statut: "en_attente"                                       │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. EXPERT REÇOIT LA NOTIFICATION                                │
│    - Voit "X demandes en attente" sur le dashboard              │
│    - Clique sur "Demandes en attente"                           │
│    - Voit la liste des demandes                                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. EXPERT PREND EN CHARGE                                       │
│    - Clique "Prendre en charge"                                 │
│    - Statut: "en_attente" → "en_cours"                          │
│    - Technicien assigné: Expert                                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. EXPERT EFFECTUE LE DIAGNOSTIC IA                             │
│    - Clique "Diagnostiquer"                                     │
│    - Vision Diagnosis (avec image) OU Text Diagnosis (sans)     │
│    - IA génère le diagnostic                                    │
│    - Météo récupérée                                            │
│    - Résultat stocké                                            │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. EXPERT CRÉE DES CONSEILS                                     │
│    - Clique "Créer Conseil"                                     │
│    - Crée 1+ Conseils                                           │
│    - Chaque Conseil: Description + Priorité                     │
│    - Statut: "en_cours" → "terminee"                            │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 6. AGRICULTEUR REÇOIT LES CONSEILS                              │
│    - Notification: "Conseils disponibles"                       │
│    - Voit les Conseils sur son dashboard                        │
│    - Lit les recommandations                                    │
└─────────────────────────────────────────────────────────────────┘
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

## 📊 Statistiques du Dashboard Expert

```
┌─────────────────────────────────────────────────────────────────┐
│ ANALYSES CE MOIS: 5                                             │
│ ANALYSES TOTAL: 42                                              │
│ CONSEILS TOTAL: 128                                             │
│ DEMANDES EN ATTENTE: 3                                          │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🚀 Commandes Utiles

### Démarrage
```bash
# Serveur PHP
php -S localhost:8000 -t public public/router.php

# Python API
cd python_api && source venv/bin/activate && python app.py
```

### Tests
```bash
# Tous les tests
php bin/phpunit

# Tests Expert
php bin/phpunit tests/Functional/Controller/AnalyseControllerTest.php
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
```

### Services
```
src/Service/GroqService.php
src/Service/WeatherService.php
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
templates/portal/expert/conseils.html.twig
```

---

## 💡 Points Clés

1. **Demande-Réponse**: Agriculteur demande → Expert répond
2. **IA Intégrée**: Groq pour diagnostic, OpenWeather pour météo
3. **Sécurité**: Expert ne voit que ses propres analyses
4. **Statuts**: Suivi du cycle de vie de l'analyse
5. **Priorités**: Conseils classés par urgence
6. **Notifications**: Agriculteur notifié des conseils
7. **Export**: Analyses exportables en PDF

---

## 🎯 Prochaines Étapes

1. Lire `PROJECT_ARCHITECTURE.md` pour la vue d'ensemble
2. Lire `EXPERT_MODULE_WORKFLOW.md` pour le workflow détaillé
3. Lire `EXPERT_QUICK_START.md` pour les commandes et routes
4. Commencer à développer!

---

**Dernière mise à jour**: 6 mai 2026  
**Version**: 1.0  
**Statut**: Production
