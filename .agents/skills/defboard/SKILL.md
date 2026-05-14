---
name: defboard

description: >
  Activate this skill for ANY staging/pre-production testing session, agentic app ordering, module handshake validation, or milestone tracking across sessions. Trigger whenever the user says "test my modules", "staging test", "pre-prod check", "defBoard", "handshake test", "validate my flow", "test the connection between X and Y", "resume my board", or pastes a defBoard.md snippet to resume. This skill replaces E2E mouse-click/screenshot testing with Q&A render checks, auto-logs every state snapshot and milestone to defBoard.md, and survives session saturation — the board is the memory. Works for ANY stack (React, Vue, Symfony, Python, Node, mobile, CLI, API). Always activate when the user wants to test module interactions, track what broke and how it was fixed, or resume work from a prior session using a board file.
---
 
# DefBoard — Staging Intelligence & Session Memory

## Philosophy

> **Test the handshake, not the hand.**  
> We don't click buttons — we validate that the wire between a trigger and its action is live.  
> Every milestone gets written to `defBoard.md`. That file IS the session memory.

---

## Phase 0 — Board Bootstrap

**Always do this first**, before any testing:

1. Check if `defBoard.md` exists in the working directory or was pasted by the user.
   - **If yes**: Read it fully. Extract the last `## STATE` block. Resume from there — announce what phase you're resuming and what's pending.
   - **If no**: Create `defBoard.md` with the template below. Ask the user for the **goal scope** (module / feature / full project) and **stack** if not already known.

### defBoard.md Template (new session)

```markdown
# defBoard — [Project Name]
**Stack:** [e.g. React + Node, Symfony 6.4, Flutter, etc.]
**Scope:** [module name / feature / full project]
**Session started:** [date]
 
---
 
## GOAL
[One-line statement of what we're validating]
 
---
 
## MODULE MAP
<!-- Auto-filled as modules are discovered -->
| Module | Trigger | Expected Action | Status |
|--------|---------|-----------------|--------|
|        |         |                 | ⬜ pending |
 
---
 
## SNAPSHOTS
<!-- One snapshot block per milestone -->
 
---
 
## OPEN ISSUES
<!-- Unresolved items carry forward across sessions -->
```

---

## Phase 1 — Module Discovery

Before testing, map what exists. Ask or infer:

- What are the **entry points**? (buttons, API endpoints, form submits, CLI commands, cron jobs, events)
- What **action** does each entry point trigger?
- What **modules** are involved in that action chain?

Build the **Module Map** table in `defBoard.md`. One row per (trigger → action) pair.

**Stack-specific discovery hints:**

- **React/Vue/Angular**: Look for `onClick`, `onSubmit`, `useEffect`, router hooks, store dispatches
- **Symfony/Laravel/Django**: Routes → Controller methods → Service calls
- **Node/Express**: Route handlers → middleware chain → service layer
- **CLI tools**: Command → handler → output
- **Mobile (Flutter/RN)**: Widget events → BLoC/Redux/ViewModel actions

---

## Phase 2 — Staging Tests (Q&A Mode)

**Core rule: No screenshots. No mouse clicks. Test via Q&A and code inspection.**

For each row in the Module Map, run a **Handshake Check**:

### Handshake Check Protocol

Ask (or inspect code to answer) these questions per module:

```
1. RENDER CHECK     — Does this component/route/endpoint exist and render without errors?
2. WIRE CHECK       — Is the trigger (button/event/route) actually connected to its handler?
3. HANDLER CHECK    — Does the handler exist, accept the right input, and call the right service?
4. OUTPUT CHECK     — Does the output/response/state change match what's expected?
5. EDGE CHECK       — What happens on empty input / error / missing dependency?
```

**How to answer each check (by stack):**

| Stack | How to verify without screenshots |
|-------|-----------------------------------|
| React component | Read JSX — does `onClick={handler}` exist? Does `handler` exist in scope? |
| API endpoint | Check route definition → controller → does it return expected shape? |
| Form submit | Is `onSubmit` bound? Does it call the right mutation/action? |
| Symfony route | `php bin/console debug:router` → does route exist + method match? |
| CLI command | Dry-run or `--help` flag → does command register? |
| DB migration | Does schema match model? Any missing FK or nullable conflicts? |

**Result per check:** ✅ Pass / ❌ Fail / ⚠️ Partial / ❓ Unknown

---

## Phase 3 — Snapshot Writing

**After every milestone** (a module tested, a bug found, a fix applied), write a snapshot to `defBoard.md`.

### Snapshot Format

```markdown
### SNAPSHOT — [timestamp or milestone name]
**Phase:** [Discovery / Testing / Fix / Verified]
**Module tested:** [name]
**Checks run:**
- RENDER: ✅ / ❌ / ⚠️
- WIRE:   ✅ / ❌ / ⚠️
- HANDLER:✅ / ❌ / ⚠️
- OUTPUT: ✅ / ❌ / ⚠️
- EDGE:   ✅ / ❌ / ⚠️
 
**What was found:**
[brief description of issue or confirmation]
 
**How it was handled:**
[fix applied, workaround, deferred — and why]
 
**State after this snapshot:**
[what's now working / what's still open]
```

Write snapshots incrementally — don't batch. One action = one snapshot.

---

## Phase 4 — Session Handoff

When the session is ending or the context is getting saturated:

1. Write a **FINAL STATE** block at the bottom of `defBoard.md`:

```markdown
---
## STATE — [timestamp]
**Completed modules:** [list]
**In progress:** [module + last check completed]
**Blocked on:** [issue description]
**Next action:** [exact next step to resume]
**Resume instruction:** Paste this defBoard.md into a new session and say "resume defBoard"
```

1. Tell the user: _"Session board saved. Paste `defBoard.md` in a new chat and say 'resume defBoard' to pick up exactly here."_

---

## Phase 5 — Resume Protocol

When user pastes a `defBoard.md` or says "resume defBoard":

1. Read the last `## STATE` block
2. Print a **resume summary**:

   ```
   📋 Resuming defBoard — [Project Name]
   ✅ Done: [modules]
   🔄 In progress: [module] — last at [check]
   🔴 Blocked: [issue if any]
   ▶️ Next: [next action]
   ```

3. Continue from **Next action** without re-doing completed checks.

---

## Behavior Rules

- **Never** describe a test as "I would click..." — always inspect code or ask a Q&A question
- **Always** write a snapshot before moving to the next module
- **Carry forward** open issues — never silently drop them
- **Be stack-agnostic** — adapt discovery and check methods to whatever the user is building
- **One scope at a time** — if scope is a single module, don't sprawl into the whole project
- **Fail loudly** — a ❌ or ⚠️ is more valuable than a silent skip

---

## Quick Reference — Status Symbols

| Symbol | Meaning |
|--------|---------|
| ⬜ | Pending — not yet tested |
| 🔄 | In progress |
| ✅ | Verified pass |
| ❌ | Failed — issue found |
| ⚠️ | Partial — needs follow-up |
| 🔒 | Blocked — external dependency |
| 🗃️ | Deferred — out of current scope |
