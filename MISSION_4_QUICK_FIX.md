# ⚡ MISSION 4: Quick Fix Guide

**Problem:** AI Vision API returns 401 Unauthorized  
**Root Cause:** Duplicate GROQ_API_KEY in `.env` (second entry is empty and overrides first)  
**Fix Time:** 2 minutes

---

## 🔧 IMMEDIATE FIX

### Step 1: Open `.env` file

### Step 2: Find and remove the duplicate (lines 62-64)

**REMOVE THIS:**
```dotenv
###> Groq API ###
GROQ_API_KEY=
###< Groq API ###
```

**KEEP THIS:**
```dotenv
###> groq/api ###
GROQ_API_KEY="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"
GROQ_MODEL="meta-llama/llama-4-scout-17b-16e-instruct"
###< groq/api ###
```

### Step 3: Save the file

### Step 4: Restart the dev server

```bash
# Stop current server (Ctrl+C)
# Then restart:
php -S localhost:8000 -t public public/router.php
```

### Step 5: Test AI diagnosis

1. Login as expert
2. Go to an analysis with an image
3. Click "Relancer le diagnostic"
4. Verify: AI diagnosis completes without 401 error

---

## ✅ VERIFICATION

**Before fix:**
```
Erreur Vision API: HTTP/2 401 returned for "https://api.groq.com/openai/v1/chat/completions"
Response: { "error": { "message": "invalid API Key", "type": "invalid_request_error", "code": "invalid_api_key" } }
```

**After fix:**
```
Condition: [AI-generated diagnosis]
Symptoms: [AI-generated symptoms]
Treatment: [AI-generated treatment]
Prevention: [AI-generated prevention]
```

---

## ⚠️ IF FIX DOESN'T WORK

**If you still get 401 error after removing duplicate:**

The API key itself may be invalid or expired. You need to:

1. Go to https://console.groq.com/keys
2. Generate a new API key
3. Update `.env` with the new key:
   ```dotenv
   GROQ_API_KEY="gsk_YOUR_NEW_KEY_HERE"
   ```
4. Restart the dev server
5. Test again

---

## 📋 WHAT WAS WRONG

**The `.env` file had:**

```dotenv
# Line 51 - FIRST ENTRY (has value)
GROQ_API_KEY="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"

# Line 62 - SECOND ENTRY (empty)
GROQ_API_KEY=
```

**Symfony loads `.env` sequentially, so:**
- First entry sets: `GROQ_API_KEY="gsk_..."`
- Second entry overrides: `GROQ_API_KEY=""` (empty!)
- App ends up with empty key
- Groq API rejects empty key with 401

**Solution:** Remove the duplicate empty entry

---

## 🎯 NEXT STEPS (After Quick Fix)

1. Add API key validation on startup (so empty key fails immediately)
2. Add better error messages (distinguish 401 from other errors)
3. Add retry logic for transient errors
4. Run full test suite

See `MISSION_4_INVESTIGATION_SUMMARY.md` for detailed plan.

---

**Time to fix:** 2 minutes  
**Difficulty:** Easy  
**Risk:** None (just removing duplicate config)
