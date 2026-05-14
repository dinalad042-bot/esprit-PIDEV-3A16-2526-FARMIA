# 🔧 FARM SELECTOR FIX — DETAILED CODE CHANGES

## 📋 OVERVIEW

This document shows the exact code changes needed to fix the farm selector problem.

---

## 1️⃣ CONTROLLER CHANGES

### File: `src/Controller/Web/FarmerRequestController.php`

#### Change 1: Update the newRequest() method

**BEFORE (lines 31-48):**
```php
public function newRequest(Request $request): Response
{
    $user = $this->getUser();
    $ferme = $user->getFermes()->first();

    if (!$ferme) {
        $this->addFlash('warning', 'Vous devez d\'abord créer une ferme avant de faire une demande d\'analyse.');
        return $this->redirectToRoute('app_ferme_new');
    }

    // Get animals and plants from the farmer's farm
    $animals = $this->animalRepo->findByFerme($ferme->getId());
    $plantes = $this->planteRepo->findByFerme($ferme->getId());

    if ($request->isMethod('POST')) {
        $description = $request->request->get('description');
        $animalId = $request->request->get('animal');
        $planteId = $request->request->get('plante');

        $analyse = new Analyse();
        $analyse->setDemandeur($user);
        $analyse->setFerme($ferme);
        // ... rest of POST handler ...
    }

    return $this->render('portal/agricole/new_request.html.twig', [
        'animals' => $animals,
        'plantes' => $plantes,
        'ferme' => $ferme,
    ]);
}
```

**AFTER (lines 31-60):**
```php
public function newRequest(Request $request): Response
{
    $user = $this->getUser();
    $fermes = $user->getFermes();  // ← Get ALL farms

    if ($fermes->isEmpty()) {
        $this->addFlash('warning', 'Vous devez d\'abord créer une ferme avant de faire une demande d\'analyse.');
        return $this->redirectToRoute('app_ferme_new');
    }

    // Handle farm selection from POST or use first farm as default
    $fermeId = $request->request->get('ferme');
    $ferme = null;

    if ($fermeId) {
        // Find the selected farm and validate it belongs to user
        $ferme = $fermes->filter(fn($f) => $f->getIdFerme() == $fermeId)->first();
    }

    // If no valid farm selected, use first farm
    if (!$ferme) {
        $ferme = $fermes->first();
    }

    // Get animals and plants from the selected farm
    $animals = $this->animalRepo->findByFerme($ferme->getIdFerme());
    $plantes = $this->planteRepo->findByFerme($ferme->getIdFerme());

    if ($request->isMethod('POST')) {
        $description = $request->request->get('description');
        $animalId = $request->request->get('animal');
        $planteId = $request->request->get('plante');

        $analyse = new Analyse();
        $analyse->setDemandeur($user);
        $analyse->setFerme($ferme);  // ← Uses selected farm
        // ... rest of POST handler ...
    }

    return $this->render('portal/agricole/new_request.html.twig', [
        'animals' => $animals,
        'plantes' => $plantes,
        'ferme' => $ferme,
        'fermes' => $fermes,  // ← Pass ALL farms to template
    ]);
}
```

**Key Changes:**
- Line 33: `$ferme = $user->getFermes()->first()` → `$fermes = $user->getFermes()`
- Lines 35-36: Check if fermes is empty (not just if ferme is null)
- Lines 39-48: New logic to handle farm selection from POST
- Line 51: Use `$ferme->getIdFerme()` instead of `$ferme->getId()`
- Line 52: Use `$ferme->getIdFerme()` instead of `$ferme->getId()`
- Line 68: Add `'fermes' => $fermes` to template data

---

## 2️⃣ TEMPLATE CHANGES

### File: `templates/portal/agricole/new_request.html.twig`

#### Change 1: Replace static farm display with dropdown selector

**BEFORE (lines 31-38):**
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

**AFTER (lines 31-48):**
```twig
{# Farm selection dropdown #}
<div style="margin-bottom:1.5rem;">
    <label style="display:block; color:#333; font-weight:600; margin-bottom:0.5rem;">
        <i class="fa-solid fa-map-marker-alt" style="color:#2ecc71;"></i> Sélectionner une ferme *
    </label>
    <select name="ferme" required style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:8px; font-size:0.95rem;">
        <option value="">-- Choisir une ferme --</option>
        {% for f in fermes %}
            <option value="{{ f.idFerme }}" {{ ferme and ferme.idFerme == f.idFerme ? 'selected' : '' }}>
                {{ f.nomFerme }}
            </option>
        {% endfor %}
    </select>
    {% if fermes|length == 0 %}
        <p style="color:#888; font-size:0.85rem; margin-top:0.5rem;">
            <i class="fa-solid fa-info-circle"></i> Vous n'avez pas encore de ferme enregistrée.
        </p>
    {% endif %}
</div>
```

**Key Changes:**
- Replaced static `<div>` display with `<select>` dropdown
- Added `required` attribute to make farm selection mandatory
- Loop through all farms: `{% for f in fermes %}`
- Pre-select current farm: `{{ ferme and ferme.idFerme == f.idFerme ? 'selected' : '' }}`
- Added empty state message when no farms exist
- Styled to match other form fields (animals, plants)

---

## 📊 COMPARISON: BEFORE vs AFTER

### Before (Broken)
```
User has farms: [olo, zzz]
    ↓
Controller: $ferme = $user->getFermes()->first()
    ↓
Template receives: 'ferme' => olo (only)
    ↓
Template displays: Static text "olo"
    ↓
User: Cannot select zzz
    ↓
Form submits: Always uses olo
```

### After (Fixed)
```
User has farms: [olo, zzz]
    ↓
Controller: $fermes = $user->getFermes()
    ↓
Template receives: 'fermes' => [olo, zzz] (all)
    ↓
Template displays: Dropdown with [olo, zzz]
    ↓
User: Selects zzz
    ↓
Form submits: Uses selected farm (zzz)
```

---

## 🔍 DETAILED EXPLANATION

### Controller Logic

**Old Logic:**
```php
$ferme = $user->getFermes()->first();  // Always get first farm
```

**New Logic:**
```php
$fermes = $user->getFermes();  // Get all farms

// Check if user has any farms
if ($fermes->isEmpty()) {
    // Redirect to create farm
}

// Try to get selected farm from POST
$fermeId = $request->request->get('ferme');
$ferme = null;

if ($fermeId) {
    // Find farm in user's farms and validate it belongs to user
    $ferme = $fermes->filter(fn($f) => $f->getIdFerme() == $fermeId)->first();
}

// If no valid farm selected, use first farm as default
if (!$ferme) {
    $ferme = $fermes->first();
}
```

**Why this works:**
1. Gets all user's farms
2. Checks if user has any farms
3. Tries to get selected farm from POST data
4. Validates selected farm belongs to user (security)
5. Falls back to first farm if no selection
6. Passes all farms to template for dropdown

### Template Logic

**Old Template:**
```twig
<div>{{ ferme.nomFerme }}</div>  {# Just displays the farm name #}
```

**New Template:**
```twig
<select name="ferme" required>
    <option value="">-- Choisir une ferme --</option>
    {% for f in fermes %}
        <option value="{{ f.idFerme }}" {{ ferme and ferme.idFerme == f.idFerme ? 'selected' : '' }}>
            {{ f.nomFerme }}
        </option>
    {% endfor %}
</select>
```

**Why this works:**
1. Creates a dropdown with all farms
2. Each option has the farm's ID as value
3. Pre-selects the current farm
4. User can change selection before submitting
5. Form sends selected farm ID in POST data

---

## ✅ VALIDATION CHECKLIST

After making these changes, verify:

- [ ] Controller passes `'fermes'` (plural) to template
- [ ] Template receives all farms
- [ ] Dropdown shows all user's farms
- [ ] Current farm is pre-selected
- [ ] User can select different farm
- [ ] Form submits with selected farm
- [ ] Selected farm is saved to Analyse entity
- [ ] Animals/plants update when farm changes
- [ ] No errors in browser console
- [ ] Tests pass

---

## 🔗 RELATED PATTERNS

### Similar Working Pattern: Animal Selector

**Controller** (`src/Controller/AnimalController.php`):
```php
return $this->render('animal/index.html.twig', [
    'fermes' => $fRepo->findAll(),  // Pass all farms
    // ...
]);
```

**Template** (`templates/animal/index.html.twig`):
```twig
<select name="id_ferme">
    <option value="">Choisir une ferme...</option>
    {% for ferme in fermes %}
        <option value="{{ ferme.idFerme }}" ...>
            {{ ferme.nomFerme }}
        </option>
    {% endfor %}
</select>
```

**Our fix follows the same pattern!**

---

## 📝 NOTES

- The fix is non-breaking: existing code continues to work
- Farm selection is now mandatory (required attribute)
- Selected farm is validated against user's farms (security)
- Animals/plants are loaded based on selected farm
- Default behavior: first farm is selected if no selection made

