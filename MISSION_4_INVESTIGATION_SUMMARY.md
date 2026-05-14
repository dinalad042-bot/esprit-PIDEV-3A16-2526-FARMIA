# 🔧 MISSION 4: AI Vision API Integration Fix — Investigation Summary

**Date:** 2026-05-06  
**Status:** Investigation Complete  
**Priority:** CRITICAL (Blocks core AI feature)

---

## 🚨 THE PROBLEM

**Error:** `Erreur Vision API: HTTP/2 401 returned for "https://api.groq.com/openai/v1/chat/completions"`

**What users see:**
- Page: Expert Analysis Results (`/expert/analyses/1/ai-result`)
- Error message: "Erreur de diagnostic"
- Symptoms section shows: Full API error with 401 Unauthorized
- AI diagnosis feature is completely broken

**Impact:**
- Users cannot get AI-powered plant/animal diagnosis
- Core feature of the app is non-functional
- Expert dashboard shows error instead of analysis results

---

## 🔍 ROOT CAUSE IDENTIFIED

### Primary Issue: Duplicate GROQ_API_KEY in `.env`

**Location:** `.env` file

**Problem:**
```dotenv
# Line 51-53 (FIRST ENTRY - HAS VALUE)
###> groq/api ###
GROQ_API_KEY="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"
GROQ_MODEL="meta-llama/llama-4-scout-17b-16e-instruct"
###< groq/api ###

# ... other config ...

# Line 62-64 (SECOND ENTRY - EMPTY!)
###> Groq API ###
GROQ_API_KEY=
###< Groq API ###
```

**Why this is a problem:**
- Symfony loads `.env` files sequentially
- The second (empty) entry OVERRIDES the first entry
- App ends up with `GROQ_API_KEY=""` (empty string)
- Groq API rejects empty key with 401 Unauthorized

**This is the root cause of the 401 error!**

---

## 📊 INVESTIGATION FINDINGS

### 1. API Integration Architecture

**Groq API is used in 4 places:**

| File | Method | Purpose | Model |
|------|--------|---------|-------|
| `GroqService.php` | `generateVisionDiagnostic()` | Image analysis | `llama-3.2-11b-vision-preview` |
| `GroqService.php` | `generateTextDiagnostic()` | Text analysis | `llama-3.2-11b-vision-preview` |
| `GroqService.php` | `generateExecutiveSummary()` | Report generation | `llama-3.2-11b-vision-preview` |
| `GroqChatService.php` | `generateResponse()` | Chatbot | `llama-3.3-70b-versatile` |
| `FarmPredictor.php` | `callGroq()` | Farm predictions | `meta-llama/llama-4-scout-17b-16e-instruct` |
| `AnimalController.php` | Direct call | Animal diagnosis | `llama-3.2-11b-vision-preview` |

**All use the same API key and endpoint:** `https://api.groq.com/openai/v1/chat/completions`

### 2. Data Flow: How AI Diagnosis Works

```
User clicks "Relancer le diagnostic"
  ↓
POST /expert/analyse/{id}/diagnose
  ↓
ExpertAIController::diagnose()
  ├─ Validates user is technicien for this analysis
  ├─ Checks if analysis has image
  ├─ Calls GroqService::generateVisionDiagnostic($imageUrl)
  │   ├─ Builds prompt with farm context
  │   ├─ Calls Groq API with image URL
  │   ├─ Parses JSON response
  │   └─ Returns DiagnosisResult (or error)
  ├─ Stores result in Analyse entity
  │   ├─ ai_diagnosis_result (JSON)
  │   ├─ ai_confidence_score
  │   └─ ai_diagnosis_date
  └─ Redirects to /expert/analyse/{id}/ai-result
       ↓
       ExpertAIController::showAiResult()
       ├─ Retrieves stored AI result from database
       └─ Renders template with result
            ↓
            Template displays:
            ├─ Condition (from AI)
            ├─ Symptoms (from AI)
            ├─ Treatment (from AI)
            ├─ Prevention (from AI)
            ├─ Urgency badge
            ├─ Expert consultation recommendation
            └─ Original image
```

**Key insight:** When API fails, the error is stored in the database as the "diagnosis result", so it persists even after page reload.

### 3. Error Handling

**Current error handling in GroqService.php (line 237):**

```php
catch (\Throwable $e) {
    $errorDetails = $e->getMessage();
    if (method_exists($e, 'getResponse')) {
        $response = $e->getResponse();
        if ($response) {
            $errorDetails .= ' | Response: ' . $response->getContent(false);
        }
    }
    return $this->errorResult('Erreur Vision API: ' . $errorDetails);
}
```

**What happens:**
1. API call fails with 401
2. Exception is caught
3. Error message is formatted: `"Erreur Vision API: HTTP/2 401 ..."`
4. `errorResult()` creates a DiagnosisResult with error message
5. Result is stored in database
6. Template displays the error message to user

**Problem:** No retry logic, no fallback, no validation that API key is configured

### 4. Analyse Entity Storage

**Fields in Analyse entity:**

```php
#[ORM\Column(name: 'ai_diagnosis_result', type: 'text', nullable: true)]
private ?string $aiDiagnosisResult = null;  // Stores JSON result (or error)

#[ORM\Column(name: 'ai_diagnosis_date', type: 'datetime', nullable: true)]
private ?\DateTimeInterface $aiDiagnosisDate = null;  // When diagnosis was run

#[ORM\Column(name: 'ai_confidence_score', type: 'string', length: 20, nullable: true)]
private ?string $aiConfidenceScore = null;  // Confidence level (HIGH/MEDIUM/LOW)

#[ORM\Column(name: 'diagnosis_mode', type: 'string', length: 20, nullable: true)]
private ?string $diagnosisMode = null;  // 'vision' or 'text'
```

**When error occurs, this is stored:**
```json
{
  "condition": "Erreur de diagnostic",
  "confidence": "LOW",
  "symptoms": ["Erreur Vision API: HTTP/2 401 ..."],
  "treatment": "Veuillez réessayer ou consulter un expert.",
  "prevention": "",
  "urgency": "Surveiller",
  "needsExpertConsult": true,
  "rawResponse": ""
}
```

### 5. Template Display

**File:** `templates/portal/expert/ai_result.html.twig`

**How error is displayed:**
```twig
{# Line 14 #}
<h3>{{ aiResult.condition|default('Non déterminé') }}</h3>
{# Shows: "Erreur de diagnostic" #}

{# Line 26 #}
<div>{{ aiResult.symptoms is iterable ? aiResult.symptoms|join('\n') : ... }}</div>
{# Shows: "Erreur Vision API: HTTP/2 401 ..." #}
```

---

## 🎯 SOLUTION PLAN

### Step 1: Fix Duplicate GROQ_API_KEY in `.env` ⚡ IMMEDIATE
**Action:** Remove the duplicate empty entry (lines 62-64)

**Result:** App will use the valid API key from line 51

### Step 2: Verify API Key is Valid
**Action:** Test if the key works with a simple API call

**If valid:** Continue to Step 3  
**If invalid:** Ask user for new key from Groq dashboard

### Step 3: Add API Key Validation on Startup
**Action:** Add check in GroqService constructor

**Result:** App fails to start with clear error if key is missing

### Step 4: Add Better Error Messages
**Action:** Improve error handling to distinguish between:
- 401 Unauthorized (invalid key)
- 429 Too Many Requests (rate limited)
- 5xx Server Errors (transient)

**Result:** Users get actionable error messages

### Step 5: Add Retry Logic for Transient Errors
**Action:** Implement retry with exponential backoff (like GroqChatService)

**Result:** Transient errors are automatically retried

### Step 6: Test AI Diagnosis
**Action:** Manual browser testing

**Result:** AI diagnosis works without errors

### Step 7: Run Tests
**Action:** Run staging tests

**Result:** All tests pass

---

## 📋 FILES INVOLVED

### Primary Files
- `.env` — Configuration (HAS DUPLICATE KEY)
- `src/Service/GroqService.php` — Main AI service
- `src/Controller/Web/ExpertAIController.php` — Expert AI controller
- `templates/portal/expert/ai_result.html.twig` — Error display

### Secondary Files
- `src/Service/GroqChatService.php` — Chat service (has retry logic)
- `src/Service/FarmPredictor.php` — Farm prediction
- `src/Controller/AnimalController.php` — Animal diagnosis
- `src/Entity/Analyse.php` — Data storage

### Test Files
- `tests/Staging/ExpertAIConnectionTest.php` — AI diagnosis tests

---

## ✅ SUCCESS CRITERIA

- ✅ No 401 Unauthorized errors
- ✅ AI diagnosis completes successfully
- ✅ Results are displayed to user (not error message)
- ✅ Error messages are clear and actionable
- ✅ Transient errors are retried
- ✅ All tests pass

---

## ⚠️ IMPORTANT NOTES

**This is a production API issue:**
- May need user to provide valid API key
- Don't commit API keys to git (use .env.example instead)
- This blocks the core "AI Expert" feature of the app

**API Key Format:**
- Groq keys start with `gsk_`
- Get from: https://console.groq.com/keys
- Keep secret (don't commit to git)

**If key is invalid:**
- User needs to generate new key from Groq dashboard
- Update `.env` with new key
- Restart app

---

## 📝 NEXT STEPS

1. **Immediate:** Fix duplicate GROQ_API_KEY in `.env`
2. **Verify:** Test if API key works
3. **Improve:** Add validation and better error messages
4. **Test:** Run staging tests
5. **Validate:** Manual browser testing

---

**Investigation completed by:** Kiro  
**Date:** 2026-05-06  
**Status:** Ready for execution
