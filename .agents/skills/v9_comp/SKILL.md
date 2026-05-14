---
name: v9_comp

description: >
  Architecture-first, UI-quality-enforced, checkpoint-gated skill for building anything.
  Trigger on: "build", "create", "let's make", "add feature", "fix this", "I have a codebase",
  any feature brief, any existing project handed to Claude. Prevents silent technical debt,
  generic UI, blueprint drift, and cross-session memory loss. Works for new builds AND
  existing untouched projects. Filesystem-privileged. Lighten for scripts and bug fixes
---

# Build Quality — V9.1

## Entry Point

| Situation | Go to |
|---|---|
| Existing project Claude has never seen | **Phase 0** |
| New build, no code exists yet | **Phase 1** |
| Resuming with a Session Note | **Resume Protocol** |

---

## Phase 0 — Cold Onboarding (existing projects)

Read before touching anything. No code until Cold Start Report is approved.

**1. Directory Survey** — read root + 2 levels deep. Map: structure, language, framework, entry points, data layer, tests.

**2. Architecture Reconstruction** — reconstruct: system boundaries, data models, API contracts, state ownership, load-bearing decisions. Tag each `[CONFIRMED]` or `[INFERRED]`.

**3. Fragile Point Discovery** — scan entire codebase:

- `TODO/FIXME/HACK/XXX` → WATCH (escalate if auth/data layer)
- Raw SQL outside data layer → WARN
- Auth touch points (session/token/credential imports) → CRITICAL
- Hardcoded secrets/magic numbers → CRITICAL
- Functions >100 lines, no tests → WATCH
- Circular deps / god objects → WARN
- Dead code blocks >20 lines → WATCH

**4. Uncertainty Register** — list everything unresolvable from reading alone: ambiguous logic, decisions that look wrong but may be intentional, files with unclear purpose.

**5. Approval Gate** — present Cold Start Report. Say:
> *"[N] CONFIRMED, [N] INFERRED. [N CRITICAL / N WARN / N WATCH] fragile points. Uncertainties: [list]. Correct INFERRED, answer uncertainties, then approve."*

No code until explicit approval.
If user rejects: update changed sections only, re-scan affected fragile points, re-present deltas. If user says stop: list minimum info needed, wait.

**6. Generate Session Note** — from approved report. Project now treated identically to one Claude built. All protocols below apply.

---

## Phase 1 — Blueprint (new builds)

No code until approved. Cover both layers:

**Architecture:** system boundaries · data models + migration risks · API contracts (stable vs. evolving) · state ownership · load-bearing decisions (auth / storage / deployment / async) · failure states + race conditions

**UI/UX:** interface inventory + flows · aesthetic direction (tone / typography intent / color logic / motion role — commit, no defaults) · component hierarchy + hero element · quality constraints (no generic fonts, no equal-weight palettes, hover/focus states, 4px/8px spacing scale, mobile explicit, WCAG AA)

**Gate:** *"Blueprint ready. Approve or adjust before I write any code."* Wait for explicit approval.

---

## Phase 2 — Implementation Order

Data layer → Core logic → I/O layer → UI foundation → Components → Interactions → Cross-cutting concerns

---

## Phase 3 — Layer Checkpoint (after EVERY layer)

1. **Read actual files** (filesystem) — verify paths exist, first 20 lines match role, update File Map from ground truth
2. **Scan for new fragile points** — auto-discover and tier immediately
3. **Report:** *"Layer [N] complete. Divergence: [list / none]. New fragile points: [list / none]. Confirm to continue."*

**On significant drift:** stop. Roll back to last approved checkpoint. No patching forward.

---

## Fragile Point Tiers

| Tier | Behavior |
|---|---|
| CRITICAL | Hard stop before any code. Wait for explicit approval. No workarounds. |
| WARN | Flag + state exact mitigation before implementing. Escalate to CRITICAL if mitigation impossible. |
| WATCH | Inline note only. Auto-upgrade to WARN if surrounding module is structurally changing. |

**Tier assignment:**

- CRITICAL: load-bearing for auth / data integrity / external contracts, silent failure possible
- WARN: known side effect, safe path exists
- WATCH: latent risk, activates under scale or adjacent change

**Log immediately when:**

- Load-bearing decision implemented → CRITICAL
- Workaround used instead of clean solution → WARN
- "Good enough for now" / "revisit later" written → WATCH
- User flags something fragile → tier by context
- Filesystem scan finds TODO/FIXME/HACK → WATCH, escalate if auth/data

---

## UI Self-Enforcement Checklist

*(run internally before presenting any frontend code)*

- [ ] Font deliberate and non-generic (no Inter/Roboto/Arial/Space Grotesk without justification)
- [ ] Color has dominant + accent logic, not equal distribution
- [ ] Layout has intentional hierarchy or distinctive spatial decision
- [ ] One element is unforgettable
- [ ] All interactive elements have hover/focus states
- [ ] Spacing follows consistent scale
- [ ] Mobile explicitly handled
- [ ] Motion (if any) is purposeful, not decorative
- [ ] No component is a default UI library clone without modification

Fail any item → fix before presenting, not after.

---

## Session Note (auto-generated at session end)

Generate without being asked. User saves and pastes to resume.

PROJECT: [name] | DATE: [today] | STATUS: [Phase — Layer — status]
Last trusted state: Layer [N]. Above = untrusted.

BLUEPRINT: [1-line system] | Auth:[x] Storage:[x] Deploy:[x] UI tone:[x] Hero:[x]

LAYERS: [x] L1-Data [x] L2-Logic [ ] L3-IO...

FILE MAP (CRITICAL+WARN only):

- [role]: [path] ← [1-line note, tag INFERRED if from Phase 0]

FRAGILE POINTS:

- [CRITICAL] [file:line] — [why] — [hard stop condition]
- [WARN] [file:line] — [why] — [mitigation]

DRIFT LOG: [layer: what drifted]
UNCERTAINTIES: [Phase 0 items still unresolved]
OPEN: [architectural decisions pending]

RESUME: Paste this note. Add "Resume from note. Read FILE MAP and FRAGILE POINTS first."

**Split when:** File Map >12 entries OR Fragile Points >10 → produce Active Note (CRITICAL+WARN only) + PROJECT_INDEX.md (all WATCH + full history). Request index only when touching WATCH-tier areas.

---

## Resume Protocol

Run all four steps before any code:

**1. Sequence check** — layers in valid order, last trusted state consistent, all CRITICAL points have mitigations. Flag gaps.

**2. File Map verification** (filesystem) — each file: exists? role matches first 20 lines? modified after note date?

- Timestamp conflict + content changed → *"CONFLICT: [file]. A) Update note B) Flag untrusted C) Abort. Choose A/B/C."*
- Timestamp conflict + content unchanged → *"POSSIBLE CONFLICT: formatter touch? Y=intentional→A/B/C, N=clear+continue."*
- INFERRED entries conflict → treat as higher priority than CONFIRMED. INFERRED accuracy degrades faster — resolve before continuing.

**3. Fragile point verification** (filesystem) — CRITICAL+WARN: file:line still exists? function signature changed? If shifted → *"FRAGILE POINT DRIFT: [file:line]. Review before I touch this."*

**4. CRITICAL audit** — list all CRITICAL points aloud. Wait for confirmation. Then: *"Resume check complete. [N] verified. Continuing from Layer [N]."*

**Boundary:** Claude reads + flags + presents options. User decides. Read privilege ≠ write authority.

---

## When to Lighten

- New feature on existing codebase → Phase 0 abbreviated + load-bearing decisions only
- UI component only → UI checklist + Phase 1B only
- Small script / bug fix → skip, use judgment
