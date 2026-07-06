# TWorkflow `Parallel` + Component-Types Doc Refresh — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Regenerate the Tholos component-types reference from the 2026-07-06 extract and implement the new `TWorkflow.Parallel` frontend behavior (fire all child steps' `onExecute` without waiting).

**Architecture:** Two independent deliverables. (1) Documentation: mechanical pandoc regeneration of `Tholos_Component_Types.md` from the newest HTML extract plus one hand-written release-history entry (the "usual way" per the runbook). (2) Frontend: `TWorkflow.main.template` starts rendering its props input + a `display:contents` wrapper, and a new `TWorkflow_execute` handler in `TholosApplication.js` enumerates the child `TWorkflowStep` `-props` inputs and triggers each step's `onExecute` in document order when `Parallel=true`.

**Tech Stack:** PHP 8.4 (Tholos/Eisodos), Eisodos template engine (`.template` files), vanilla JS + jQuery (`TholosApplication.js`), pandoc 3.10 (GFM), git (the `Base/` directory is a git repo).

## Global Constraints

- **Design spec:** `Base/docs/plans/2026-07-06-tworkflow-parallel-design.md` — the authority for behavior; every task must conform to it.
- **No automated test suite or linter exists** in this repo (per `Base/CLAUDE.md`). Verification is therefore: deterministic shell assertions (grep/pandoc/`node --check`) plus a documented manual browser check. Do not invent a test framework.
- **Update runbook:** `Base/docs/Tholos_Component_Types_update_runbook.md` governs the doc regeneration. The pandoc-generated body is **never hand-edited**; only the release-history block is hand-maintained (append-only, newest first).
- **Per-step trigger:** fire `onExecute` **only** — never `onFinished` — from `TWorkflow_execute` (confirmed decision, design §9).
- **`Parallel=false` is a no-op** in this change set. Do not implement sequential orchestration.
- **Documented component-type count is 84 and must stay 84** after regeneration (the new members are properties/methods, not new `## T` sections).
- **Absolute paths** below are rooted at the repo: `/Users/baxi/Work/_tholos/Base`. Git commands target that git root: `git -C /Users/baxi/Work/_tholos/Base ...`.
- **Commits:** commit steps are included per the frequent-commit principle, but this session's policy is to **confirm with the user before committing**. When executing, pause at each commit step for go-ahead.

---

## File Structure

- `Base/docs/Tholos_Component_Types.md` — **modify** (regenerate body + prepend one release-history entry). Task 1.
- `Base/docs/Tholos_Component_Types-20260706144817.html` — **read-only input** (newest extract; source of truth).
- `Base/assets/templates/tholos/TWorkflow.main.template` — **modify** (props include + wrapper div). Task 2.
- `Base/assets/js/TholosApplication.js` — **modify** (add `TWorkflow_execute` handler). Task 2.

---

## Task 1: Documentation refresh (the "usual way")

Regenerate `Tholos_Component_Types.md` from the newest extract and record the full May-11 → Jul-6 change set in one new release-history entry. This task is doc-only and fully independent of Task 2.

**Files:**
- Modify: `/Users/baxi/Work/_tholos/Base/docs/Tholos_Component_Types.md`
- Input (read-only): `/Users/baxi/Work/_tholos/Base/docs/Tholos_Component_Types-20260706144817.html`

**Interfaces:**
- Consumes: nothing from other tasks.
- Produces: nothing other tasks depend on (documentation only).

- [ ] **Step 1: Capture the pre-change baseline (for the after-check)**

Run:
```bash
cd /Users/baxi/Work/_tholos/Base/docs
echo "type count = $(grep -c '^## T' Tholos_Component_Types.md)"   # expect 84
echo "markers    = $(grep -c 'RELEASE HISTORY START\|RELEASE HISTORY END' Tholos_Component_Types.md)"  # expect 2
grep -c 'Execute all belonging TWorkFlowStep parallel' Tholos_Component_Types.md  # expect 0 (not yet present)
```
Expected: `type count = 84`, `markers = 2`, and `0` (Parallel not yet in the doc).

- [ ] **Step 2: Regenerate the file (preserve history, new entry on top)**

Run this whole block (it preserves the existing release-history entries, regenerates the body via pandoc, prepends the new entry, and rewrites the file):
```bash
cd /Users/baxi/Work/_tholos/Base/docs
NEW_HTML=Tholos_Component_Types-20260706144817.html

# (a) Preserve existing release-history ENTRIES only (drop markers + the heading)
awk '/<!-- RELEASE HISTORY START -->/,/<!-- RELEASE HISTORY END -->/' Tholos_Component_Types.md \
  | sed -n '/^- \*\*/,$p' | sed '/<!-- RELEASE HISTORY END -->/d' > /tmp/rh_prev_entries.md

# (b) Regenerate body; strip pandoc's duplicate title line + any leading blank lines
pandoc -f html -t gfm "$NEW_HTML" -o /tmp/body.md
tail -n +2 /tmp/body.md | sed '/./,$!d' > /tmp/body_notitle.md

# (c) The new release-history entry (matches the existing bullet style)
cat > /tmp/rh_new_entry.md <<'EOF'
- **2026-07-06** — `Tholos_Component_Types-20260706144817.html`
  - `TWorkflow`: new property **`Parallel`** (BOOLEAN, mandatory, default `false`) — "Execute all belonging TWorkFlowStep parallel". When `true`, the workflow's frontend `execute()` fires every child `TWorkflowStep`'s `onExecute` in document order without waiting between them; when `false`, steps stay developer-driven.
  - `TWorkflow`: new method **`execute()`** — frontend workflow entry point.
  - `TGrid`: new property **`MultiSelectValue`** (STRING) — "MultiSelect checkbox value".
  - `TGrid`: new methods **`clearSelection()`** ("Clears selection") and **`setValuesRefresh()`** ("Set selection values and refresh").
  - `TGrid`: **`MultiSelectDBField`** — *Hidden data* flag changed Yes→No (now emitted as a `data-` attribute).
EOF

# (d) Assemble: title -> release-history (new entry first) -> pandoc body
{
  echo '# Tholos :: Component Type documentation'
  echo
  echo '<!-- RELEASE HISTORY START -->'
  echo '## Release history'
  echo
  cat /tmp/rh_new_entry.md
  cat /tmp/rh_prev_entries.md
  echo '<!-- RELEASE HISTORY END -->'
  echo
  cat /tmp/body_notitle.md
} > Tholos_Component_Types.md
```
Expected: command completes with no errors; `Tholos_Component_Types.md` rewritten.

- [ ] **Step 3: Run the runbook sanity checks**

Run:
```bash
cd /Users/baxi/Work/_tholos/Base/docs
echo "type count = $(grep -c '^## T' Tholos_Component_Types.md)"   # expect 84
echo "markers    = $(grep -c 'RELEASE HISTORY START\|RELEASE HISTORY END' Tholos_Component_Types.md)"  # expect 2
head -2 Tholos_Component_Types.md                                 # expect the title line then a blank line (no pandoc title duplication)
grep -c 'Execute all belonging TWorkFlowStep parallel' Tholos_Component_Types.md  # expect 1 (now in the body)
grep -n '^- \*\*2026-07-06\*\*' Tholos_Component_Types.md         # expect exactly one match, near the top
```
Expected: `type count = 84`; `markers = 2`; head shows `# Tholos :: Component Type documentation` then a blank line; body contains the Parallel description exactly once; the new dated entry is present once.

- [ ] **Step 4: Confirm the diff is clean (only intended changes)**

Run:
```bash
cd /Users/baxi/Work/_tholos/Base
git diff --stat -- docs/Tholos_Component_Types.md          # only this file changed
git diff -- docs/Tholos_Component_Types.md | grep '^[+-]' | grep -v '^[+-][+-]' | grep -v '^[+-]$'
```
Expected: exactly one file in the stat. The added/removed content lines are: the new `2026-07-06` release-history entry, and — in the body — only these member rows: `MultiSelectValue`, `clearSelection()`, `setValuesRefresh()`, `MultiSelectDBField` (its `Yes`→`No` flip), `Parallel`, and `execute()`. **No other structural churn.** If unrelated body lines appear, stop and investigate (do not hand-edit the body).

- [ ] **Step 5: Reconcile memory (no-op check)**

The documented-type count is unchanged (84) and no path/filename convention changed, so per runbook §7–§8 **no ADR edit and no Claude-memory edit are required**. Confirm and take no action:
```bash
grep -c '^## T' /Users/baxi/Work/_tholos/Base/docs/Tholos_Component_Types.md   # 84 → no memory/ADR change needed
```
Expected: `84`. Do not edit codebase-memory or `~/.claude/.../project_tholos_framework.md`.

- [ ] **Step 6: Commit** (confirm with the user first — see Global Constraints)

```bash
git -C /Users/baxi/Work/_tholos/Base add docs/Tholos_Component_Types.md
git -C /Users/baxi/Work/_tholos/Base commit -m "docs: refresh component types from 2026-07-06 extract (TWorkflow.Parallel, TGrid multiselect members)"
```

---

## Task 2: TWorkflow `Parallel` frontend behavior

Make the workflow render its props + a wrapper element, and add the `TWorkflow_execute` handler that fires every child step's `onExecute` when `Parallel=true`. Template and handler are one deliverable — the behavior is only observable with both present, so they share a single verification cycle.

**Files:**
- Modify: `/Users/baxi/Work/_tholos/Base/assets/templates/tholos/TWorkflow.main.template` (currently the single token `$content`)
- Modify: `/Users/baxi/Work/_tholos/Base/assets/js/TholosApplication.js` (insert after the `TWorkflowStep_execute` method, ~line 1462)

**Interfaces:**
- Consumes: existing framework primitives — `Tholos.getData(id)` (reads `#<id>-props` data attributes), `Tholos.getObject(id)` (returns `$("#"+id)`), `generateDataValues()` emitting `data-parallel` and `data-componenttype` on the props input, and the `methods` dispatch table (`TholosApplication.js`). No dependency on Task 1.
- Produces: `methods.TWorkflow_execute(sender, target, route, eventData)` — the frontend entry point invoked when a workflow's `execute()` action fires. Fires child `TWorkflowStep` `onExecute` events; returns nothing.

- [ ] **Step 1: State the expected observable behavior (acceptance criteria)**

There is no automated test runner (Global Constraints), so record the acceptance criteria this task must satisfy — verified by Steps 5–6:
1. A rendered `TWorkflow` emits `<input id="<id>-props" ... data-parallel="true|false" data-componenttype="TWorkflow">` and a `<div id="<id>" style="display:contents">` wrapping its steps.
2. With `Parallel=true`, invoking the workflow's `execute()` triggers each child `TWorkflowStep`'s `onExecute` (in document order), one after another, without waiting; `onFinished` is **not** triggered by the workflow.
3. With `Parallel=false`, `execute()` does nothing.

- [ ] **Step 2: Edit the workflow template**

Replace the entire contents of `/Users/baxi/Work/_tholos/Base/assets/templates/tholos/TWorkflow.main.template` (currently exactly `$content`) with:
```
$templateabs_tholos__TComponent_properties
<div id="$prop_id" style="display:contents">$content</div>
```

- [ ] **Step 3: Verify the template content**

Run:
```bash
cd /Users/baxi/Work/_tholos/Base
cat assets/templates/tholos/TWorkflow.main.template
grep -q 'templateabs_tholos__TComponent_properties' assets/templates/tholos/TWorkflow.main.template && echo "props include OK"
grep -q 'id="\$prop_id" style="display:contents">\$content</div>' assets/templates/tholos/TWorkflow.main.template && echo "wrapper OK"
```
Expected: both `props include OK` and `wrapper OK` print; the file shows exactly the two lines above.

- [ ] **Step 4: Add the `TWorkflow_execute` handler**

In `/Users/baxi/Work/_tholos/Base/assets/js/TholosApplication.js`, insert the new method immediately **after** the `TWorkflowStep_execute` block and before `TWorkflowStep_execute`'s trailing `,`/`TWizardStep_activate`. Perform this exact replacement.

Find:
```js
    TWorkflowStep_execute: function (sender, target, route, eventData) {
      Tholos.trace("TWorkflowStep_execute()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.trace("TWorkflowStep_execute(): Triggering onExecute");
      o.trigger("onExecute", eventData);
      o.trigger("onFinished", eventData);
    }
    ,
    TWizardStep_activate: function (sender, target, route, eventData) {
```
Replace with:
```js
    TWorkflowStep_execute: function (sender, target, route, eventData) {
      Tholos.trace("TWorkflowStep_execute()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.trace("TWorkflowStep_execute(): Triggering onExecute");
      o.trigger("onExecute", eventData);
      o.trigger("onFinished", eventData);
    }
    ,
    TWorkflow_execute: function (sender, target, route, eventData) {
      Tholos.trace("TWorkflow_execute()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      if (d.parallel === true || d.parallel === "true") {
        var o = Tholos.getObject(target);
        o.find(":input[type='hidden'][id$='-props'][data-componenttype='TWorkflowStep']").each(function () {
          var stepId = $(this).data("id");
          Tholos.trace("TWorkflow_execute(): Triggering onExecute on step", stepId);
          Tholos.getObject(stepId).trigger("onExecute", eventData);
        });
      }
      // Parallel=false -> no-op: steps remain developer-driven (design decision)
    }
    ,
    TWizardStep_activate: function (sender, target, route, eventData) {
```

- [ ] **Step 5: Verify the JS parses and the handler is present**

Run:
```bash
cd /Users/baxi/Work/_tholos/Base
node --check assets/js/TholosApplication.js && echo "JS syntax OK"
grep -n 'TWorkflow_execute: function' assets/js/TholosApplication.js   # expect exactly one match
grep -c "o.trigger(\"onFinished\", eventData);" assets/js/TholosApplication.js  # expect 1 (still only in TWorkflowStep_execute, not added to TWorkflow_execute)
```
Expected: `JS syntax OK`; one `TWorkflow_execute: function` match; the `onFinished` count is `1` (proves the workflow handler fires `onExecute` only).

- [ ] **Step 6: Manual browser verification (documented acceptance)**

No automated harness exists, so verify behavior in the running app against Step 1's criteria. Use a page (or a throwaway `.tcd` definition) containing a `TWorkflow` with `Parallel=true` and ≥2 `TWorkflowStep` children, each with a distinguishable `onExecute` GUI handler (e.g. `console.log('step N')`).

1. Load the page. In DevTools, confirm the workflow rendered `<input id="…-props" data-parallel="true" data-componenttype="TWorkflow">` and `<div id="…" style="display:contents">` around the steps.
2. Enable Tholos tracing if available (`TholosJSDebugLevel="trace"`) and invoke the workflow's `execute()` action (the trigger that dispatches `TWorkflow_execute`).
3. **Expected:** each step's `onExecute` runs in document order without waiting; `TWorkflow_execute(): Triggering onExecute on step <id>` traces appear once per step; no `onFinished` fired by the workflow.
4. Set `Parallel=false` (or omit it) and repeat: `execute()` performs **no** step triggering (no-op).

If a suitable test page is not readily available, record this step as "verified by the framework author in the target app" and hand back to the user for the manual check — do not fabricate a pass.

- [ ] **Step 7: Commit** (confirm with the user first — see Global Constraints)

```bash
git -C /Users/baxi/Work/_tholos/Base add assets/templates/tholos/TWorkflow.main.template assets/js/TholosApplication.js
git -C /Users/baxi/Work/_tholos/Base commit -m "feat(TWorkflow): Parallel property fires all child TWorkflowStep onExecute without waiting"
```

---

## Self-Review

**Spec coverage** (design §4/§5/§6/§9):
- §4 doc refresh (usual way) → Task 1 (Steps 1–6). Full diff (TWorkflow + TGrid members) captured in the release-history entry (Task 1 Step 2c) and asserted (Step 4). ✓
- §4 memory/ADR reconciliation (no-op) → Task 1 Step 5. ✓
- §5 B1 template (props + `display:contents` wrapper) → Task 2 Steps 2–3. ✓
- §5 B2 `TWorkflow_execute` handler (parallel branch, `onExecute` only, doc order) → Task 2 Steps 4–5. ✓
- §6 edge cases (empty steps, missing `Parallel` → falsy → no-op) → covered by the `if (d.parallel …)` guard and `.each` over empty set; asserted behaviorally in Task 2 Step 6.4. ✓
- §9 `onExecute`-only → enforced and asserted (Task 2 Step 5, `onFinished` count = 1). ✓

**Placeholder scan:** No TBD/TODO; every code and command step shows exact content. ✓

**Type/name consistency:** `TWorkflow_execute`, `Tholos.getData`, `Tholos.getObject`, `data-componenttype='TWorkflowStep'`, `data("id")`, `data-parallel` used identically across the design and both tasks. Handler signature `(sender, target, route, eventData)` matches the `methods` dispatch convention. ✓
