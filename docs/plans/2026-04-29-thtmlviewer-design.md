# THTMLViewer — Design Spec

**Date:** 2026-04-29
**Branch:** `feature/thtmlviewer-component`
**Status:** Approved for implementation planning

## Summary

`THTMLViewer` is a read-only `TFormControl` that renders an HTML document inside a sandboxed `<iframe>`. The HTML source is held in a hidden `<textarea>` (the canonical control value, form-serializable, DBField-bindable) and pushed into the iframe via `srcdoc`. AJAX-driven updates flow through the standard Tholos `setValue` dispatch.

Closest analog: `THTMLEdit` (uses a textarea + RichTextEditor); `TStatic` (static read-only display + `TStatic_setValue` AJAX path).

## Component spec (canonical, from `docs/Tholos_Component_Types.html`)

- **Inheritance:** `TComponent / TControl / TFormControl / THTMLViewer`
- **Parent must be:** `TContainer`
- **Properties:** standard `TFormControl` baseline (`Attributes`, `Class`, `ControlSize*`, `DBField`, `DBParameterName`, `DBValue`, `DevNote`, `Enabled`, `FunctionCode`, `HelpText`, `Label`, `LabelClass`, `LabelSize*`, `Name`, `NameSuffix`, `RowClass`, `RowStyle`, `Style`, `Value`, `Visible`) **plus one own property:**
  - **`ScriptsAllowed`** — `BOOLEAN`, mandatory, default `false`. Controls iframe sandbox: `false` → `sandbox=""` (no scripts, no same-origin, fully isolated); `true` → `sandbox="allow-scripts"`.
- **Events:** standard inherited (`onAfterCreate`, `onBeforeClick`, `onBeforeCreate`, `onBlur`, `onChange`, `onClick`, `onConfirmFalse`, `onConfirmTrue`, `onHide`, `onMouseDown/Out/Over/Up`, `onShow`, plus PHP `onAfterInit`, `onAfterRender`, `onBeforeCreate`, `onBeforeRender`).
- **Methods:** standard inherited (`parseControlParameters`, `setDataParameters`, `setEnabled`, `setLabel`, `setReadOnly`, `setRequired`, `setValue`, `setVisible`).

## Files

| File | Status | Purpose |
|---|---|---|
| `Base/assets/templates/tholos/THTMLViewer.main.template` | NEW | Eisodos template emitting hidden textarea + iframe + init script. |
| `Base/assets/js/TholosApplication.js` | MODIFIED | Add `THTMLViewer_setValue` handler (insertion point: after `TStatic_setValue`, currently around line 410). |

**No new PHP class.** `THTMLEdit`, `TText`, `TStatic`, `TLabel` have no per-component PHP class either; they're driven by the template plus property metadata loaded from `.tcd` files. THTMLViewer follows the same pattern; instances dispatch through the generic `TFormControl` PHP base.

**No `.tcd` registration in this PR.** Per `Base/CLAUDE.md`, `.tcd` files are compiled by the Tholos Builder Compiler externally; the canonical `THTMLViewer` definition already lives in `docs/Tholos_Component_Types.html`.

**No new asset directories** (no JS lib, CSS, or fonts).

## Template

Path: `Base/assets/templates/tholos/THTMLViewer.main.template`

Skeleton mirrors `THTMLEdit.main.template` (row → label → control-size div → control). The control area emits:

1. A hidden `<textarea id="$prop_id" name="$prop_name" hidden>$prop_value</textarea>` — canonical control value, used by form post, DBField round-trip, and `setValue` target. `$prop_id` is the same id `THTMLEdit` uses on its textarea.
2. A sibling `<iframe id="$prop_id-frame" data-source="#$prop_id" sandbox="…" class="form-control $prop_class" style="$prop_style">` — visual renderer.
3. A small inline `<script nonce="$Tholos_nonce">` that reads the textarea value and assigns it to `iframe.srcdoc` once at render time.

Sketch:

```
## <!-- HTMLViewer $prop_name -->
<div class="row mb-1 $prop_rowclass [%_function_name=Tholos\TholosCallback::_eqs;param=prop_visible;value=true;false=hidden;true=</>%]" style="$prop_rowstyle">
  $templateabs_tholos__TComponent_properties
  <label id="$prop_id-label" for="$prop_id"
         class="col-form-label $templateabs_tholos__TFormControl_labelsize">
    $templateabs_tholos__TComponent_labelicon
  </label>

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

**Key invariants:**
- The textarea carries `id="$prop_id"`; the iframe is `$prop_id-frame`.
- `name="$prop_name"` only on the textarea (form value).
- `$templateabs_tholos__TComponent_basedata` only on the textarea (so AJAX targeting hits it).
- The conditional sandbox attribute uses the same `Tholos\TholosCallback::_eqs` callback pattern used elsewhere in templates (e.g. `THTMLEdit.main.template`).
- `$prop_value` between the textarea tags is HTML-escaped by Eisodos (browser auto-decodes), avoiding attribute-escaping headaches that would arise from `srcdoc="…"`.

## JS — `THTMLViewer_setValue`

Insertion point: `Base/assets/js/TholosApplication.js`, immediately after `TStatic_setValue` (currently around line 410). Modeled on `TStatic_setValue`.

```js
THTMLViewer_setValue: function (sender, target, route, eventData) {
  Tholos.trace("THTMLViewer_setValue()", sender, target, route, eventData);
  var o = Tholos.getObject(target);                  // hidden textarea
  Tholos.setData(target, "value", eventData.value);
  o.val(eventData.value);
  var frame = document.getElementById(o.attr('id') + '-frame');
  if (frame) frame.srcdoc = eventData.value;         // re-render iframe
  Tholos.trace("THTMLViewer_setValue(): Triggering change");
  o.trigger("change");                               // fires onChange GUI event
  return true;
},
```

## Data flow

**Initial render**
1. Server: `TFormControl` resolves `Value` (from `DBField.Value` if bound, else literal).
2. Template emits the textarea with HTML-escaped `$prop_value` between tags.
3. Browser parses; inline init script runs once: `iframe.srcdoc = textarea.value`.

**AJAX update**
1. Any server-driven action targeting this component returns a Tholos `setValue` envelope.
2. Tholos dispatch invokes `THTMLViewer_setValue(sender, target, route, eventData)`.
3. Handler updates the textarea's `val()`, re-pipes to `iframe.srcdoc`, and triggers `change` (so `onChange` GUI handlers fire).

**Form post / DBField round-trip**
- Form submit serialises the textarea by `name`. PHP receives the HTML; `TDBField` writes to the bound DB column. No special handling — same path THTMLEdit uses.

## Security

- **Default (`ScriptsAllowed=false`):** `sandbox=""`. Most restrictive: no scripts, no forms, no top-nav, no popups, opaque cross-origin. Stored `<script>`/`onclick`/`<form>` is rendered visually but inert. Safe for HTML of unknown provenance.
- **Opt-in (`ScriptsAllowed=true`):** `sandbox="allow-scripts"`. Scripts run inside the iframe; still no same-origin, no top-nav, no form submission, no popups. Suitable for trusted authored HTML (RTE preview, generated reports).

**Explicitly out of scope (YAGNI):** per-instance sandbox flag overrides; server-side HTML sanitization (responsibility of whoever stores the HTML); per-frame CSP overrides.

**Inline init script** uses `nonce="$Tholos_nonce"`, matching the `THTMLEdit.main.template` pattern. CSP compatibility verified by analogy; first integration should confirm.

## Vertical sizing

- **Scrollable (supported):** set `Style="height: 600px; width: 100%; border: 1px solid #ccc"` (or any CSS via `Style`/`Class`). If rendered HTML is taller, the iframe gets its own internal scrollbar.
- **Autosize (not implemented, deferred):** would require either `allow-same-origin` in the sandbox (weakens isolation) or a `postMessage` height-shim injected into the iframe content (only works with `ScriptsAllowed=true`). Adds nontrivial complexity (origin checks, multi-instance disambiguation, height-loop hazards). Out of scope for this version; revisit when a real use case appears.

## Testing

`Base/CLAUDE.md` confirms no test suite or linter. Manual test plan, run against a Tholos app consuming the Base library via the dev symlink:

**Initial render**
1. `THTMLViewer` in a `TContainer` with literal `Value` containing a small HTML doc → iframe shows rendered content.
2. Bind `DBField` to a column holding HTML → loads from DB, renders.
3. Empty `Value` → iframe blank, no JS errors.
4. `Visible=false` → row hidden.
5. `Style="height: 400px; width: 100%; border: 1px solid #ccc"` → iframe sized accordingly; vertical overflow scrolls inside the frame.
6. `Label`, `LabelSize*`, `ControlSize*`, `RowClass`, `RowStyle` → grid layout matches THTMLEdit's behaviour in the same form.

**Sandbox**
7. `ScriptsAllowed=false` (default) + HTML containing `<script>alert(1)</script>` → no alert; tag visible in DOM but inert.
8. `ScriptsAllowed=true` + same HTML → alert fires inside iframe context; parent unaffected.
9. With `ScriptsAllowed=true`, in-frame JS attempting `parent.document` → throws `SecurityError`.

**AJAX update**
10. Trigger a server action that returns a `setValue` envelope targeting the viewer → iframe re-renders with new HTML; `onChange` fires; textarea `val()` reflects new value.
11. setValue with empty string → iframe clears.
12. Rapid setValue (~50 updates) → memory stays flat (DevTools Memory tab).

**Form round-trip**
13. Inside a `TForm`, submit → posted form value matches textarea content (round-trips through DBField if bound).

**Browser matrix:** Chromium-latest, Firefox-latest, Safari-latest. `srcdoc` and `sandbox` are universally supported; eyeball Safari for historical sandbox quirks.

## Out of scope

- New PHP class (none of the analogous form controls have one).
- `.tcd` registration (compiled externally by Tholos Builder Compiler; canonical definition already lives in `docs/Tholos_Component_Types.html`).
- AutoSize / dynamic height (deferred; YAGNI).
- Sandbox flag overrides beyond `ScriptsAllowed` (deferred; YAGNI).
- Server-side HTML sanitization (responsibility of the HTML producer).
- Automated test scaffolding (no harness exists in repo).
