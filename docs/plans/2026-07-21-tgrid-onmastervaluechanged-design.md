# TGrid `onMasterValueChanged` GUI Event — Design Spec

**Date:** 2026-07-21
**Branch:** `feature/tgrid-onmastervaluechanged` (to be created off `main` at plan execution)
**Status:** Draft — pending user review

## Goal

Add a new **GUI** (client-side) event `onMasterValueChanged` to `TGrid`. It fires in the browser, on a detail grid, when that grid's master value actually changes (the user picks a different master row). The app can bind it to a JS function or a component method — exactly like the existing `onChange` / `onSelectionChange` events.

This is purely a framework-runtime plumbing change: wire up an event that already exists in the component definition. No new detection logic is invented — the master-value-change moment is already computed in `TGrid.js`; we only surface it as a dispatchable event.

The component-types documentation source already contains the event (added by the user in `docs/Tholos_Component_Types-20260721200536.html`):

> **onMasterValueChanged** — GUI — *"Triggers when master value changed on a detail grid"*

## Background — how master/detail change and GUI events work

**Master → detail change (already implemented).** A detail `TGrid` subscribes to the jQuery custom event `masterDataChange` on its `#<id>-props` element. When it fires with `edata` (the master row's new value) and the value differs from the detail grid's current master, the handler:

1. sets `TGrid_MasterValue_` = `edata`,
2. clears `TGrid_Value_` (and, where present, `TGrid_Selection_`),
3. resets `TGrid_ActivePage_` to `1`,
4. flags `TGrid_MasterValueChanged_` = `'T'`,
5. triggers a `refresh` of the detail grid.

This `masterDataChange` handler is duplicated verbatim at **three** sites in `assets/js/TGrid.js` (approx. lines 168–176, 253–260, 583–591). The `if (current != edata) { ... }` guard is the single point where a *genuine* change is known — pagination, sorting and filtering refreshes never enter it, and initial load never enters it.

**GUI event wiring (existing pattern).** A GUI event handler defined by the app is turned into a JS snippet by `TComponent::generateEvents()`, exposed to templates as the parameter `event_<lowercased-name>` (e.g. `event_onchange`). `TGrid.partial.foot.template` then conditionally binds it with a Tholos template callback:

```
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=event_onchange
value=
true=
false=<script type="text/javascript" nonce="$Tholos_nonce">$("#$prop_id").on('onChange', function (e) { $event_onchange });</script>
%FUNC%>
```

The `_eqs` guard emits the `.on(...)` binding **only when** `event_onchange` is non-empty — i.e. only when the app actually defined a handler. The event is then delivered by a jQuery `.trigger()` in `TGrid.js` (e.g. `$('#' + formId_).trigger("onChange", {...})`).

`onMasterValueChanged` reuses this exact mechanism.

## Public Contract

| Surface | Value |
|---|---|
| Event name | `onMasterValueChanged` |
| Event type | GUI (client-side) |
| Fires on | The **detail** `TGrid` element (`#<render-id>`) |
| When | Only when the detail grid's master value genuinely changes (inside the `masterDataChange` `if` guard). Not on pagination/sort/filter refresh, not on initial load. |
| Handler payload | The **new master value** (`edata`), delivered as the handler's `eventData` argument (see below) |
| App binding | Standard GUI event handler in the component definition (`.tcd`), same as `onChange` |
| No-op when unbound | If the app defines no handler, `event_onmastervaluechanged` is empty → the template binds nothing → the `.trigger()` is a harmless no-op |

### Payload note (intentional, minor enhancement over `onChange`)

The existing `onChange` binding is `function (e) { $event_onchange }` and does not forward the value into the generated handler call. For `onMasterValueChanged` the new master value is the single most relevant datum, so the binding names the handler's second argument `eventData`:

```
$("#$prop_id").on('onMasterValueChanged', function (e, eventData) { $event_onmastervaluechanged });
```

Because `generateEvents()` emits the handler call as `func('id', (typeof eventData !== 'undefined' ? eventData : null), ...)`, naming the jQuery handler argument `eventData` makes the trigger's payload flow straight through to the app handler as its event-data parameter. No change to `generateEvents()` is required.

## Implementation

Two files change. No PHP changes.

### 1. `assets/js/TGrid.js` — emit the event at all three change sites

Inside each of the three `masterDataChange` `if (current != edata) { ... }` blocks (≈ lines 168–176, 253–260, 583–591), add one line alongside the existing `TGrid_MasterValueChanged_='T'` assignment, before or after the `refresh` call (order is irrelevant — `refresh` is async):

```js
$('#' + $(this).data().id).trigger('onMasterValueChanged', [edata]);
```

- `$(this).data().id` — the detail grid's render id (`this` is `#<id>-props`).
- `edata` — the new master value; passed as the trigger's data argument so it reaches the handler's `eventData`.

All three sites get the identical line, matching the existing duplication.

### 2. `assets/templates/tholos/TGrid.partial.foot.template` — bind the handler

Add a new `_eqs` block modelled on the existing `onchange` / `onafterrefresh` blocks:

```
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=event_onmastervaluechanged
value=
true=
false=<script type="text/javascript" nonce="$Tholos_nonce">$("#$prop_id").on('onMasterValueChanged', function (e, eventData) { $event_onmastervaluechanged });</script>
%FUNC%>
```

Place it adjacent to the existing `onchange` / `onafterrefresh` blocks (around lines 50–63).

## Edge cases & non-goals

- **Fires only on real change.** The trigger sits inside the `if (current != edata)` guard, so pagination, sorting, filtering and initial load never fire it. This is the desired semantics.
- **Unbound handler.** With no app handler, nothing is bound and the trigger is a no-op — zero overhead, no JS errors.
- **Re-binding across AJAX swaps.** The foot template re-runs on HTML swaps and binds via `.on()` without a preceding `.off()`, exactly as `onChange` / `onAfterRefresh` already do. `onMasterValueChanged` follows the same pattern for consistency; if handler-stacking on repeated swaps is ever a concern it is a pre-existing, framework-wide question and out of scope here. (Flag for the verification step.)
- **Three-site duplication is preserved, not refactored.** The `masterDataChange` block is already duplicated three times in `TGrid.js`. Consolidating it is a larger, riskier change to working code and is deliberately **out of scope** — the feature adds one identical line per site.
- **No PHP / no `generateEvents()` change.** The framework already generates `event_onmastervaluechanged` generically whenever the app defines the event.

## Documentation

- The authoritative source (`docs/Tholos_Component_Types-20260721200536.html`) already lists the event.
- The generated `docs/Tholos_Component_Types.md` still needs to be regenerated from that HTML — done via the **`cmd-update-tholos-component-types`** skill, as a follow-up (not part of the code change in this spec).
- Making the event selectable in the Tholos Builder UI (so apps can attach a handler in the `.tcd`) lives in the Builder / component-definition source, outside this repository.

## Testing / Verification

There is no automated test suite in this repository (per `CLAUDE.md`), so verification is manual, in a master/detail grid page:

1. **Handler bound, master changed** → select a different master row → `onMasterValueChanged` fires once, receiving the new master value; the detail grid refreshes.
2. **Handler bound, non-change refresh** → paginate / sort / filter the detail grid → `onMasterValueChanged` does **not** fire.
3. **Handler bound, initial load** → first render → `onMasterValueChanged` does **not** fire.
4. **No handler defined** → no `.on('onMasterValueChanged', ...)` in the rendered HTML; no JS console error; grid behaves exactly as before.
5. **Payload** → the handler receives the new master value (e.g. `console.log` in the handler shows `edata`).
