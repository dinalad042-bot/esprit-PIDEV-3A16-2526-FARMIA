# 🔍 INVESTIGATION REPORT — MODULE EXPERT MISSION

**Date:** 2026-05-06  
**Investigator:** Autonomous Codebase Analysis  
**Status:** Complete

---

## 🐛 BUG ANALYSIS

### Primary Bug: Ferme::getId() Undefined

**Error Message:**
```
Attempted to call an undefined method named "getId" of class "App\Entity\Ferme".
Did you mean to call "getIdFerme"?
```

**Location:** `src/Controller/Web/FarmerRequestController.php` line 40

**Exact Code:**
```php
// Line 39-41 in FarmerRequestController.php
$ferme = $user->getFermes()->first();
// Get animals and plants from the farmer's farm
$animals = $this->animalRepo->findByFerme($ferme->getId());  // ❌ Line 40
$plantes = $this->planteRepo->findByFerme($ferme->getId());  // ❌ Line 41
```

**Root Cause:**
The Ferme entity uses a non-standard naming convention:
- Property: `$id_ferme` (snake_case)
- Getter: `getIdFerme()` (camelCase with suffix)
- Missing: `getId()` (standard Symfony pattern)

**Ferme Entity Definition:**
```php
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(name: "id_ferme")]
private ?int $id_ferme = null;

// Only this getter exists:
public function getIdFerme(): ?int { return $this->id_ferme; }

// This getter is MISSING:
// public function getId(): ?int { return $this->id_ferme; }
```

---

## 🏗️ ARCHITECTURE MAP

### Stack Confirmed
- **Framework:** Symfony 6.4
- **PHP Version:** 8.1+
- **Database:** MySQL/MariaDB (10.4.32-MariaDB)
- **Database Name:** `farmia`
- **ORM:** Doctrine 2.18
- **Frontend:** Twig templates (no React/Vue detected)

### Expert Module Structure

#### Controllers (4 files)
1. **ExpertAIController.php** — AI diagnosis operations
   - Vision diagnosis: `POST /expert/analyse/{id}/diagnose`
   - Text diagnosis: `GET|POST /expert/analyse/{id}/diagnose-text` (if exists)
   - Show AI result: `GET /expert/analyse/{id}/ai-result`
   - API endpoint: `POST /expert/analyse/{id}/diagnose/json`

2. **ExpertAnalyseController.php** — Analysis CRUD
   - List: `GET /expert/analyses`
   - Show: `GET /expert/analyse/{id}`
   - Pending requests: `GET /expert/demandes-en-attente`
   - Take request: `POST /expert/demande/{id}/prendre-en-charge`
   - Create: `GET|POST /expert/analyse/new`
   - Edit: `GET|POST /expert/analyse/{id}/edit`
   - Delete: `POST /expert/analyse/{id}/delete`
   - Update status: `POST /expert/analyse/{id}/status/{status}`
   - Add conseil: `GET|POST /expert/analyse/{id}/conseil/new`
   - Export PDF: `GET /expert/analyse/{id}/export/pdf`

3. **ExpertConseilController.php** — Manual advice management
   - List: `GET /expert/conseils`
   - Show: `GET /expert/conseil/{id}`
   - Create: `GET|POST /expert/conseil/new`
   - Edit: `GET|POST /expert/conseil/{id}/edit`
   - Delete: `POST /expert/conseil/{id}/delete`

4. **FarmerRequestController.php** — Farmer creates requests
   - New request: `GET|POST /agricole/nouvelle-demande`
   - My requests: `GET /agricole/mes-demandes`
   - **⚠️ BUG LOCATION:** Line 40-41 calls `$ferme->getId()`

#### Entities (8 files)
- **User.php** — Users with roles (ADMIN, EXPERT, AGRICOLE, FOURNISSEUR)
- **Analyse.php** — Analysis records (has `getId()` ✅)
- **Ferme.php** — Farm entity (missing `getId()` ❌)
- **Animal.php** — Farm animals (missing `getId()` ❌)
- **Plante.php** — Farm plants (missing `getId()` ❌)
- **Conseil.php** — Manual advice records
- **Arrosage.php** — Irrigation records
- **SuiviSante.php** — Health tracking
- **Notification.php** — Notifications (exists but may be incomplete)

#### Services
- **GroqService.php** — AI diagnosis (vision + text)
- **WeatherService.php** — Weather data fetching
- **NotificationService.php** — Notification creation
- **AuthService.php** — Authentication
- **UserService.php** — User management

### Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ FARMER (ROLE_AGRICOLE)                                      │
│ Creates Analysis Request                                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ↓
        ┌────────────────────────┐
        │ Analyse Entity         │
        │ - id_analyse           │
        │ - demandeur (User)     │
        │ - ferme (Ferme)        │
        │ - statut: en_attente   │
        │ - imageUrl (optional)  │
        └────────────┬───────────┘
                     │
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ EXPERT (ROLE_EXPERT)                                        │
│ Takes Request & Runs Diagnosis                              │
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ↓                         ↓
   VISION MODE              TEXT MODE
   (Image-based)            (Description-based)
   GroqService::            GroqService::
   generateVisionDiagnostic generateTextDiagnostic
        │                         │
        └────────────┬────────────┘
                     │
                     ↓
        ┌────────────────────────┐
        │ Analyse Updated        │
        │ - aiDiagnosisResult    │
        │ - aiConfidenceScore    │
        │ - aiDiagnosisDate      │
        │ - weatherData          │
        │ - technicien (Expert)  │
        └────────────┬───────────┘
                     │
                     ↓
        ┌────────────────────────┐
        │ Expert Adds Conseils   │
        │ (Manual Advice)        │
        │ OneToMany: Analyse→    │
        │ Conseils               │
        └────────────┬───────────┘
                     │
                     ↓
        ┌────────────────────────┐
        │ Export PDF Report      │
        │ (Optional)             │
        └────────────────────────┘
```

### Expert-Type Relations Found

| Role | Type | Permissions | Status |
|------|------|-------------|--------|
| ROLE_EXPERT | Technicien | Take requests, run diagnosis, add conseils, export PDF | ✅ Active |
| ROLE_AGRICOLE | Farmer | Create requests, view own requests | ✅ Active |
| ROLE_ADMIN | Administrator | Manage all analyses, assign experts | ✅ Active |
| ROLE_FOURNISSEUR | Supplier | Not involved in expert module | ✅ Active |

---

## ⚠️ RISK ZONES

### Issue 1: Inconsistent Entity Naming Pattern

**Affected Entities:**
- Ferme: `$id_ferme` → `getIdFerme()` (NO `getId()`)
- Animal: `$id_animal` → `getIdAnimal()` (NO `getId()`)
- Plante: `$id_plante` → `getIdPlante()` (NO `getId()`)

**Contrast with Standard Pattern:**
- Analyse: `$id` → `getId()` ✅
- User: `$id` → `getId()` ✅
- Conseil: `$id` → `getId()` ✅

**Risk:** Code may call `getId()` on any of these three entities and crash

**Locations Where This Could Break:**
1. ✅ **FOUND:** FarmerRequestController line 40-41 calls `$ferme->getId()`
2. ✅ **FOUND:** FermeRepositoryTest line 44-45, 58, 62, 125, 139, 175 calls `$ferme->getId()`
3. ✅ **FOUND:** FermeControllerTest line 123 calls `$ferme->getId()`
4. ⚠️ **POTENTIAL:** Any future code that treats Ferme like other entities

### Issue 2: Test File Expects getId() to Exist

**File:** `tests/Unit/Entity/FermeTest.php` line 62-68

```php
public function testIdFermeAliasReturnsSameAsGetId(): void
{
    $ferme = new Ferme();
    $this->assertNull($ferme->getId());        // ❌ FAILS — method doesn't exist
    $this->assertNull($ferme->getIdFerme());   // ✅ PASSES
}
```

**Test Name Implication:** `getId()` should be an alias for `getIdFerme()`

**Current Status:** Test likely FAILS or is SKIPPED

### Issue 3: Inconsistent Usage in Tests

**Pattern Found:**
- Some tests use `$ferme->getIdFerme()` (correct)
- Some tests use `$ferme->getId()` (incorrect, but expected to work)

**Files:**
- `tests/Functional/Controller/FermeControllerTest.php` — Uses both
- `tests/Functional/Controller/AnimalControllerTest.php` — Uses `getIdFerme()`
- `tests/Functional/Controller/AnalyseControllerTest.php` — Uses `getIdFerme()`
- `tests/Repository/FermeRepositoryTest.php` — Uses `getId()` (will fail)

---

## 📋 PROPOSED FIX PLAN

### Fix 1: Add getId() Alias to Ferme Entity
**File:** `src/Entity/Ferme.php`

**Change:**
```php
// Add this method after getIdFerme()
public function getId(): ?int { return $this->id_ferme; }
```

**Why:** Makes Ferme consistent with standard Symfony pattern and fixes FarmerRequestController

**Impact:** Non-breaking — existing code using `getIdFerme()` continues to work

---

### Fix 2: Add getId() Alias to Animal Entity
**File:** `src/Entity/Animal.php`

**Change:**
```php
// Add this method after getIdAnimal()
public function getId(): ?int { return $this->id_animal; }
```

**Why:** Consistency with Ferme pattern and prevents future bugs

**Impact:** Non-breaking

---

### Fix 3: Add getId() Alias to Plante Entity
**File:** `src/Entity/Plante.php`

**Change:**
```php
// Add this method after getIdPlante()
public function getId(): ?int { return $this->id_plante; }
```

**Why:** Consistency with Ferme/Animal pattern

**Impact:** Non-breaking

---

### Fix 4: Run Tests to Verify
**Command:** `php bin/phpunit`

**Expected Result:** All tests pass, including `testIdFermeAliasReturnsSameAsGetId()`

---

### Fix 5: Manual IRL Testing
**Steps:**
1. Start dev server: `php -S localhost:8000 -t public public/router.php`
2. Navigate to `/agricole/nouvelle-demande`
3. Verify form loads without 500 error
4. Submit a request
5. Verify no "undefined method getId" error

---

## ✅ VALIDATION CHECKLIST

- [ ] Ferme::getId() method added
- [ ] Animal::getId() method added
- [ ] Plante::getId() method added
- [ ] All unit tests pass
- [ ] All functional tests pass
- [ ] FarmerRequestController works without errors
- [ ] Dev server starts successfully
- [ ] Farmer can create analysis request
- [ ] No regressions in other modules

---

## 📊 SUMMARY

| Category | Finding |
|----------|---------|
| **Primary Bug** | Ferme::getId() missing — called on line 40 of FarmerRequestController |
| **Root Cause** | Non-standard entity naming (id_ferme vs id) |
| **Scope** | 3 entities affected (Ferme, Animal, Plante) |
| **Severity** | HIGH — Blocks farmer request creation |
| **Fix Complexity** | LOW — Add 3 simple alias methods |
| **Risk Level** | VERY LOW — Non-breaking changes |
| **Test Coverage** | GOOD — Existing tests validate the fix |
| **Estimated Time** | 5-10 minutes |

---

**END OF INVESTIGATION REPORT**
