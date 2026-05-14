# Expert Module - Connection Notes
## Date: 2026-04-14
## Senior Review: Work incomplete, don't over-engineer

---

## 🔗 HOW DOTS CONNECT (Current Flow)

### 1. Farmer Creates Analysis Request
**File:** `src/Controller/Web/FarmerRequestController.php`
- Farmer fills form with description + optional image upload
- Image saved to: `/public/uploads/analyses/`
- Analysis created with status: `en_attente`
- **MISSING:** No notification created for experts

### 2. Expert Takes Request
**File:** `src/Controller/Web/ExpertAnalyseController.php::takeRequest()`
- Expert clicks "Prendre en charge" 
- Status changes: `en_attente` → `en_cours`
- Expert assigned as `technicien`
- **MISSING:** No notification to farmer

### 3. Expert Runs Diagnosis
**Two separate flows exist:**

#### Flow A: Vision Diagnosis
**File:** `src/Controller/Web/ExpertAIController.php::diagnose()`
- Route: `POST /expert/analyse/{id}/diagnose`
- Uses existing `imageUrl` from analysis
- Calls `GroqService::generateVisionDiagnostic()`
- Stores result in `aiDiagnosisResult`, `aiDiagnosisDate`, `aiConfidenceScore`
- **MISSING:** Does NOT track that VISION mode was used

#### Flow B: Text Diagnosis  
**File:** `src/Controller/Web/ExpertAIController.php::diagnoseText()`
- Route: `GET|POST /expert/analyse/{id}/diagnose-text`
- Expert enters observations in textarea
- Calls `GroqService::generateTextDiagnostic()`
- Stores result in same fields
- **MISSING:** Does NOT track that TEXT mode was used

### 4. Context-Aware Prompt Building
**File:** `src/Controller/Web/ExpertAIController.php::buildFarmContext()`
- Called by BOTH diagnosis methods
- Fetches: Plantes (type, santé, qty), Animaux (espèce, santé, count)
- Adds to LLM prompt for better accuracy
- **WORKING CORRECTLY**

### 5. Conseil Management
**File:** `src/Controller/Web/ExpertConseilController.php`
- Expert adds manual conseils via `ConseilType` form
- Priority: HAUTE/MOYENNE/BASSE
- OneToMany: Analyse → Conseils
- **WORKING CORRECTLY**

---

## ❌ GAPS IDENTIFIED (From Requirements vs Implementation)

### Gap 1: No Diagnosis Mode Tracking
**Requirement:** "History showing which diagnosis type (Text/Vision) was used"
**Current:** No field stores TEXT vs VISION
**Location:** `src/Entity/Analyse.php` missing `diagnosisMode` field
**Impact:** Cannot show mode tags in dashboard, cannot filter by mode

### Gap 2: No Unified Form
**Requirement:** "Single form supporting both modes (Text Diagnosis button + Vision Diagnosis button)"
**Current:** Two separate interfaces:
- Vision: Triggered from `analyse_show.html.twig` via button
- Text: Separate page at `diagnose_text.html.twig`
**Location:** Templates need merging
**Impact:** Expert must navigate differently for each mode

### Gap 3: No Notification System
**Requirement:** "Automatically creates notification for the farmer after a new diagnosis"
**Current:** No Notification entity exists (Phase 4 marked PENDING in def_expert.md)
**Location:** Need to create `src/Entity/Notification.php` + `src/Service/NotificationService.php`
**Impact:** Farmer never knows when diagnosis is complete

### Gap 4: Dashboard Missing Mode Tags/Filters
**Requirement:** "List of all analyses with clear tags: Text Mode vs Vision Mode + Filters by mode, farm, urgency, date"
**Current:** `templates/portal/expert/analyses.html.twig` only has basic search
**Location:** Dashboard template needs enhancement
**Impact:** Cannot distinguish or filter by diagnosis type

---

## 📋 DEF_EXPERT.MD PHASES STATUS

| Phase | Status | Content |
|-------|--------|---------|
| Phase 1 | ✅ | Analyse & Conseil CRUD + Dashboard |
| Phase 2 | ✅ | AI Integration (GroqService) |
| Phase 3 | ✅ | PDF Export |
| Phase 4 | ❌ PENDING | Notification System |
| Phase 5 | ✅ | Admin BackOffice |
| Phase 6 | ✅ | Testing Suite |

**Key Finding:** Phase 4 (Notification System) is explicitly marked as pending but REQUIRED by the task.

---

## 🎯 MINIMAL FIXES NEEDED (Per Senior: Don't Over-Engineer)

### Fix 1: Add diagnosisMode Field
**File:** `src/Entity/Analyse.php`
```php
#[ORM\Column(type: 'string', length: 20, nullable: true)]
private ?string $diagnosisMode = null; // 'TEXT' or 'VISION'
```
**Migration:** Required

### Fix 2: Update AI Controller to Track Mode
**File:** `src/Controller/Web/ExpertAIController.php`
- In `diagnose()`: Set `$analyse->setDiagnosisMode('VISION')`
- In `diagnoseText()`: Set `$analyse->setDiagnosisMode('TEXT')`

### Fix 3: Create Notification Entity
**File:** `src/Entity/Notification.php`
- Fields: id, user (recipient), message, type, isRead, createdAt, link
- Relations: ManyToOne User

### Fix 4: Create Notification Service
**File:** `src/Service/NotificationService.php`
- Method: `notifyFarmerOfDiagnosis(Analyse $analyse)`
- Called from ExpertAIController after diagnosis completes

### Fix 5: Update Dashboard Template
**File:** `templates/portal/expert/analyses.html.twig`
- Add mode badges (TEXT/VISION tags)
- Add filters sidebar for mode/farm/urgency/date

---

## 🔍 FILES TO READ FOR FULL PICTURE

1. `src/Entity/Analyse.php` - Current fields and relations
2. `src/Controller/Web/ExpertAIController.php` - Diagnosis flow
3. `src/Controller/Web/FarmerRequestController.php` - Request creation
4. `templates/portal/expert/analyses.html.twig` - Dashboard UI
5. `templates/portal/expert/analyse_show.html.twig` - Analysis detail with AI buttons
6. `def_expert.md` - Phase tracking

---

## ✅ VERIFICATION CHECKLIST

- [x] Text diagnosis works
- [x] Vision diagnosis works
- [x] Conseil CRUD works
- [x] Context passed to LLM
- [ ] Diagnosis mode tracked
- [ ] Unified form exists
- [ ] Notifications sent to farmers
- [ ] Dashboard shows mode tags
- [ ] Dashboard has mode/urgency/farm filters

**Status: 4/9 Complete (44%)**
