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
| `Base/assets/js/TholosApplication.js` | MODIFIED | Add `Tholos.b64` namespace (encode/decode helpers), `THTMLViewer_getValue` (returns decoded HTML), and `THTMLViewer_setValue` (encodes for textarea, decodes for `srcdoc`). |
| `Base/src/Tholos/TholosCallback.php` | MODIFIED | Add `_b64encode_html` callback used by the template to encode `Value` into the hidden textarea. |

**No new PHP class.** `THTMLEdit`, `TText`, `TStatic`, `TLabel` have no per-component PHP class either; they're driven by the template plus property metadata loaded from `.tcd` files. THTMLViewer follows the same pattern; instances dispatch through the generic `TFormControl` PHP base.

**No `.tcd` registration in this PR.** Per `Base/CLAUDE.md`, `.tcd` files are compiled by the Tholos Builder Compiler externally; the canonical `THTMLViewer` definition already lives in `docs/Tholos_Component_Types.html`.

**No new asset directories** (no JS lib, CSS, or fonts).

## Template

Path: `Base/assets/templates/tholos/THTMLViewer.main.template`

Skeleton mirrors `THTMLEdit.main.template` (row → label → control-size div → control). The control area emits:

1. A hidden `<textarea id="$prop_id" name="$prop_name" hidden>…</textarea>` carrying the **base64-encoded** HTML payload. Encoding happens at template time via the `Tholos\TholosCallback::_b64encode_html` callback (idempotent: detects already-encoded input and passes it through). This is what makes the textarea body safe regardless of what the underlying HTML contains — `</textarea>` and other DOM-breaking sequences in raw HTML never reach the parser.
2. A sibling `<iframe id="$prop_id-frame" data-source="#$prop_id" sandbox="…" class="form-control $prop_class" style="$prop_style">` — visual renderer.
3. A small inline `<script nonce="$Tholos_nonce">` that reads the textarea value, base64-decodes it via `Tholos.b64.ensureDecoded`, and assigns the result to `iframe.srcdoc` once at render time.

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
              name="$prop_name" hidden>[%_function_name=Tholos\TholosCallback::_b64encode_html;param=prop_value%]</textarea>
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
    if (ta && f) f.srcdoc = Tholos.b64.ensureDecoded(ta.value);
  })();
</script>
## <!-- /HTMLViewer $prop_name -->
```

**Key invariants:**
- The textarea carries `id="$prop_id"`; the iframe is `$prop_id-frame`.
- `name="$prop_name"` only on the textarea (form value).
- `$templateabs_tholos__TComponent_basedata` only on the textarea (so AJAX targeting hits it).
- The conditional sandbox attribute uses the same `Tholos\TholosCallback::_eqs` callback pattern used elsewhere in templates (e.g. `THTMLEdit.main.template`).
- The textarea body holds **base64-encoded** HTML (via `_b64encode_html`). The init script base64-decodes before piping to `iframe.srcdoc`. This keeps the textarea inert regardless of the raw HTML contents and dodges any `</textarea>` parsing hazard.

## JS — `Tholos.b64`, `THTMLViewer_setValue`, `THTMLViewer_getValue`

### `Tholos.b64` namespace

Added once as a sibling of `Tholos.methods`. Provides UTF-8-safe base64 with idempotent encode/decode. The `isEncoded` heuristic exploits the fact that any HTML always contains characters outside the base64 charset (`<`, `>`, whitespace), making the discriminator reliable.

```js
b64: {
  isEncoded: function (s) {
    return typeof s === 'string' && s.length > 0 && s.length % 4 === 0
      && /^[A-Za-z0-9+/]+=*$/.test(s);
  },
  decode: function (s) {
    try {
      var bin = atob(s);
      var bytes = new Uint8Array(bin.length);
      for (var i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
      return new TextDecoder('utf-8').decode(bytes);
    } catch (e) { return s; }
  },
  encode: function (s) {
    var bytes = new TextEncoder().encode(s);
    var bin = '';
    for (var i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
    return btoa(bin);
  },
  ensureEncoded: function (s) { return Tholos.b64.isEncoded(s) ? s : Tholos.b64.encode(s); },
  ensureDecoded: function (s) { return Tholos.b64.isEncoded(s) ? Tholos.b64.decode(s) : s; }
},
```

### `THTMLViewer_setValue`

Insertion point: `Base/assets/js/TholosApplication.js`, immediately after `TStatic_setValue`. Modeled on `TStatic_setValue` with base64 encode/decode applied at the appropriate boundaries: textarea always holds encoded form, iframe always renders decoded HTML.

```js
THTMLViewer_setValue: function (sender, target, route, eventData) {
  Tholos.trace("THTMLViewer_setValue()", sender, target, route, eventData);
  var o = Tholos.getObject(target);
  var encoded = Tholos.b64.ensureEncoded(eventData.value);
  Tholos.setData(target, "value", encoded);
  o.val(encoded);
  var frame = document.getElementById(o.attr('id') + '-frame');
  if (frame) frame.srcdoc = Tholos.b64.ensureDecoded(eventData.value);
  Tholos.trace("THTMLViewer_setValue(): Triggering change");
  o.trigger("change");
  return true;
},
```

### `THTMLViewer_getValue`

Insertion point: immediately after `TControl_getValue`. Returns the **decoded** HTML so callers (e.g. AJAX form-submit paths that read values via the type-dispatched `getValue` handler) see raw HTML, not the base64 wire format.

```js
THTMLViewer_getValue: function (sender, target, route, eventData) {
  Tholos.trace("THTMLViewer_getValue()", sender, target, route, eventData);
  var o = Tholos.getObject(target);
  return Tholos.b64.ensureDecoded(o.val());
},
```

## PHP — `_b64encode_html` callback

Added to `Base/src/Tholos/TholosCallback.php`. Used by the template to encode `Value` on the way into the textarea. Idempotent: if the input already passes the base64 charset/length/round-trip check, it's returned unchanged.

```php
public static function _b64encode_html($params = array(), $parameterPrefix = ''): string {
  $value = Eisodos::$parameterHandler->getParam($params['param']);
  if ($value === '' || $value === null) {
    return '';
  }
  if (preg_match('/^[A-Za-z0-9+\/]+=*$/', $value) && strlen($value) % 4 === 0) {
    $decoded = base64_decode($value, true);
    if ($decoded !== false && base64_encode($decoded) === $value) {
      return $value;
    }
  }
  return base64_encode($value);
}
```

## Data flow

**Initial render**
1. Server: `TFormControl` resolves `Value` (from `DBField.Value` if bound, else literal).
2. Template emits the textarea body via `_b64encode_html(prop_value)` — base64-encoded payload regardless of `<`/`>` content.
3. Browser parses; inline init script runs once: `iframe.srcdoc = Tholos.b64.ensureDecoded(textarea.value)`.

**AJAX update**
1. Any server-driven action targeting this component returns a Tholos `setValue` envelope. The `value` field can be raw HTML or pre-base64-encoded; the handler is idempotent.
2. Tholos dispatch invokes `THTMLViewer_setValue(sender, target, route, eventData)`.
3. Handler stores `ensureEncoded(value)` in the textarea, sets `iframe.srcdoc` to `ensureDecoded(value)`, and triggers `change` (so `onChange` GUI handlers fire).

**JS-side read via getValue**
- `THTMLViewer_getValue` reads the textarea and returns `ensureDecoded(value)` — callers see raw HTML.

**Form post / DBField round-trip**
- The hidden textarea contains base64. On native form submit, the server receives base64 in `$_POST[$name]`. **Decoding on the server side is the consumer's responsibility** in this iteration (e.g. via a paired `_b64decode_html` parameter-handler hook, or by the receiving action explicitly base64-decoding).
- Why not auto-decode on the receive side here? The Tholos parameter pipeline doesn't offer a per-component server-side post-receive hook in this codebase; introducing one would expand the change scope. Documented as a follow-up — recommended approach: add a paired `_b64decode_html` callback used by any consumer that wants the raw HTML, or wire a TFormControl override for THTMLViewer instances.
- For consumers using the AJAX-form-submit path (which reads values through the type-dispatched `getValue` handler), `THTMLViewer_getValue` already returns decoded HTML, so they're unaffected.

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
