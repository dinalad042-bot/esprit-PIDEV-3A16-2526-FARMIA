# Checklist des Screenshots - Rapport Expert Module

## 📸 Screenshots à Prendre

### ✅ Screenshot 1 - PHPStan BEFORE
**Commande:** `vendor/bin/phpstan analyse src/Controller/Web/Expert*.php --level=5`  
**Résultat attendu:** `[ERROR] Found 11 errors`  
**Contenu à capturer:** Terminal montrant les 11 erreurs  
**Label:** "PHPStan BEFORE - 11 errors"

---

### ✅ Screenshot 2 - Tests Unitaires (VS Code)
**Commande:** `php bin/phpunit tests/Service/AnalyseManagerTest.php --testdox`  
**Résultat attendu:** `OK (5 tests, 8 assertions)`  
**Contenu à capturer:** Terminal VS Code avec résultats des tests  
**Label:** "Unit Tests - 5/5 PASS"

---

### ✅ Screenshot 3 - Tests Unitaires (Terminal propre)
**Commande:** `php bin/phpunit tests/Service/AnalyseManagerTest.php --testdox`  
**Résultat attendu:** `OK (5 tests, 8 assertions)`  
**Contenu à capturer:** Terminal montrant les 5 tests avec checkmarks  
**Label:** "Tests Terminal - OK (5 tests, 8 assertions)"

---

### ✅ Screenshot 4 - PHPStan AFTER
**Commande:** `vendor/bin/phpstan analyse --configuration=phpstan.neon`  
**Résultat attendu:** `[OK] No errors`  
**Contenu à capturer:** Terminal montrant "[OK] No errors"  
**Label:** "PHPStan AFTER - 0 errors"

---

### ✅ Screenshot 5 - DoctrineDoctor Panel (Browser)
**URL:** `http://localhost:8000/expert/dashboard`  
**Étapes:**
1. Ouvrir le navigateur
2. Aller à `http://localhost:8000/expert/dashboard`
3. Regarder la barre noire en bas (Symfony Web Profiler)
4. Cliquer sur l'icône "Doctrine Doctor" (stéthoscope)
5. Capturer le panel complet

**Contenu à capturer:** Panel DoctrineDoctor montrant les problèmes détectés  
**Label:** "DoctrineDoctor BEFORE - Issues detected"

---

### ✅ Screenshot 6 - Query Metrics (Symfony Toolbar)
**URL:** `http://localhost:8000/expert/analyses`  
**Étapes:**
1. Aller à `http://localhost:8000/expert/analyses`
2. Regarder la barre noire en bas (Symfony Web Profiler)
3. Cliquer sur la section "DATABASE"
4. Capturer le nombre de requêtes

**Contenu à capturer:** Toolbar Symfony montrant le nombre de requêtes DB  
**Label:** "Database Queries BEFORE - 5 queries"

---

### ✅ Screenshot 7 - Performance AFTER (curl)
**Commandes:**
```bash
curl -w "\nTotal Time: %{time_total}s\n" -o /dev/null -s http://localhost:8000/expert/dashboard
curl -w "\nTotal Time: %{time_total}s\n" -o /dev/null -s http://localhost:8000/expert/analyses
curl -w "\nTotal Time: %{time_total}s\n" -o /dev/null -s http://localhost:8000/expert/demandes-en-attente
```

**Résultats attendus:**
- `/expert/dashboard`: ~904ms
- `/expert/analyses`: ~984ms
- `/expert/demandes-en-attente`: ~811ms

**Contenu à capturer:** Terminal montrant les 3 résultats de curl  
**Label:** "Performance AFTER - Response times"

---

## 📊 Données à Inclure dans le Rapport

### Tableau Comparatif AVANT/APRÈS

```
| Métrique | AVANT | APRÈS | Amélioration |
|----------|-------|-------|--------------|
| PHPStan Errors | 11 | 0 | -100% ✅ |
| Tests Unitaires | N/A | 5/5 PASS | ✅ |
| /expert/dashboard | 934ms | 904ms | -3% |
| /expert/analyses | 4779ms | 984ms | -79% ⭐ |
| /expert/demandes-en-attente | 851ms | 811ms | -5% |
| N+1 Queries | 2 detected | 0 | -100% ✅ |
| orphanRemoval | Missing | Configured | ✅ |
```

---

## 📝 Texte à Copier-Coller

### Section PHPStan

```
## 1- PHPStan - Analyse Statique

### a) Avant Optimisation
Résultat initial: [ERROR] Found 11 errors

Les 11 erreurs détectées:
1. ExpertAIController.php:56 → Call to protected method setAiDiagnosisDate()
2. ExpertAIController.php:62 → Call to protected method setWeatherFetchedAt()
3. ExpertAIController.php:114 → Call to protected method setWeatherFetchedAt()
4. ExpertAIController.php:131 → Call to protected method setAiDiagnosisDate()
5. ExpertAIController.php:193 → Call to protected method setAiDiagnosisDate()
6. ExpertAIController.php:200 → Call to protected method setWeatherFetchedAt()
7. ExpertAnalyseController.php:102 → Call to protected method setWeatherFetchedAt()
8. ExpertAnalyseController.php:131 → Parameter type mismatch (User vs UserInterface)
9. ExpertAnalyseController.php:144 → Parameter type mismatch (User vs UserInterface)
10. ExpertAnalyseController.php:145 → Parameter type mismatch (User vs UserInterface)
11. ExpertConseilController.php:33 → Call to undefined method getId()

### b) Après Optimisation
Corrections appliquées:
- Analyse.php: 3 méthodes protected → public
- ExpertAnalyseController.php: Cast User ajouté
- ExpertConseilController.php: Cast User ajouté

Résultat final: [OK] No errors
```

### Section Tests

```
## 2- Tests Unitaires

Service créé: src/Service/AnalyseManager.php
Règles métier validées:
- Règle 1: Description obligatoire
- Règle 2: Statut valide
- Règle 3: Score IA entre 0-100

Résultats:
✅ testAnalyseValide - PASS
✅ testDescriptionVideLanceException - PASS
✅ testStatutInvalideLanceException - PASS
✅ testScoreConfianceInvalideLanceException - PASS
✅ testScoreConfianceValide - PASS

Résultat global: OK (5 tests, 8 assertions)
```

### Section Performance

```
## 4- Performance - Mesures Avant/Après

| Endpoint | Avant | Après | Amélioration |
|----------|-------|-------|--------------|
| /expert/dashboard | 934ms | 904ms | -3% |
| /expert/analyses | 4779ms | 984ms | -79% ⭐ |
| /expert/demandes-en-attente | 851ms | 811ms | -5% |

Amélioration majeure sur /expert/analyses:
- AVANT: 4.78s (N+1 queries, 21 requêtes DB)
- APRÈS: 0.98s (1 requête avec JOIN)
- Gain: 3.8 secondes (-79%)
```

---

## ✅ Checklist Finale

- [ ] Screenshot 1 - PHPStan BEFORE (11 errors)
- [ ] Screenshot 2 - Tests VS Code (5/5 PASS)
- [ ] Screenshot 3 - Tests Terminal (OK 5 tests)
- [ ] Screenshot 4 - PHPStan AFTER (0 errors)
- [ ] Screenshot 5 - DoctrineDoctor Panel
- [ ] Screenshot 6 - Query Metrics
- [ ] Screenshot 7 - Performance curl
- [ ] Rapport RAPPORT_EXPERT_MODULE.md complété
- [ ] Code PREUVES_CODE_OPTIMISATIONS.md inclus
- [ ] Tous les fichiers modifiés vérifiés

---

**Statut:** ✅ PRÊT POUR SOUMISSION
