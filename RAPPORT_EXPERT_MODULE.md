# Rapport Complet - Module Expert FarmAI
## Sprint Web PIDEV - Optimisation et Tests

**Groupe:** FarmAI - Module Expert  
**Date:** Mai 2026  
**Projet:** Symfony 6.4 - Application FarmAI

---

## ✅ Inventaire des Screenshots

| # | Screenshot | Contenu | Status |
|---|---|---|---|
| **1** | PHPStan BEFORE | `[ERROR] Found 11 errors` terminal | ✅ |
| **2** | Tests VS Code | `OK (5 tests, 8 assertions)` | ✅ |
| **3** | Tests terminal | `OK (5 tests, 8 assertions)` | ✅ |
| **4** | PHPStan AFTER | `[OK] No errors` | ✅ |
| **5** | DoctrineDoctor | Profiler browser panel | ✅ |
| **6** | Query Metrics | `/expert/analyses` DB queries | ✅ |
| **7** | Performance AFTER | curl response times | ✅ |

---

## 1️⃣ PHPStan - Analyse Statique

### a) Avant Optimisation

**Résultat initial:** `[ERROR] Found 11 errors`

#### Test 1 - Méthodes protégées inaccessibles

```
Fichier : ExpertAIController.php
Lignes : 56, 62, 114, 131, 193, 200

Erreur : "Call to protected method setAiDiagnosisDate() 
         of class App\Entity\Analyse"
         
Erreur : "Call to protected method setWeatherFetchedAt() 
         of class App\Entity\Analyse"

Cause : Les méthodes setAiDiagnosisDate() et setWeatherFetchedAt() 
        dans l'entité Analyse.php étaient déclarées protected 
        au lieu de public. Les contrôleurs ne pouvaient pas les appeler.
```

#### Test 2 - Type mismatch User/UserInterface

```
Fichier : ExpertAnalyseController.php
Lignes : 131, 144, 145

Erreur : "Parameter #1 $t of method App\Entity\Analyse::setTechnicien() 
         expects App\Entity\User|null, 
         Symfony\Component\Security\Core\User\UserInterface|null given"

Cause : $this->getUser() retourne UserInterface|null 
        mais setTechnicien() et setDemandeur() attendent App\Entity\User|null. 
        Cast manquant.
```

#### Test 3 - Méthode inexistante sur UserInterface

```
Fichier : ExpertConseilController.php
Ligne : 33

Erreur : "Call to an undefined method 
         Symfony\Component\Security\Core\User\UserInterface::getId()"

Cause : getId() est définie sur App\Entity\User 
        mais pas sur l'interface UserInterface. 
        Il fallait caster l'objet avant l'appel.
```

**Liste complète des 11 erreurs:**

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

---

### b) Après Optimisation

**Corrections appliquées:**

```
1. Analyse.php → setAiDiagnosisDate() : protected → public
2. Analyse.php → setWeatherFetchedAt() : protected → public
3. Analyse.php → setDateAnalyse() : protected → public

4. ExpertAnalyseController.php → Cast ajouté:
   $user = $this->getUser();
   if ($user instanceof \App\Entity\User) {
       $analyse->setTechnicien($user);
   }

5. ExpertConseilController.php → Cast ajouté avant getId():
   $userId = $user instanceof \App\Entity\User ? $user->getId() : null;
```

**Résultat final:** `[OK] No errors`

✅ **Amélioration:** 11 erreurs → 0 erreurs (100% de réduction)

---

## 2️⃣ Tests Unitaires

**Entité testée:** `Analyse` (Module Expert)  
**Service créé:** `src/Service/AnalyseManager.php`  
**Framework:** PHPUnit 9.6.34

### Règles métier validées

- **Règle 1:** La description de la demande est obligatoire
- **Règle 2:** Le statut doit être valide (`en_attente`, `en_cours`, `terminee`, `annulee`)
- **Règle 3:** Le score de confiance IA doit être entre 0 et 100

---

### Test 1: `testAnalyseValide`

```
Objectif : Vérifier qu'une Analyse avec des données valides 
           passe la validation sans exception

Données d'entrée:
  - descriptionDemande = "Taches brunes sur les feuilles"
  - statut = "en_attente"

Résultat attendu : true
Résultat obtenu  : ✅ PASS
```

### Test 2: `testDescriptionVideLanceException`

```
Objectif : Vérifier qu'une description vide est rejetée

Données d'entrée:
  - descriptionDemande = "" (vide)
  - statut = "en_attente"

Exception attendue : InvalidArgumentException
Message attendu   : "La description de la demande est obligatoire"
Résultat obtenu   : ✅ PASS
```

### Test 3: `testStatutInvalideLanceException`

```
Objectif : Vérifier qu'un statut inconnu est rejeté

Données d'entrée:
  - descriptionDemande = "Problème sur les tomates"
  - statut = "statut_inexistant"

Exception attendue : InvalidArgumentException
Message attendu   : "Le statut est invalide"
Résultat obtenu   : ✅ PASS
```

### Test 4: `testScoreConfianceInvalideLanceException`

```
Objectif : Vérifier qu'un score IA hors limites (> 100) est rejeté

Données d'entrée:
  - descriptionDemande = "Problème détecté"
  - statut = "en_cours"
  - aiConfidenceScore = "150"

Exception attendue : InvalidArgumentException
Message attendu   : "Le score de confiance doit être entre 0 et 100"
Résultat obtenu   : ✅ PASS
```

### Test 5: `testScoreConfianceValide`

```
Objectif : Vérifier qu'un score IA de 92% est accepté

Données d'entrée:
  - descriptionDemande = "Diagnostic IA effectué"
  - statut = "en_cours"
  - aiConfidenceScore = "92"

Résultat attendu : true
Résultat obtenu  : ✅ PASS
```

---

### Résumé des tests

```
Temps d'exécution : 00:00.011
Utilisation mémoire : 8.00 MB
Résultat global : OK (5 tests, 8 assertions)
```

✅ **Tous les tests passent avec succès**

---

## 3️⃣ DoctrineDoctor - Optimisation des Requêtes

### Tableau principal

| Indicateur | Avant optimisation | Après optimisation | Amélioration |
|---|---|---|---|
| **Problèmes N+1 détectés** | 2 find() queries inefficaces | 0 | -100% |
| **Total Issues** | 33 (1 Critical, 8 Warnings, 24 Info) | Réduit | ✅ |
| **orphanRemoval** | Manquant | Configuré | ✅ |

---

### Détail des problèmes trouvés

#### Problème 1 - Performance (Critical): `GET_REFERENCE`

```
DoctrineDoctor a détecté : 
"Inefficient Entity Loading: 2 find() queries detected"

Description : 
Detected 2 simple SELECT by ID queries across 2 tables 
(analyse, user_face). Consider using getReference() instead 
of find() when you only need the entity reference for 
associations (threshold: 2)

Impact : 
Chaque appel à find() charge l'entité complète depuis 
la base de données même quand seule la référence est 
nécessaire pour une association.
```

#### Problème 2 - N+1 Query dans `findByTechnicienId()`

**AVANT (Inefficace):**
```php
public function findByTechnicienId(int $id): array
{
    return $this->findBy(['technicien' => $id]);
    // → 1 query pour les analyses
    // → puis 1 query PAR analyse pour charger ferme
    // → puis 1 query PAR analyse pour charger conseils
    // = N+1 queries total
}
```

**APRÈS (Optimisé):**
```php
public function findByTechnicienId(int $id): array
{
    return $this->createQueryBuilder('a')
        ->leftJoin('a.conseils', 'c')
        ->addSelect('c')
        ->leftJoin('a.ferme', 'f')
        ->addSelect('f')
        ->andWhere('a.technicien = :id')
        ->setParameter('id', $id)
        ->orderBy('a.dateAnalyse', 'DESC')
        ->getQuery()->getResult();
    // = 1 seule query avec JOIN
}
```

#### Problème 3 - N+1 Query dans `findPendingRequests()`

**AVANT:**
```php
findBy(['statut' => 'en_attente'])
// → N requêtes supplémentaires pour demandeur, ferme, animal, plante
```

**APRÈS:**
```php
public function findPendingRequests(): array
{
    return $this->createQueryBuilder('a')
        ->leftJoin('a.demandeur', 'd')
        ->addSelect('d')
        ->leftJoin('a.ferme', 'f')
        ->addSelect('f')
        ->leftJoin('a.animalCible', 'animal')
        ->addSelect('animal')
        ->leftJoin('a.planteCible', 'plante')
        ->addSelect('plante')
        ->andWhere('a.statut = :statut')
        ->setParameter('statut', 'en_attente')
        ->orderBy('a.dateAnalyse', 'DESC')
        ->getQuery()->getResult();
    // = 1 seule requête
}
```

#### Problème 4 - Integrity: `orphanRemoval` manquant

**AVANT:**
```php
#[ORM\OneToMany(
    mappedBy: 'analyse',
    targetEntity: Conseil::class
)]
```

**APRÈS:**
```php
#[ORM\OneToMany(
    mappedBy: 'analyse',
    targetEntity: Conseil::class,
    cascade: ['persist', 'remove'],
    orphanRemoval: true
)]
```

**Raison:** Sans `orphanRemoval=true`, supprimer un Conseil de la collection laissait des enregistrements orphelins en base de données.

---

## 4️⃣ Performance - Mesures Avant/Après

### Tableau de comparaison

| Endpoint | Avant optimisation | Après optimisation | Amélioration |
|---|---|---|---|
| **`/expert/dashboard`** | 934 ms | 904 ms | -3% |
| **`/expert/analyses`** | 4 779 ms | 984 ms | **-79%** ⭐ |
| **`/expert/demandes-en-attente`** | 851 ms | 811 ms | -5% |
| **Utilisation mémoire** | 8.00 MB | 8.00 MB | - |

---

### Analyse détaillée

#### Amélioration majeure sur `/expert/analyses` (-79%)

```
AVANT : 4 779 ms
  → findByTechnicienId() sans JOIN
  → Pour chaque analyse : requête séparée pour ferme + conseils
  → Exemple avec 10 analyses = 21 requêtes DB
  → Temps total : 4.78 secondes

APRÈS : 984 ms
  → findByTechnicienId() avec leftJoin
  → 1 seule requête SQL avec JOIN
  → Toutes les données chargées en une seule requête
  → Temps total : 0.98 secondes

RÉSULTAT : Amélioration de 79% du temps de réponse
```

#### Autres endpoints

- **`/expert/dashboard`**: Légère amélioration (-3%) - cache et optimisations mineures
- **`/expert/demandes-en-attente`**: Amélioration modérée (-5%) - findPendingRequests optimisée

---

## 📊 Résumé des Optimisations

### Code Changes

| Fichier | Changement | Impact |
|---|---|---|
| `src/Entity/Analyse.php` | 3 méthodes protected → public | PHPStan: -6 erreurs |
| `src/Controller/Web/ExpertAnalyseController.php` | Cast User ajouté | PHPStan: -3 erreurs |
| `src/Controller/Web/ExpertConseilController.php` | Cast User ajouté | PHPStan: -1 erreur |
| `src/Repository/AnalyseRepository.php` | 2 méthodes avec leftJoin | Performance: -79% |
| `src/Service/AnalyseManager.php` | Service créé | Tests: 5/5 ✅ |

### Résultats Finaux

✅ **PHPStan:** 11 erreurs → 0 erreurs  
✅ **Tests:** 5/5 tests passants  
✅ **Performance:** -79% sur `/expert/analyses`  
✅ **DoctrineDoctor:** N+1 queries éliminées  

---

## ✅ Statut Final du Rapport

| Section | Status | Preuves |
|---|---|---|
| **1. PHPStan** | ✅ COMPLET | Screenshots avant/après |
| **2. Tests Unitaires** | ✅ COMPLET | 5/5 tests passants |
| **3. DoctrineDoctor** | ✅ COMPLET | Optimisations appliquées |
| **4. Performance** | ✅ COMPLET | Mesures curl avant/après |

---

**Rapport généré:** Mai 2026  
**Module:** Expert - FarmAI  
**Statut:** ✅ COMPLET ET VALIDÉ
