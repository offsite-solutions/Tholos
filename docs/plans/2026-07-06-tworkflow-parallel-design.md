# TWorkflow `Parallel` property + component-types doc refresh — Design

**Date:** 2026-07-06
**Status:** Approved (design)
**Scope:** Documentation refresh (the usual way) **+** frontend implementation of the new `TWorkflow.Parallel` behavior.

## 1. Goal

The Tholos Builder Compiler has emitted a new component-types extract
(`Base/docs/Tholos_Component_Types-20260706144817.html`) that adds a **`Parallel`**
boolean property (and an `execute()` method) to **TWorkflow**. This work:

1. Regenerates `Base/docs/Tholos_Component_Types.md` from that extract per the update runbook,
   and records the full May-11 → Jul-6 change set in the release-history block.
2. Implements the frontend runtime behavior of `Parallel`: when `true`, invoking the
   workflow's frontend `execute()` fires every child `TWorkFlowStep`'s `onExecute` in
   document order, one after another, **without waiting** between them.

## 2. Background / current state

- **`TWorkflow` / `TWorkflowStep` have no PHP class** — they are declarative subtypes
  configured via `.tcd` definitions. All behavior lives in templates + `TholosApplication.js`.
- **`TWorkflow.main.template` is currently just `$content`** — the workflow renders **no
  `-props` element and no wrapper element**. Consequently `Tholos.getData(workflowId)`
  (which reads `#<id>-props`) returns nothing today, and there is no DOM container to
  enumerate the steps from.
- **`TholosApplication.js` has no `TWorkflow_execute` handler** — only
  `TWorkflowStep_execute` (line ~1455), which triggers a single step's `onExecute` then
  `onFinished`, and `action()` (line ~2391), which fires per-step `onSuccess`/`onError`.
- **Step sequencing today is developer-wired** through each step's `onSuccess`/`onFinished`
  GUI event handlers. There is no framework-level workflow runner.
- **Container enumeration idiom** (`TForm`, `TholosApplication.js:1202/1378`):
  `o.find(":input[type='hidden'][id$='-props'][data-componenttype='…'])` where
  `o = Tholos.getObject(target) = $("#"+id)`. Works only if the children are DOM
  descendants of an element carrying the container's id.
- **`generateDataValues()`** (`TComponent.php:541`) emits `data-<propkey>` for every
  property whose `nodata !== 'Y'`. `Parallel` has *Hidden data = No*, so
  `data-parallel="true|false"` **will** appear on the workflow's props input, alongside
  `data-componenttype="TWorkflow"`.

## 3. Design decisions (locked)

| Decision | Choice |
|---|---|
| How `TWorkflow_execute` finds its child steps | **Wrapper div + props** — the template renders the props input and wraps `$content` in `<div id="$prop_id">`, then the handler uses `o.find(...)` (the TForm idiom). |
| `Parallel=false` behavior | **No-op** for now — only the `true` path is built. Steps remain developer-driven. |
| What is triggered per step | **`onExecute` only** (not `onFinished`) — literal to "call the onExecute method," and correct for fire-and-forget where steps have not completed. |
| Step order | **Document order**, synchronous loop, no waiting between steps. |

## 4. Part A — Documentation refresh ("the usual way")

Follow `Base/docs/Tholos_Component_Types_update_runbook.md`:

1. Newest extract = `Tholos_Component_Types-20260706144817.html`; previous =
   `Tholos_Component_Types-20260511111444.html`.
2. Preserve the existing release-history block.
3. Regenerate the body: `pandoc -f html -t gfm <new> -o body.md` (GFM, per runbook).
4. Prepend a **single new release-history entry** dated `2026-07-06` covering the full diff:

   ```markdown
   - **2026-07-06** — `Tholos_Component_Types-20260706144817.html`
     - **TWorkflow**: new **Parallel** property (BOOLEAN, mandatory, default `false`) —
       "Execute all belonging TWorkFlowStep parallel". When `true`, the workflow's frontend
       `execute()` fires every child `TWorkFlowStep`'s `onExecute` in document order without
       waiting between them; when `false`, steps stay developer-driven.
     - **TWorkflow**: new **execute()** method (frontend workflow entry point).
     - **TGrid**: new **MultiSelectValue** property (STRING) — "MultiSelect checkbox value".
     - **TGrid**: new methods **clearSelection()** ("Clears selection") and
       **setValuesRefresh()** ("Set selection values and refresh").
     - **TGrid**: **MultiSelectDBField** — *Hidden data* flag changed Yes→No (now exposed
       as a data attribute).
   ```

5. Assemble the file (title → release-history → pandoc body minus its duplicate title) and
   write with the `Write` tool.
6. Sanity checks (runbook §6): `grep -c '^## T'` type count unchanged; release-history
   markers present exactly twice; no pandoc title duplication.
7. **Codebase-memory / Claude memory:** the documented-type count is **unchanged (84)** and
   no path/filename convention changed → **no ADR edit and no Claude-memory edit required**
   (runbook §7–§8). Re-index is optional; skip unless requested.

> The TGrid items are already implemented upstream (tail of the May-11 multiselect work);
> Part A only documents them. Only the TWorkflow items get new frontend code (Part B).

## 5. Part B — TWorkflow `Parallel` frontend behavior

### B1. `Base/assets/templates/tholos/TWorkflow.main.template`

Replace the current single-token `$content` with:

```
$templateabs_tholos__TComponent_properties
<div id="$prop_id" style="display:contents">$content</div>
```

- `$templateabs_tholos__TComponent_properties` renders `#<id>-props` carrying
  `data-parallel` and `data-componenttype="TWorkflow"`.
- `<div id="$prop_id">` gives `Tholos.getObject(target)` a real element to `.find()` within.
  `style="display:contents"` means the wrapper generates no box → zero layout impact (steps
  are hidden inputs anyway).

### B2. `Base/assets/js/TholosApplication.js` — new `TWorkflow_execute` handler

Add to the `methods` object (next to `TWorkflowStep_execute`):

```js
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
  // Parallel=false → no-op: steps remain developer-driven (per design decision)
},
```

- `d.parallel` may arrive as a real boolean (jQuery `.data()` coerces `"true"`/`"false"`)
  or a string depending on read path, so both are accepted.
- `$(this).data("id")` is the step element's render id; `Tholos.getObject(stepId)` returns
  `#<stepId>`, the hidden input where `TWorkflowStep.main.template` binds `onExecute`.

## 6. Edge cases & caveats

- **Nested workflows:** `.find()` is descendant-based, so a workflow nested inside a step
  would have its steps matched by the outer handler. Same semantics as `TForm`'s descendant
  scan; **out of scope** for v1.
- **No steps:** `.each()` over an empty set is a no-op — safe.
- **Missing `Parallel` data / legacy definitions:** `d.parallel` undefined → falsy → no-op,
  matching current (no-workflow-runner) behavior.
- **`display:contents` + `.find()`:** DOM traversal ignores CSS `display`, so enumeration is
  unaffected.

## 7. Verification (no automated test suite in this repo)

1. **Doc:** runbook §6 sanity greps pass; `git`/visual diff of `Tholos_Component_Types.md`
   shows only the regenerated body + the one new release-history entry.
2. **Template:** rendered workflow HTML now contains `#<id>-props` (with `data-parallel`) and
   a `<div id="<id>" style="display:contents">` wrapping the steps.
3. **Behavior (manual):** a workflow with `Parallel=true` and ≥2 steps, on `execute()`, fires
   each step's `onExecute` in order without waiting (observe via `Tholos.trace` / step
   handlers). With `Parallel=false`, `execute()` is a no-op.

## 8. Out of scope

- Sequential (`Parallel=false`) orchestration / framework-level step chaining.
- Nested-workflow isolation.
- Any PHP class for TWorkflow/TWorkflowStep (they stay declarative subtypes).
- TGrid JS (MultiSelectValue/clearSelection/setValuesRefresh already implemented upstream;
  Part A documents only).

## 9. Resolved decisions

- **Per-step trigger (confirmed 2026-07-06):** fire **`onExecute` only**, not `onFinished`.
  Do **not** reuse `TWorkflowStep_execute` (which also fires `onFinished`); trigger
  `onExecute` directly on each step as shown in §B2.
