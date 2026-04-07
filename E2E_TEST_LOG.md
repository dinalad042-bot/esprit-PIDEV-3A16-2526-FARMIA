# E2E CRUD TEST LOG — 2026-04-07

## MISSION: Analyse & Conseil CRUD Validation
Project URL: http://localhost:8000

### ANALYSE CRUD TESTS
- **A1: READ LIST (Index)** -> **PASS**
- **A2: READ SINGLE (Show)** -> **PASS**
- **A3: CREATE VALIDATION** -> **PASS**
- **A4: CREATE SUCCESS** -> **PASS** (ID created: 12)
- **A5: UPDATE SUCCESS** -> **PASS**
- **A6: DELETE SUCCESS** -> **PASS**

### CONSEIL CRUD TESTS
- **C1: READ LIST** -> **PASS**
- **C2: FILTER BY PRIORITY** -> **FAIL** (UI changes URL but table does not filter results)
- **C3: SEARCH** -> **PASS**
- **C4: CREATE VALIDATION** -> **PASS**
- **C5: MIN LENGTH VALIDATION** -> **PASS**
- **C6: CREATE SUCCESS** -> **FAIL** (Selecting HAUTE priority causes 500 error: `prioriteRaw` mapping missing)
- **C7: UPDATE SUCCESS** -> **FAIL** (Priority change causes 500 error: `prioriteRaw` mapping missing)
- **C8: DELETE SUCCESS** -> **PASS** (Tested with "Moyenne" impact)

### FRONTOFFICE TESTS
- **FO1: Analyse FO List** -> **PASS**
- **FO2: Analyse FO Show** -> **PASS**
- **FO3: Conseil FO List** -> **FAIL** (Filter bug persists)
- **FO4: Dashboard** -> **FAIL** (Blocked by Captcha/Auth gate in this environment)

### SPECIAL FEATURE TESTS
- **SF1: AI Diagnostic** -> **FAIL** (500 Error: `Variable "icon" does not exist` in Twig)
- **SF2: PDF Export** -> **FAIL** (500 Error: `Class "Dompdf\Options" not found`)
- **SF3: Weather API** -> **PASS** (Tunis location, 20.5°C verified)

---

### FINAL SUMMARY
TEST    | FEATURE              | RESULT | NOTES
--------|----------------------|--------|-------
A1      | Analyse List         | PASS   | 10 items, stats OK
A2      | Analyse Show         | PASS   | Buttons visible
A3      | Create Validation    | PASS   | Errors OK
A4      | Create Success       | PASS   | ID=12
A5      | Update               | PASS   | Text changed OK
A6      | Delete               | PASS   | Count returned to 10
C1      | Conseil List         | PASS   | Priority cards OK
C2      | Filter Priority      | FAIL   | Logic bug
C3      | Search               | PASS   | "calcium" OK
C4      | Create Validation    | PASS   | Errors OK
C5      | Min Length Valid      | PASS   | Errors OK
C6      | Create Success       | FAIL   | 500 HAUTE bug
C7      | Update               | FAIL   | 500 Crash
C8      | Delete               | PASS   | Removed OK
FO1     | FO Analyse List      | PASS   | Card grid OK
FO2     | FO Analyse Show      | PASS   | Conseils section OK
FO3     | FO Conseil List      | FAIL   | Filter bug
FO4     | Dashboard            | FAIL   | Captcha/Auth
SF1     | AI Diagnostic        | FAIL   | Twig icon bug
SF2     | PDF Export           | FAIL   | Missing Dompdf
SF3     | Weather API          | PASS   | 20.5°C OK

**TOTAL: 15/21 PASSED**
**FAILED_TESTS:** C2, C6, C7, FO3, FO4, SF1, SF2
**ERRORS_FOUND:**
1. `prioriteRaw` missing setter/mapping in Conseil entity.
2. `icon` variable missing in ai_diagnostic Twig template.
3. `Dompdf\Options` class not found (vendor issue).
4. Priority filter query logic missing in controllers.
