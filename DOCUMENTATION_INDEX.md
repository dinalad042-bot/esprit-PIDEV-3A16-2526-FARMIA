# 📚 Documentation FarmAI - Index Complet

## 🎯 Bienvenue!

Tu travailles **UNIQUEMENT** avec le **Module Expert** quand tu te connectes avec le rôle **ROLE_EXPERT**.

Cette documentation te guide à travers toute la structure du projet et le workflow du module Expert.

---

## 📖 Documents Disponibles

### 1. **PROJECT_ARCHITECTURE.md** 📋
**Contenu**: Vue d'ensemble complète du projet
- Architecture générale
- Toutes les entités (11 principales)
- Structure des dossiers
- Services IA
- Configuration
- Statistiques du projet

**Quand lire**: Pour comprendre la structure globale du projet

---

### 2. **EXPERT_MODULE_WORKFLOW.md** 🔄
**Contenu**: Workflow détaillé du module Expert
- Flux complet (6 étapes)
- Entités principales (Analyse, Conseil)
- Routes Expert (20+)
- Services IA (GroqService, WeatherService)
- Contrôleurs (3 principaux)
- Repositories (AnalyseRepository, ConseilRepository)
- Templates (13 fichiers)
- Relations d'entités
- Statuts et priorités

**Quand lire**: Pour comprendre le workflow complet du module Expert

---

### 3. **EXPERT_QUICK_START.md** 🚀
**Contenu**: Guide de démarrage rapide
- Résumé en 30 secondes
- Authentification Expert
- Entités principales
- Routes principales
- Services IA
- Contrôleurs
- Repositories
- Workflow complet
- Sécurité
- Commandes utiles
- Fichiers clés

**Quand lire**: Pour démarrer rapidement avec le module Expert

---

### 4. **EXPERT_SUMMARY.md** 📌
**Contenu**: Résumé complet du module Expert
- TL;DR (Too Long; Didn't Read)
- Entité principale: Analyse
- Entités liées
- Routes principales
- Services IA
- Contrôleurs
- Sécurité
- Workflow complet
- Relations d'entités
- Statistiques du dashboard
- Points clés

**Quand lire**: Pour avoir une vue d'ensemble rapide

---

### 5. **EXPERT_USE_CASES.md** 🎯
**Contenu**: Cas d'usage détaillés
- 18 cas d'usage principaux
- Flux complet (exemple réel)
- Cas d'usage de sécurité
- Cas d'usage de statistiques
- Cas d'usage avancés
- Cas d'usage API
- Résumé des cas d'usage

**Quand lire**: Pour comprendre comment utiliser le module Expert

---

### 6. **EXPERT_API_ENDPOINTS.md** 🔌
**Contenu**: Tous les endpoints API
- Base URL
- Authentification
- Dashboard
- Demandes en attente
- Analyses (CRUD)
- Diagnostic IA
- Conseils (CRUD)
- Export PDF
- Recherche et filtrage
- Statistiques
- Codes d'erreur
- Relations d'entités
- Exemples de requêtes
- Résumé des endpoints

**Quand lire**: Pour connaître tous les endpoints disponibles

---

## 🗺️ Parcours de Lecture Recommandé

### Pour les Débutants
1. **EXPERT_SUMMARY.md** - Comprendre les bases
2. **EXPERT_QUICK_START.md** - Démarrer rapidement
3. **EXPERT_USE_CASES.md** - Voir des exemples concrets

### Pour les Développeurs
1. **PROJECT_ARCHITECTURE.md** - Comprendre la structure
2. **EXPERT_MODULE_WORKFLOW.md** - Comprendre le workflow
3. **EXPERT_API_ENDPOINTS.md** - Connaître les endpoints
4. **EXPERT_USE_CASES.md** - Voir des cas d'usage

### Pour les Intégrateurs
1. **EXPERT_API_ENDPOINTS.md** - Tous les endpoints
2. **EXPERT_USE_CASES.md** - Cas d'usage
3. **PROJECT_ARCHITECTURE.md** - Architecture générale

---

## 🎯 Résumé Rapide

### Entité Principale
**Analyse** - Demande d'analyse créée par un Agriculteur, gérée par un Expert

### Flux Principal
```
Agriculteur crée Analyse 
  → Expert prend en charge 
  → Expert fait diagnostic IA 
  → Expert crée Conseils
```

### Rôles
- **Agriculteur** (ROLE_AGRICOLE): Crée les Analyses
- **Expert** (ROLE_EXPERT): Gère les Analyses et crée les Conseils

### Statuts Analyse
- `en_attente`: Demande reçue
- `en_cours`: Expert en train de traiter
- `terminee`: Diagnostic et conseils générés
- `annulee`: Demande annulée

### Priorités Conseil
- `HAUTE` 🔴: Action immédiate
- `MOYENNE` 🟡: À surveiller
- `BASSE` 🟢: Informatif

---

## 📁 Structure des Fichiers

```
Documentation/
├── PROJECT_ARCHITECTURE.md          # Vue d'ensemble du projet
├── EXPERT_MODULE_WORKFLOW.md        # Workflow détaillé
├── EXPERT_QUICK_START.md            # Guide de démarrage
├── EXPERT_SUMMARY.md                # Résumé complet
├── EXPERT_USE_CASES.md              # Cas d'usage
├── EXPERT_API_ENDPOINTS.md          # Endpoints API
└── DOCUMENTATION_INDEX.md           # Ce fichier
```

---

## 🔍 Recherche Rapide

### Je veux savoir...

**...comment fonctionne le module Expert?**
→ Lire: `EXPERT_MODULE_WORKFLOW.md`

**...quels sont les endpoints disponibles?**
→ Lire: `EXPERT_API_ENDPOINTS.md`

**...comment créer une Analyse?**
→ Lire: `EXPERT_USE_CASES.md` (Cas d'usage 1)

**...comment effectuer un diagnostic IA?**
→ Lire: `EXPERT_USE_CASES.md` (Cas d'usage 6)

**...comment créer un Conseil?**
→ Lire: `EXPERT_USE_CASES.md` (Cas d'usage 9)

**...quels sont les fichiers clés?**
→ Lire: `EXPERT_QUICK_START.md` (Fichiers clés)

**...comment démarrer rapidement?**
→ Lire: `EXPERT_QUICK_START.md`

**...quelle est la structure du projet?**
→ Lire: `PROJECT_ARCHITECTURE.md`

**...quels sont les services IA?**
→ Lire: `EXPERT_MODULE_WORKFLOW.md` (Services IA)

**...comment fonctionne la sécurité?**
→ Lire: `EXPERT_QUICK_START.md` (Sécurité)

**...quels sont les cas d'usage?**
→ Lire: `EXPERT_USE_CASES.md`

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

## 📊 Statistiques du Projet

- **Entités**: 11 principales
- **Contrôleurs**: 10+ (Web + API + Admin + ERP)
- **Services**: 18+
- **Repositories**: 13+
- **Formulaires**: 9+
- **Templates**: 30+
- **Migrations**: 19
- **Tests**: Unit + Functional + Staging
- **Documentation**: 7 fichiers

---

## 🔗 Liens Rapides

### Contrôleurs Expert
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
templates/portal/expert/
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

## 🆘 Troubleshooting

### Pas de demandes en attente?
→ Vérifier que des Analyses ont été créées par des Agriculteurs

### Diagnostic IA échoue?
→ Vérifier que GROQ_API_KEY est configurée

### Météo non disponible?
→ Vérifier que OPENWEATHER_API_KEY est configurée

### Erreur d'autorisation?
→ Vérifier que tu es connecté avec ROLE_EXPERT

---

## 📞 Support

Pour plus d'informations:
- Consulte les documents de documentation
- Lis le code source dans `src/Controller/Web/Expert*.php`
- Lis les tests dans `tests/Functional/Controller/AnalyseControllerTest.php`

---

## 📝 Versions

| Version | Date | Statut |
|---------|------|--------|
| 1.0 | 6 mai 2026 | Production |

---

## 🎓 Apprentissage

### Niveau 1: Débutant
1. Lire `EXPERT_SUMMARY.md`
2. Lire `EXPERT_QUICK_START.md`
3. Essayer les cas d'usage dans `EXPERT_USE_CASES.md`

### Niveau 2: Intermédiaire
1. Lire `EXPERT_MODULE_WORKFLOW.md`
2. Lire `EXPERT_API_ENDPOINTS.md`
3. Lire le code source

### Niveau 3: Avancé
1. Lire `PROJECT_ARCHITECTURE.md`
2. Lire tous les fichiers de documentation
3. Contribuer au projet

---

## 🎯 Prochaines Étapes

1. **Lire** la documentation appropriée
2. **Comprendre** le workflow du module Expert
3. **Essayer** les cas d'usage
4. **Développer** tes propres fonctionnalités
5. **Tester** ton code
6. **Déployer** en production

---

**Dernière mise à jour**: 6 mai 2026  
**Version**: 1.0  
**Statut**: Production

---

## 📚 Fichiers de Documentation

```
DOCUMENTATION_INDEX.md          ← Tu es ici
PROJECT_ARCHITECTURE.md         ← Vue d'ensemble
EXPERT_MODULE_WORKFLOW.md       ← Workflow détaillé
EXPERT_QUICK_START.md           ← Guide de démarrage
EXPERT_SUMMARY.md               ← Résumé complet
EXPERT_USE_CASES.md             ← Cas d'usage
EXPERT_API_ENDPOINTS.md         ← Endpoints API
```

**Bon apprentissage! 🚀**
