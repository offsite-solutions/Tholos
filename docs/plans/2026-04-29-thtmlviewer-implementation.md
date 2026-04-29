# THTMLViewer Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the `THTMLViewer` component — a read-only `TFormControl` that renders an HTML document inside a sandboxed `<iframe>` driven by a hidden textarea acting as the form-value carrier.

**Architecture:** Two artifacts, no new PHP class. (1) An Eisodos template at `Base/assets/templates/tholos/THTMLViewer.main.template` emits a hidden `<textarea>` (canonical control value) plus a sibling `<iframe>` whose `srcdoc` is set from the textarea by an inline init script. The `sandbox` attribute is conditional on the new `ScriptsAllowed` boolean property: `sandbox=""` when false (no scripts, fully isolated), `sandbox="allow-scripts"` when true. (2) A `THTMLViewer_setValue` handler is added to `Base/assets/js/TholosApplication.js` (mirroring `TStatic_setValue`) that updates the textarea and re-pipes its value to `iframe.srcdoc`, then triggers `change`.

**Tech Stack:** Eisodos templates (Tholos's templating layer), vanilla JS + jQuery (existing TholosApplication.js conventions), browser-native `<iframe srcdoc>` and `sandbox` attribute. PHP 8.4. No new libraries.

**Spec reference:** `Base/docs/plans/2026-04-29-thtmlviewer-design.md` (committed `f30c525`).

**Branch:** `feature/thtmlviewer-component` (already created).

**Working directory for all tasks:** `/Users/baxi/Work/_tholos/Base`.

---

## Preconditions / context

- **No test suite exists** in this repo (`Base/CLAUDE.md` confirms: "There is no test suite or linter configured in this repository"). Verification is **manual**, performed in a downstream Tholos application that consumes this Base library via the dev symlink (`COMPOSER=composer.dev.json`). Each task includes specific browser-side smoke steps.
- **Component registration is out of scope.** The canonical `THTMLViewer` definition already exists in `docs/Tholos_Component_Types.html` and is compiled into `.tcd` files by the external Tholos Builder Compiler. This plan does not touch `.tcd` registration; the consuming app must already have a `.tcd` recognising `THTMLViewer` for end-to-end verification.
- **Closest analogues** to read for context: `Base/assets/templates/tholos/THTMLEdit.main.template`, `Base/assets/templates/tholos/TStatic.main.template`, `TStatic_setValue` in `Base/assets/js/TholosApplication.js` (around line 402).

## File Structure

| File | Status | Responsibility |
|---|---|---|
| `Base/assets/templates/tholos/THTMLViewer.main.template` | NEW | Server-rendered output: hidden textarea + sandboxed iframe + inline init script that pipes textarea → `iframe.srcdoc`. |
| `Base/assets/js/TholosApplication.js` | MODIFIED | Add `THTMLViewer_setValue` handler. Insertion point: immediately after `TStatic_setValue` (currently line 410). |

---

## Task 1: Create the THTMLViewer template

**Files:**
- Create: `Base/assets/templates/tholos/THTMLViewer.main.template`

**Goal:** Emit the row+label+control DOM with the hidden textarea, sandboxed iframe, and inline init script. Sandbox attribute is driven by the `ScriptsAllowed` property.

- [ ] **Step 1: Create the template file with full content**

Create `Base/assets/templates/tholos/THTMLViewer.main.template` with exactly this content:

```
## <!-- HTMLViewer $prop_name -->
<div class="row mb-1 $prop_rowclass [%_function_name=Tholos\TholosCallback::_eqs;param=prop_visible;value=true;false=hidden;true=</>%]" style="$prop_rowstyle">
  $templateabs_tholos__TComponent_properties
  <label id="$prop_id-label" for="$prop_id" class="col-form-label $templateabs_tholos__TFormControl_labelsize">$templateabs_tholos__TComponent_labelicon</label>

  <div class="$templateabs_tholos__TFormControl_controlsize">
    <textarea id="$prop_id" $templateabs_tholos__TComponent_basedata
      name="$prop_name" hidden>$prop_value</textarea>
    <iframe id="$prop_id-frame"
            data-source="#$prop_id"
            class="form-control $prop_class"
            style="$prop_style"
            sandbox="[%_function_name=Tholos\TholosCallback::_eqs;param=prop_scriptsallowed;value=true;true=allow-scripts;false=%]"></iframe>
    $templateabs_tholos__TControl_helpblock
  </div>
</div>
$templateabs_tholos__TControl_baseevents
$templateabs_tholos__TControl_customevents

<script type="text/javascript" nonce="$Tholos_nonce">
  (function () {
    var ta = document.getElementById('$prop_id');
    var f  = document.getElementById('$prop_id-frame');
    if (ta && f) f.srcdoc = ta.value;
  })();
</script>
## <!-- /HTMLViewer $prop_name -->
```

Reference patterns: `THTMLEdit.main.template` for the row/label/control skeleton, `_eqs` callback usage, and the `<script nonce="$Tholos_nonce">` pattern.

- [ ] **Step 2: Verify the file is exactly as written**

Run: `cat Base/assets/templates/tholos/THTMLViewer.main.template`
Expected: 26 lines starting with `## <!-- HTMLViewer $prop_name -->` and ending with `## <!-- /HTMLViewer $prop_name -->`.

- [ ] **Step 3: Manual smoke — initial render with literal Value**

Precondition: a downstream Tholos app where the dev symlink resolves to this Base directory, and where `THTMLViewer` is recognised in the compiled `.tcd`.

In that app, place a `THTMLViewer` inside a `TContainer` with:
- `Name="vTest"`
- `Value="<!DOCTYPE html><html><body><h1>Hello</h1></body></html>"`
- `Style="height: 200px; width: 100%; border: 1px solid #ccc"`
- `ScriptsAllowed=false`

Open the page in Chromium. Inspect the DOM:
- Expected: `<textarea id="vTest" name="vTest" hidden>` containing the HTML.
- Expected: `<iframe id="vTest-frame" sandbox="" style="height: 200px; width: 100%; border: 1px solid #ccc">`.
- Expected: visible rendered `<h1>Hello</h1>` inside the iframe.
- Expected: no JS console errors.

- [ ] **Step 4: Manual smoke — sandbox blocks scripts when ScriptsAllowed=false**

Same setup, change `Value` to:
`<!DOCTYPE html><html><body><h1>Hi</h1><script>window.parent.postMessage('SCRIPT_RAN','*');</script></body></html>`

Reload. Open DevTools console; before reload, install a listener:
```js
window.addEventListener('message', e => console.log('GOT', e.data));
```

Expected: no `GOT SCRIPT_RAN` log; iframe `sandbox=""` blocks script execution. The `<h1>Hi</h1>` is still visible.

- [ ] **Step 5: Manual smoke — scripts run when ScriptsAllowed=true**

Same component, set `ScriptsAllowed=true`. Reload (with the postMessage listener installed before reload).

Expected: `GOT SCRIPT_RAN` is logged; iframe attribute is `sandbox="allow-scripts"`. Verify that script attempting `parent.document` throws `SecurityError` (sandbox without `allow-same-origin`):

Set `Value` to: `<script>try{ parent.document; } catch(e){ window.parent.postMessage('BLOCKED:' + e.name, '*'); }</script>`

Expected: `GOT BLOCKED:SecurityError`.

- [ ] **Step 6: Commit**

```bash
cd /Users/baxi/Work/_tholos/Base
git add assets/templates/tholos/THTMLViewer.main.template
git commit -m "$(cat <<'EOF'
feat(THTMLViewer): add Eisodos template for sandboxed HTML viewer

Renders a hidden textarea (carrying the form value) plus a sibling
iframe whose srcdoc is populated by an inline init script. The sandbox
attribute is driven by the ScriptsAllowed property: empty (fully
isolated) by default, allow-scripts when opted in.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

Expected: one new file in commit, no diff to other files.

---

## Task 2: Add THTMLViewer_setValue handler

**Files:**
- Modify: `Base/assets/js/TholosApplication.js` (insert after the `TStatic_setValue` block, currently line 410)

**Goal:** Wire up the AJAX update path. When a Tholos server response targets a `THTMLViewer` instance with a `setValue` envelope, the handler updates the hidden textarea and pushes the new HTML into the sibling iframe's `srcdoc`, then fires the `change` event so any `onChange` GUI handlers run.

- [ ] **Step 1: Read the existing TStatic_setValue block for context**

Read lines 402–410 of `Base/assets/js/TholosApplication.js`:

```js
TStatic_setValue: function (sender, target, route, eventData) {
  Tholos.trace("TStatic_setValue()", sender, target, route, eventData);
  var o = Tholos.getObject(target);
  Tholos.setData(target, "value", eventData.value);
  o.parent().find('.form-control-plaintext').html(eventData.value);
  Tholos.trace("TStatic_setValue(): Triggering change");
  o.val(eventData.value).trigger("change");
  return true;
},
```

This is the pattern THTMLViewer_setValue mirrors: update the canonical control + the visual sibling, trigger change.

- [ ] **Step 2: Insert THTMLViewer_setValue immediately after TStatic_setValue's closing `},`**

Insert this block at line 411 (i.e. before the existing `TGrid_setValue:` line):

```js
    THTMLViewer_setValue: function (sender, target, route, eventData) {
      Tholos.trace("THTMLViewer_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "value", eventData.value);
      o.val(eventData.value);
      var frame = document.getElementById(o.attr('id') + '-frame');
      if (frame) frame.srcdoc = eventData.value;
      Tholos.trace("THTMLViewer_setValue(): Triggering change");
      o.trigger("change");
      return true;
    },
```

Indentation must match the surrounding handlers (4-space leading indent on the property name, matching `TStatic_setValue` and `TGrid_setValue`).

- [ ] **Step 3: Verify the diff**

Run: `cd /Users/baxi/Work/_tholos/Base && git diff assets/js/TholosApplication.js`

Expected: a 12-line addition between `TStatic_setValue` and `TGrid_setValue`. No other changes.

- [ ] **Step 4: Manual smoke — JS file parses cleanly**

In a downstream Tholos app browser, hard-reload the page that loads `TholosApplication.js`. Open DevTools console.

Expected: no syntax errors during script load. `Tholos.methods.THTMLViewer_setValue` is defined when typed in the console.

Verification command in console:
```js
typeof Tholos.methods.THTMLViewer_setValue === 'function'
```
Expected: `true`.

- [ ] **Step 5: Manual smoke — AJAX setValue updates iframe**

Place a `THTMLViewer` (`Name="vTest"`, `Style="height: 200px; ..."`) and a `TButton` whose action returns a `setValue` Tholos envelope targeting `vTest` with new HTML payload, e.g.:
```html
<!DOCTYPE html><html><body><p>Updated at <span id="t"></span></p></body></html>
```

Click the button.

Expected:
- Iframe re-renders showing the new HTML.
- `$('#vTest').val()` in the console returns the new HTML.
- DevTools `Network` shows the AJAX call; `Console` shows the `THTMLViewer_setValue()` trace lines (since `Tholos.trace` logs to console when tracing is enabled).
- No JS errors.

- [ ] **Step 6: Manual smoke — empty setValue clears the iframe**

Trigger a `setValue` with `value=""`.
Expected: iframe goes blank; `$('#vTest').val() === ''`; no errors.

- [ ] **Step 7: Manual smoke — onChange GUI handler fires**

Add an `onChange` GUI handler on the `THTMLViewer` (any simple action — e.g. log a marker via a sibling `TStatic`).

Trigger a `setValue` round-trip.
Expected: the `onChange` handler fires (visible side effect, e.g. updated TStatic).

- [ ] **Step 8: Commit**

```bash
cd /Users/baxi/Work/_tholos/Base
git add assets/js/TholosApplication.js
git commit -m "$(cat <<'EOF'
feat(THTMLViewer): add setValue handler for AJAX-driven updates

Mirrors TStatic_setValue: writes the new HTML to the hidden textarea,
re-pipes it into the sibling iframe's srcdoc, and triggers change so
onChange GUI handlers fire.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

Expected: one modified file in commit.

---

## Task 3: End-to-end verification

**Files:** none modified. Verification only.

**Goal:** Confirm the full feature works against the spec's manual test plan, including form post round-trip, DBField round-trip, layout properties, and a quick browser-matrix sanity check. No code changes; this task either finds problems (which become follow-up tasks) or signs off the implementation.

- [ ] **Step 1: Verify layout properties**

In a downstream Tholos app, place a `THTMLViewer` inside a `TForm` > `TContainer` alongside a `TText` for visual comparison. Set:
- `Label="HTML preview"`
- `LabelSizeMedium=2`, `ControlSizeMedium=10`
- `RowClass="my-2"`, `RowStyle="border-top: 1px solid #eee;"`
- `Class="my-viewer"`, `Style="height: 300px; width: 100%; border: 1px solid #ccc"`
- `Visible=true`, `Enabled=true`

Expected:
- The label sits in a 2-column box, the iframe in 10 columns, just like a `TText` would.
- `RowClass` and `RowStyle` are reflected on the outer row.
- The iframe carries `class="form-control my-viewer"`.

Toggle `Visible=false` → row gets `hidden` class, content is hidden.

- [ ] **Step 2: Verify form post**

Inside a `TForm`, set the viewer's value to `<p>posted</p>`, submit the form via a `TButton` with a server-side handler that echoes posted parameters.

Expected: the value posted under the viewer's `Name` matches `<p>posted</p>` exactly (textarea is the form value carrier).

- [ ] **Step 3: Verify DBField round-trip**

Bind `DBField` to a column holding HTML in a real query. Render the form.

Expected: the iframe shows the DB-stored HTML on initial render. Submit the form with a different value; verify the column is updated to the new value (post is identical to Step 2; DB write is the standard `TDBField` path — no special THTMLViewer handling needed).

- [ ] **Step 4: Browser matrix sanity (Chromium / Firefox / Safari latest)**

Open the same test page in each browser. Verify:
- Iframe renders with `sandbox=""` (default) — no scripts run.
- Iframe renders with `sandbox="allow-scripts"` — scripts run, isolated from parent.
- AJAX `setValue` round-trip works.
- No console errors.

Safari historical note: sandbox attribute and `srcdoc` are supported in all modern Safari versions; eyeball for any cosmetic differences in the iframe scrollbar.

- [ ] **Step 5: Memory/leak sanity (optional but recommended)**

In Chromium DevTools → Memory, take a heap snapshot. Trigger ~50 `setValue` round-trips with varied HTML. Take a second snapshot.

Expected: no large retained iframe documents. (Setting `srcdoc` re-loads the iframe; previous documents should be GC'd.) If you see an unbounded growth, file a follow-up — but `srcdoc` semantics make a leak unlikely.

- [ ] **Step 6: Update branch and prepare for review**

```bash
cd /Users/baxi/Work/_tholos/Base
git log --oneline feature/thtmlviewer-component ^main
```

Expected output (in order, from oldest to newest):
- `<sha> docs(THTMLViewer): add design spec for new component`
- `<sha> feat(THTMLViewer): add Eisodos template for sandboxed HTML viewer`
- `<sha> feat(THTMLViewer): add setValue handler for AJAX-driven updates`

If all manual checks pass, the branch is ready for code review and merge. The implementation surface is small (one new template file, one ~12-line JS addition) and the verification surface is the manual smoke matrix above.

- [ ] **Step 7: Optional — open a draft PR**

If the user requests it, open a draft PR with a body summarising:
- Spec link: `Base/docs/plans/2026-04-29-thtmlviewer-design.md`
- Files touched: 2
- Manual test plan: link to the spec's "Testing" section
- Known risks: CSP nonce compatibility (verified by analogy with `THTMLEdit.main.template`); AutoSize deferred (YAGNI).

Do NOT push or open a PR without explicit user approval — leave the branch local until then.
