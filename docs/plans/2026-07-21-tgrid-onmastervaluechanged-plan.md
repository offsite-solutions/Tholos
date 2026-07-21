# TGrid `onMasterValueChanged` GUI Event — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Surface an already-detected master-value change on a detail `TGrid` as a bindable client-side GUI event, `onMasterValueChanged`, carrying the new master value.

**Architecture:** Two runtime touch-points, no PHP. (1) In `assets/js/TGrid.js`, fire a jQuery `.trigger('onMasterValueChanged', [edata])` on the detail grid element inside each of the three existing `masterDataChange` change-guards. (2) In `assets/templates/tholos/TGrid.partial.foot.template`, add an `_eqs` block that binds an app-supplied handler only when one is defined — identical mechanism to the existing `onChange` / `onAfterRefresh` events. `TComponent::generateEvents()` already produces the `event_onmastervaluechanged` parameter generically, so no PHP change is needed.

**Tech Stack:** PHP 8.4 (Tholos framework, unchanged here), vanilla JS + jQuery (`assets/js/TGrid.js`), Eisodos template files (`assets/templates/tholos/*.template`).

**Design spec:** `Base/docs/plans/2026-07-21-tgrid-onmastervaluechanged-design.md`

## Global Constraints

- **No PHP changes.** The framework already generates `event_onmastervaluechanged` when the app defines the event; do not touch `TComponent::generateEvents()` or any `.php` file.
- **No test suite exists** in this repo (per `CLAUDE.md`). Verification per task is: JS syntax check via `node --check`, static `grep` assertions on the exact edited lines, and (final task) a manual browser checklist. Do not scaffold a new test framework — that is out of scope.
- **Event name is exactly `onMasterValueChanged`** (GUI type). It is already present in the doc source `docs/Tholos_Component_Types-20260721200536.html`.
- **Match existing indentation and the existing `onChange` pattern verbatim.** This is a plumbing change, not a refactor.
- **Preserve the three-site duplication in `TGrid.js`.** Do not consolidate the `masterDataChange` blocks — out of scope.
- **Working directory & git root:** `/Users/baxi/Work/_tholos/Base`. Current branch: `main`. All `git`/`grep`/`node` commands below assume this cwd.

## File Structure

| File | Change | Responsibility |
|---|---|---|
| `assets/js/TGrid.js` | Modify (3 sites: ≈ line 174, 258, 589) | Emit `onMasterValueChanged` on genuine master change |
| `assets/templates/tholos/TGrid.partial.foot.template` | Modify (after the `onafterrefresh` block, ≈ line 63) | Bind the app handler when defined; no-op otherwise |
| `docs/Tholos_Component_Types.md` | Regenerate (Task 3, optional follow-up) | Keep generated reference in sync with the HTML source |

---

### Task 1: Emit `onMasterValueChanged` from `TGrid.js`

Fire the GUI event at the exact moment the detail grid's master value actually changes — inside the `if (current != edata)` guard of all three `masterDataChange` handlers, alongside the existing `TGrid_MasterValueChanged_ = 'T'` flag assignment.

**Files:**
- Modify: `assets/js/TGrid.js` (sites at ≈ lines 174, 258, 589)

**Interfaces:**
- Consumes: nothing from other tasks. In scope at each site: `edata` (the new master value, the `masterDataChange` handler's 2nd arg) and `$(this).data().id` (the detail grid's render id; `this` is the `#<id>-props` element).
- Produces: a jQuery custom event `onMasterValueChanged` triggered on `#<detail-grid-id>` with `[edata]` as data. Task 2's template binding consumes this event.

- [ ] **Step 1: Create the feature branch**

Run:
```bash
git checkout main && git checkout -b feature/tgrid-onmastervaluechanged
```
Expected: `Switched to a new branch 'feature/tgrid-onmastervaluechanged'`

- [ ] **Step 2: Confirm the three change sites before editing**

Run:
```bash
awk 'NR==174||NR==258||NR==589 { match($0,/^ */); printf "line %d: %d leading spaces\n", NR, RLENGTH }' assets/js/TGrid.js
```
Expected:
```
line 174: 14 leading spaces
line 258: 14 leading spaces
line 589: 6 leading spaces
```
(If line numbers drifted, re-locate the three sites with `grep -n "TGrid_MasterValueChanged_\").val('T')" assets/js/TGrid.js` — there must be exactly three, two at 14-space indent and one at 6-space.)

- [ ] **Step 3: Add the trigger at the two 14-space sites (lines 174 & 258)**

Use the Edit tool with `replace_all: true` (the two 14-space occurrences are byte-identical, so one edit updates both):

old_string (14 leading spaces):
```
              $("#helper_" + $(this).data().id + " #TGrid_MasterValueChanged_").val('T');
```
new_string:
```
              $("#helper_" + $(this).data().id + " #TGrid_MasterValueChanged_").val('T');
              $('#' + $(this).data().id).trigger('onMasterValueChanged', [edata]);
```

- [ ] **Step 4: Add the trigger at the 6-space site (line 589)**

Use the Edit tool with `replace_all: false` (this 6-space occurrence is unique):

old_string (6 leading spaces):
```
      $("#helper_" + $(this).data().id + " #TGrid_MasterValueChanged_").val('T');
```
new_string:
```
      $("#helper_" + $(this).data().id + " #TGrid_MasterValueChanged_").val('T');
      $('#' + $(this).data().id).trigger('onMasterValueChanged', [edata]);
```

- [ ] **Step 5: Verify all three triggers are present**

Run:
```bash
grep -nc "trigger('onMasterValueChanged', \[edata\])" assets/js/TGrid.js
```
Expected: `3`

- [ ] **Step 6: Verify JS still parses (syntax check)**

Run:
```bash
node --check assets/js/TGrid.js && echo "SYNTAX OK"
```
Expected: `SYNTAX OK` (no output before it). If `node` is unavailable, open the file and confirm each new line sits inside the `if (...) { }` block, before the `Tholos.eventHandler(... 'refresh')` call, with balanced brackets.

- [ ] **Step 7: Commit**

```bash
git add assets/js/TGrid.js
git commit -m "feat(TGrid): fire onMasterValueChanged on master value change

Trigger a jQuery onMasterValueChanged event carrying the new master
value at all three masterDataChange change-guards in TGrid.js.

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Bind the handler in the foot template

Wire the app-supplied handler to the `onMasterValueChanged` event, following the existing `onChange` / `onAfterRefresh` pattern exactly. The handler's second argument is named `eventData` so the new master value passed by Task 1's `.trigger(..., [edata])` flows into the generated handler call (which uses `eventData`). The `_eqs` guard emits the `.on(...)` binding only when the app actually defined a handler, so an unused event costs nothing.

**Files:**
- Modify: `assets/templates/tholos/TGrid.partial.foot.template` (insert after the `event_onafterrefresh` block, ≈ line 63)

**Interfaces:**
- Consumes: the `onMasterValueChanged` jQuery event triggered by Task 1; the `event_onmastervaluechanged` template parameter emitted generically by `TComponent::generateEvents()` when the app defines the event.
- Produces: a rendered `$("#$prop_id").on('onMasterValueChanged', function (e, eventData) { ... })` binding (only when a handler is defined). End of feature — nothing downstream depends on it.

- [ ] **Step 1: Confirm the anchor block exists**

Run:
```bash
grep -n "event_onafterrefresh" assets/templates/tholos/TGrid.partial.foot.template
```
Expected: two lines (the `param=event_onafterrefresh` line and the `false=...$event_onafterrefresh...` line), around lines 59 and 62.

- [ ] **Step 2: Insert the `onMasterValueChanged` binding block**

Use the Edit tool.

old_string (the whole existing `onafterrefresh` block):
```
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=event_onafterrefresh
value=
true=
false=<script type="text/javascript" nonce="$Tholos_nonce">$("#$prop_id").on('onAfterRefresh', function (e) { $event_onafterrefresh });</script>
%FUNC%>
```
new_string (same block, followed by the new one):
```
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=event_onafterrefresh
value=
true=
false=<script type="text/javascript" nonce="$Tholos_nonce">$("#$prop_id").on('onAfterRefresh', function (e) { $event_onafterrefresh });</script>
%FUNC%>
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=event_onmastervaluechanged
value=
true=
false=<script type="text/javascript" nonce="$Tholos_nonce">$("#$prop_id").on('onMasterValueChanged', function (e, eventData) { $event_onmastervaluechanged });</script>
%FUNC%>
```

- [ ] **Step 3: Verify the block was added**

Run:
```bash
grep -nc "event_onmastervaluechanged\|onMasterValueChanged" assets/templates/tholos/TGrid.partial.foot.template
```
Expected: `2` (the `param=event_onmastervaluechanged` line and the `.on('onMasterValueChanged'...` line).

- [ ] **Step 4: Sanity-check the block shape matches the sibling events**

Run:
```bash
grep -nA6 "param=event_onmastervaluechanged" assets/templates/tholos/TGrid.partial.foot.template
```
Expected: an `_eqs` block whose `false=` line contains `$("#$prop_id").on('onMasterValueChanged', function (e, eventData) { $event_onmastervaluechanged });`, structurally identical to the `onchange` / `onafterrefresh` blocks above it.

- [ ] **Step 5: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.foot.template
git commit -m "feat(TGrid): bind onMasterValueChanged handler in foot template

Add an _eqs block that binds the app-supplied onMasterValueChanged
handler when defined, passing the new master value via eventData.
Mirrors the existing onChange/onAfterRefresh wiring.

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

- [ ] **Step 6: End-to-end manual verification (requires a running app with a master/detail grid)**

The framework has no standalone runner, so this runs in a consuming app that renders a master grid + a detail `TGrid` with an `onMasterValueChanged` handler defined in its `.tcd` (e.g. handler body `console.log('master changed to', eventData);`). Confirm each case:

1. **Handler bound, master changed** — select a different master row → the handler runs exactly once and logs the new master value; the detail grid refreshes.
2. **Handler bound, non-change refresh** — paginate / sort / filter the detail grid → the handler does **not** run.
3. **Handler bound, initial load** — first page render → the handler does **not** run.
4. **No handler defined** — inspect rendered HTML: no `.on('onMasterValueChanged', ...)` script is emitted; browser console shows no error; grid behaves exactly as before.
5. **Re-select the same master row** — the handler does **not** run (guarded by `if (current != edata)`).

If no consuming app is available in this environment, mark this step as "deferred to user acceptance" and note it in the handoff — do not claim end-to-end success without observing it.

---

### Task 3 (optional follow-up): Regenerate the component-types reference

The authoritative HTML source (`docs/Tholos_Component_Types-20260721200536.html`) already lists `onMasterValueChanged`, but the generated Markdown reference (`docs/Tholos_Component_Types.md`) is stale. Regenerating it is documentation upkeep, independent of the runtime change, and uses a dedicated skill.

**Files:**
- Regenerate: `docs/Tholos_Component_Types.md` (plus the skill's release-history / memory reconciliation)

- [ ] **Step 1: Run the regeneration skill**

Invoke the **`cmd-update-tholos-component-types`** skill, pointing it at the newest HTML extract (`docs/Tholos_Component_Types-20260721200536.html`). Follow the skill's own steps (it also appends a release-history entry and reconciles codebase-memory + Claude memory references).

- [ ] **Step 2: Verify the event now appears in the generated Markdown**

Run:
```bash
grep -n "onMasterValueChanged" docs/Tholos_Component_Types.md
```
Expected: at least one hit inside the `### TGrid events` section, typed `GUI`, described "Triggers when master value changed on a detail grid".

- [ ] **Step 3: Commit (if the skill did not already commit)**

```bash
git add docs/Tholos_Component_Types.md
git commit -m "docs(TGrid): regenerate component-types reference with onMasterValueChanged

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Self-Review

**1. Spec coverage** — every spec section maps to a task:
- Public contract (GUI event, fires on detail grid, only on genuine change, payload = new master value, no-op when unbound) → Task 1 (trigger inside the change-guard, `[edata]` payload) + Task 2 (guarded `_eqs` binding, `eventData` arg).
- Implementation change #1 (`TGrid.js`, three sites) → Task 1, Steps 3–4.
- Implementation change #2 (foot template `_eqs` block) → Task 2, Step 2.
- "No PHP / no `generateEvents()` change" → Global Constraints + no PHP task.
- Edge cases (fires only on real change; unbound no-op; re-bind pattern matches `onChange`; three-site duplication preserved) → Global Constraints + Task 2 Step 6 cases 2–5.
- Documentation (HTML already updated; `.md` regen via skill is a follow-up) → Task 3.
- Verification (5 manual cases) → Task 2, Step 6.

**2. Placeholder scan** — no `TBD`/`TODO`/"handle edge cases"/"similar to Task N"; every code step shows the exact old/new strings and every command shows expected output. Task 3 is explicitly marked optional, not a placeholder.

**3. Type / name consistency** — event name is `onMasterValueChanged` and template param `event_onmastervaluechanged` (lowercased, matching Eisodos convention) everywhere; trigger data variable `edata` and handler arg `eventData` are used consistently between Task 1 (producer) and Task 2 (consumer); grid-id expression `$(this).data().id` matches the surrounding `TGrid.js` code.
