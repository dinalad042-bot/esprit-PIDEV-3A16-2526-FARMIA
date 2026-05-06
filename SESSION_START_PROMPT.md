# 📍 SESSION START PROMPT — Module Expert Mission

**Use this prompt at the start of every session until mission complete.**

---

## Quick Briefing

This is a **multi-session bug fix mission** for a Symfony 6.4 PHP app.

**The Bug:** Ferme entity missing `getId()` method — FarmerRequestController calls it on line 40 and crashes.

**The Fix:** Add `getId()` as an alias to three entities (Ferme, Animal, Plante) that use non-standard naming.

**Status:** Investigation complete. Ready to execute fixes.

---

## What to Do Now

1. **Read the plan:** Open `/plan.md`
2. **Find current step:** Look for "Current active step" in SESSION START POINTER section
3. **Check what changed:** Review SESSION LOG to see what was done last session
4. **Execute next step:** Follow the numbered steps in EXECUTION STEPS section
5. **Update plan:** After each completed step, update the plan file with new status

---

## Key Files to Know

- **Plan file:** `/plan.md` — Master reference for all steps
- **Bug location:** `src/Controller/Web/FarmerRequestController.php` line 40
- **Entities to fix:** `src/Entity/Ferme.php`, `src/Entity/Animal.php`, `src/Entity/Plante.php`
- **Test command:** `php bin/phpunit`
- **Dev server:** `php -S localhost:8000 -t public public/router.php`

---

## Quick Commands

```bash
# Run all tests
php bin/phpunit

# Run specific test file
php bin/phpunit tests/Functional/Controller/FarmerRequestControllerTest.php

# Start dev server (port 8000 must be free)
php -S localhost:8000 -t public public/router.php

# Install dependencies (if needed)
composer install
```

---

## When You're Done

- [ ] All steps completed
- [ ] All tests pass
- [ ] IRL testing successful
- [ ] Update plan.md with final status
- [ ] Mark mission as COMPLETE

---

**Ready? Open `/plan.md` and find the current active step.**
