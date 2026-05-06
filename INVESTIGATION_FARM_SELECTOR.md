# 🔍 INVESTIGATION REPORT: Farm Selection Problem

## 📋 PROBLEM SUMMARY
- **Page:** "Nouvelle Demande d'Analyse" (New Analysis Request)
- **Issue:** Farm is hardcoded/auto-selected to the first farm only
- **Expected:** User should be able to select which farm to create the analysis request for
- **Current:** No dropdown selector for farm choice

---

## 🔎 INVESTIGATION FINDINGS

### 1. **Form Template Location**
**File:** `templates/portal/agricole/new_request.html.twig`

**Current Implementation:**
```twig
{# Ferme info #}
{% if ferme %}
<div style="background:#e8f8f0; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
    <div style="color:#2ecc71; font-weight:600; margin-bottom:0.25rem;">
        <i class="fa-solid fa-map-marker-alt"></i> Ferme sélectionnée
    </div>
    <div style="color:#333;">{{ ferme.nomFerme }}</div>
</div>
{% endif %}
```

**Problem:** This is a **static display**, not a selector. It just shows the farm name in a green box.

---

### 2. **Controller Logic**
**File:** `src/Controller/Web/FarmerRequestController.php`

**Current Implementation (lines 31-35):**
```php
public function newRequest(Request $request): Response
{
    $user = $this->getUser();
    $ferme = $user->getFermes()->first();  // ← HARDCODED: Gets FIRST farm only!

    if (!$ferme) {
        $this->addFlash('warning', 'Vous devez d\'abord créer une ferme avant de faire une demande d\'analyse.');
        return $this->redirectToRoute('app_ferme_new');
    }

    // Get animals and plants from the farmer's farm
    $animals = $this->animalRepo->findByFerme($ferme->getId());
    $plantes = $this->planteRepo->findByFerme($ferme->getId());
    
    // ... rest of code ...
    
    return $this->render('portal/agricole/new_request.html.twig', [
        'animals' => $animals,
        'plantes' => $plantes,
        'ferme' => $ferme,  // ← Only passes ONE farm
    ]);
}
```

**Root Cause:** 
- Line 33: `$ferme = $user->getFermes()->first();` gets only the FIRST farm
- Line 48: Only passes `'ferme' => $ferme` (singular) to template
- No farm selection logic in POST handler

---

### 3. **Data Flow Analysis**

**User Entity Relationship:**
- `User::getFermes()` returns a `Collection` of all user's farms
- User can have multiple farms (e.g., "olo" and "zzz")
- Currently only the first one is used

**Current Flow:**
```
User → getFermes() → [Farm1, Farm2, Farm3] → .first() → Farm1 only
```

**Expected Flow:**
```
User → getFermes() → [Farm1, Farm2, Farm3] → User selects one → Selected farm
```

---

### 4. **Similar Pattern: Animal & Plant Selectors**

**How Animal/Plant Dropdowns Work (Reference Pattern):**

**Animal Controller** (`src/Controller/AnimalController.php`, line 45):
```php
return $this->render('animal/index.html.twig', [
    'animals' => $aRepo->findBySearchAndSort($search, $sort, $direction),
    'fermes' => $fRepo->findAll(),  // ← Passes ALL farms
    'suivis' => $sRepo->findBy([], ['dateConsultation' => 'DESC']),
    'animal_edit' => null,
    'errors' => [],
    'searchTerm' => $search,
    'currentSort' => $sort,
    'currentDirection' => $direction
]);
```

**Animal Template** (`templates/animal/index.html.twig`, lines 111-115):
```twig
<select name="id_ferme" style="width: 100%; padding: 12px; border: 1px solid {{ errors.id_ferme is defined ? '#dc2626' : '#e2e8f0' }}; border-radius: 10px; background-color: white; outline-color: #2d7a36;">
    <option value="">Choisir une ferme...</option>
    {% for ferme in fermes %}
        <option value="{{ ferme.idFerme }}" {{ animal_edit and animal_edit.ferme and animal_edit.ferme.idFerme == ferme.idFerme ? 'selected' : '' }}>
            {{ ferme.nomFerme }}
```

**Key Differences:**
- ✅ Passes `'fermes'` (plural) - ALL farms
- ✅ Uses `<select>` dropdown with loop
- ✅ Allows user to choose
- ✅ Handles form submission with selected value

---

### 5. **Form Submission Analysis**

**Current POST Handler** (lines 46-88):
```php
if ($request->isMethod('POST')) {
    $description = $request->request->get('description');
    $animalId = $request->request->get('animal');
    $planteId = $request->request->get('plante');

    $analyse = new Analyse();
    $analyse->setDemandeur($user);
    $analyse->setFerme($ferme);  // ← Uses the hardcoded $ferme from line 33
    // ... rest of code ...
}
```

**Problem:** 
- No `$request->request->get('ferme')` to get user's selection
- Always uses the hardcoded first farm

---

## 📊 COMPARISON TABLE

| Aspect | Current (Broken) | Animal Selector (Working) |
|--------|------------------|--------------------------|
| **Controller** | Passes 1 farm | Passes all farms |
| **Template** | Static display | Dropdown selector |
| **Form Field** | None | `<select name="id_ferme">` |
| **POST Handler** | Uses hardcoded farm | Uses `$request->request->get('id_ferme')` |
| **User Choice** | No choice | Full choice |

---

## 🎯 ROOT CAUSES IDENTIFIED

1. **Controller only retrieves first farm** → `$user->getFermes()->first()`
2. **Template doesn't pass all farms** → Only passes `'ferme' => $ferme`
3. **Template has no selector** → Just displays farm name in a box
4. **POST handler ignores farm selection** → No `$request->request->get('ferme')`
5. **No validation** → Doesn't verify selected farm belongs to user

---

## ✅ WHAT NEEDS TO CHANGE

### Controller Changes:
1. Pass ALL user farms to template: `'fermes' => $user->getFermes()`
2. Handle farm selection in POST: `$fermeId = $request->request->get('ferme')`
3. Validate selected farm belongs to user
4. Dynamically load animals/plants based on selected farm

### Template Changes:
1. Replace static farm display with `<select>` dropdown
2. Loop through all farms: `{% for ferme in fermes %}`
3. Add form field: `<select name="ferme">`
4. Match the Animal selector pattern

### Form Submission Changes:
1. Get selected farm from POST data
2. Validate it belongs to user
3. Use selected farm instead of hardcoded first farm

---

## 📝 NEXT STEPS

Ready to create the fix plan with:
- Detailed execution steps
- Code changes for controller
- Code changes for template
- Validation logic
- Testing approach

