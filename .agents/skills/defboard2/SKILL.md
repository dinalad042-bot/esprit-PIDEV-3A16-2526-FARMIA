---
name: defboard2
description: >
  Activate for ANY staging/pre-production testing, module handshake validation, or
  milestone tracking across sessions. Trigger on: "test my modules", "staging test",
  "pre-prod check", "defBoard", "handshake test", "validate my flow", "test the
  connection between X and Y", "resume my board", or when user pastes a defBoard.md.
  Performs BOTH static wire inspection AND runtime probe invocation — simulates the
  trigger, fires the action, asserts the output. No browser clicks. No screenshots.
  Auto-logs every snapshot to defBoard.md. That file IS the session memory.
  Works for ANY stack (React, Vue, Symfony, Python, Node, Mobile, CLI, API).
---

# DefBoard — Staging Intelligence & Session Memory

## Philosophy
>
> Test the handshake, then fire the wire and watch what comes out.
> defBoard.md is the memory. Every milestone gets written there.

---

## Phase 0 — Bootstrap

- If `defBoard.md` exists or was pasted → read last `## STATE` block → resume from there.
- If not → create it. Ask for: project name, stack, scope (module/feature/full), one-line goal.

### defBoard.md Template

# defBoard — [Project Name]

**Stack:** | **Scope:** | **Started:**

## GOAL

[One-line validation goal]

## MODULE MAP

| Module | Trigger | Expected Action | Static | Runtime | Status |
|--------|---------|-----------------|--------|---------|--------|
|        |         |                 | ⬜     | ⬜      | ⬜     |

## SNAPSHOTS

## OPEN ISSUES

---

## Phase 1 — Module Discovery

Map every entry point (button, route, event, CLI command, cron) to its action and expected output.
One row per trigger → action → expected output in the Module Map.

**Hints by stack:**

- React/Vue: `onClick`, `onSubmit`, `useEffect`, store dispatches
- Symfony/Laravel/Django: Route → Controller → Service
- Node/Express: Route handler → middleware → service
- Mobile: Widget event → BLoC/Redux/ViewModel
- CLI: Command → handler → stdout/exit code

---

## Phase 2 — Two-Layer Testing

Every module gets both layers. Both must pass for ✅.

### Layer 1 — Static Wire Check (is it connected?)

| Check | What to verify |
|-------|---------------|
| RENDER | Component/route/endpoint exists without errors |
| WIRE | Trigger is bound to its handler |
| HANDLER | Handler exists, accepts correct input, calls right service |
| OUTPUT | Defined output matches expected shape |
| EDGE | Empty input / error / missing dep is handled |

### Layer 2 — Runtime Probe (fire it, assert the result)

| Check | What to assert |
|-------|---------------|
| INVOCATION | Trigger fires without throwing |
| OUTPUT | Response/state matches expected shape |
| SIDE EFFECT | DB write / event emitted / redirect occurred |

**Probe method by stack:**

| Stack | Method |
|-------|--------|
| React handler | `handler(mockEvent)` → assert state/output |
| REST API | `curl -X POST /endpoint -d '{payload}'` → assert status + body |
| Symfony | PHPUnit `$client->request(...)` → assertResponseStatusCodeSame |
| Laravel | `$this->post('/route', $data)->assertStatus(200)` |
| Django | `client.post('/route/', data)` → assert response + DB |
| Node service | Call `fn(mockInput)` directly → assert return |
| CLI | Run with args → assert stdout/exit code |
| Mobile BLoC | Dispatch event → assert emitted state sequence |

**Always generate a runnable probe artifact** (curl command, test snippet, or CLI call) per module.

---

## Phase 3 — Snapshots

Write one snapshot per milestone. Never batch.

### SNAPSHOT — [timestamp / milestone]

**Module:** | **Phase:** Discovery / Static / Runtime / Fix / Verified

**Layer 1 — Static:**
RENDER ✅/❌/⚠️ | WIRE ✅/❌/⚠️ | HANDLER ✅/❌/⚠️ | OUTPUT ✅/❌/⚠️ | EDGE ✅/❌/⚠️

**Layer 2 — Runtime:**
INVOCATION ✅/❌/⚠️ | OUTPUT ✅/❌/⚠️ | SIDE EFFECT ✅/❌/⚠️

**Probe run:** [actual command or test code]
**Actual output:** [what came back]
**Expected output:** [what should have come back]
**Found:** [issue or confirmation]
**Handled:** [fix / workaround / deferred]
**State after:** [what works / what's open]

---

## Phase 4 — Session Handoff

When ending or context saturating, write to `defBoard.md`:

## STATE — [timestamp]

**Completed:** [modules ✅/❌]
**In progress:** [module — last check]
**Blocked:** [issue]
**Probes generated:** [list]
**Next action:** [exact next step]
**Resume:** Paste defBoard.md and say "resume defBoard"

---

## Phase 5 — Resume Protocol

On paste of `defBoard.md` or "resume defBoard":

1. Read last `## STATE` block
2. Print summary:

📋 Resuming — [Project]
✅ Done: [modules]
🔄 In progress: [module — last check]
🔴 Blocked: [issue]
🧪 Probes ready: [list]
▶️ Next: [action]

1. Continue from Next action. Never redo completed checks.

---

## Rules

- Never say "I would click" — always inspect code or generate a direct invocation
- Both layers required — static alone is incomplete
- Always produce a runnable probe artifact per module
- Write a snapshot before moving to the next module
- Carry open issues forward — never drop them silently
- Log actual output vs expected output in every runtime snapshot
- Fail loudly — ❌ ⚠️ are more valuable than silent skips

## Status Symbols

⬜ Pending | 🔄 In progress | ✅ Both layers pass | ❌ Failed | ⚠️ Partial | 🔒 Blocked | 🗃️ Deferred | 🧪 Probe generated
