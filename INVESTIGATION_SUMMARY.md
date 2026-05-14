# 🔍 INVESTIGATION COMPLETE: Farm Selection Problem

## 📋 EXECUTIVE SUMMARY

**Issue:** Users cannot select which farm to create an analysis request for. The form auto-selects the first farm ("olo") with no dropdown to choose between multiple farms.

**Root Cause:** The controller hardcodes farm selection to the first farm only, and the template displays it as static text instead of a dropdown.

**Severity:** HIGH — Blocks user workflow when they have multiple farms

**Fix Complexity:** MEDIUM — Requires changes to controller and template

---

## 🎯 WHAT I FOUND

### 1. The Problem in Code

**Controller** (`src/Controller/Web/FarmerRequestController.php` line 33):
```php
$ferme = $user->getFermes()->first();  // ← Gets ONLY first farm
```

**Template** (`templates/portal/agricole/new_request.html.twig` lines 31-38):
```twig
<div style="background:#e8f8f0; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
    <div style="color:#2ecc71; font-weight:600; margin-bottom:0.25rem;">
        <i class="fa-solid fa-map-marker-alt"></i> Ferme sélectionnée
    </div>
    <div style="color:#333;">{{ ferme.nomFerme }}</div>  {# ← Static display, not a selector #}
</div>
```

### 2. Why It's Broken

| Component | Current | Problem |
|-----------|---------|---------|
| **Controller** | `$ferme = $user->getFermes()->first()` | Only gets first farm |
| **Template Pass** | `'ferme' => $ferme` | Only passes one farm |
| **Template Display** | Static `<div>` | No dropdown selector |
| **POST Handler** | No `$request->request->get('ferme')` | Doesn't read user's selection |

### 3. How It Should Work (Reference: Animal Selector)

The Animal form has a working dropdown selector. Here's the pattern:

**Controller** (`src/Controller/AnimalController.php` line 45):
```php
return $this->render('animal/index.html.twig', [
    'fermes' => $fRepo->findAll(),  // ← Passes ALL farms
    // ...
]);
```

**Template** (`templates/animal/index.html.twig` lines 111-115):
```twig
<select name="id_ferme" style="...">
    <option value="">Choisir une ferme...</option>
    {% for ferme in fermes %}
        <option value="{{ ferme.idFerme }}" ...>
            {{ ferme.nomFerme }}
        </option>
    {% endfor %}
</select>
```

### 4. Data Flow Analysis

**Current (Broken):**
```
User has farms: [olo, zzz]
    ↓
Controller: getFermes().first()
    ↓
Gets: olo only
    ↓
Template: Shows "olo" in static box
    ↓
User: Cannot select zzz
```

**Expected (Fixed):**
```
User has farms: [olo, zzz]
    ↓
Controller: Passes all farms to template
    ↓
Template: Shows dropdown with [olo, zzz]
    ↓
User: Selects zzz
    ↓
POST handler: Reads selected farm
    ↓
Analysis: Created for zzz
```

---

## 📊 INVESTIGATION ARTIFACTS

### Files Analyzed
1. ✅ `src/Controller/Web/FarmerRequestController.php` — Found hardcoded farm selection
2. ✅ `templates/portal/agricole/new_request.html.twig` — Found static display instead of selector
3. ✅ `src/Controller/AnimalController.php` — Found working reference pattern
4. ✅ `templates/animal/index.html.twig` — Found working dropdown implementation
5. ✅ `src/Entity/User.php` — Confirmed `getFermes()` returns Collection of all farms

### Key Findings
- User entity has `getFermes()` method that returns ALL farms
- Controller only uses `.first()` to get one farm
- Template never receives the full list of farms
- Animal/Plant selectors use the correct pattern (dropdown with loop)
- No validation that selected farm belongs to user (security issue)

---

## 🔧 WHAT NEEDS TO CHANGE

### 1. Controller Changes
**File:** `src/Controller/Web/FarmerRequestController.php`

**Changes needed:**
- Pass ALL farms to template: `'fermes' => $user->getFermes()`
- Read farm selection from POST: `$fermeId = $request->request->get('ferme')`
- Validate selected farm belongs to user
- Load animals/plants based on selected farm (not hardcoded first farm)

### 2. Template Changes
**File:** `templates/portal/agricole/new_request.html.twig`

**Changes needed:**
- Replace static farm display with `<select>` dropdown
- Loop through all farms: `{% for f in fermes %}`
- Add form field: `<select name="ferme">`
- Match the Animal selector pattern

### 3. Form Submission Changes
**File:** `src/Controller/Web/FarmerRequestController.php` (POST handler)

**Changes needed:**
- Get selected farm from POST data
- Validate it belongs to user
- Use selected farm instead of hardcoded first farm

---

## 📋 EXECUTION PLAN

### Step 1: Update Controller
- Pass all farms to template
- Handle farm selection in POST
- Validate selected farm

### Step 2: Update Template
- Replace static display with dropdown
- Loop through all farms
- Pre-select current farm

### Step 3: Update Animals/Plants Loading
- Load based on selected farm, not hardcoded first farm

### Step 4: Manual Testing
- Test farm selector in browser
- Verify animals/plants update when farm changes
- Verify form submits with correct farm

### Step 5: Run Tests
- Verify functional tests pass
- Verify no regressions

### Step 6: IRL Verification
- Create analysis requests for different farms
- Verify they're saved with correct farm
- Verify Expert can see which farm each analysis is for

---

## ✅ SUCCESS CRITERIA

When the fix is complete:
- ✅ Farm dropdown appears on form (not static display)
- ✅ All user's farms are selectable
- ✅ Selected farm is saved to Analyse entity
- ✅ Animals/plants update when farm changes
- ✅ Expert can see which farm each analysis is for
- ✅ No data mixing between farms

---

## 📝 NEXT STEPS

1. Review this investigation report
2. Confirm you want to proceed with the fix
3. I'll execute the changes in order
4. Each step will be validated before moving to the next

---

## 📎 RELATED DOCUMENTS

- `INVESTIGATION_FARM_SELECTOR.md` — Full technical investigation with code snippets
- `plan.md` — Master plan with detailed execution steps (Mission 2 section)

