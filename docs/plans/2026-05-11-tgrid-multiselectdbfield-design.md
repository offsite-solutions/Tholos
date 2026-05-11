# TGrid MultiSelectDBField & setValues — Design Spec

**Date:** 2026-05-11
**Branch:** `feature/tgrid-multiselectdbfield` (to be created off `main` at plan execution)
**Status:** Draft — pending user review
**Builds on:** `2026-04-28-tgrid-multiselect-design.md` (merged in PR #1)

## Goal

Extend the merged TGrid multi-select feature so the checkbox column is driven by a dedicated `MultiSelectDBField` TDBField child instead of the row's `DBField` value, and add a new dispatchable JS method `setValues` that overwrites the selection from a GUI event.

Two semantic shifts:

1. **`MultiSelectDBField` is the canonical source of each row's checkbox value.** `DBField` retains its existing role as the "current row" identity used by `Selectable` / `LookupValue` / master-detail; the two are decoupled.
2. **Per-row visibility gate.** If a row's resolved `MultiSelectDBField` value is empty/null, that row renders an empty `<td>` (column alignment preserved) and no `<input>` — the row is not checkable.

## Public Contract

| Surface | Type | Required to render column | Purpose |
|---|---|---|---|
| `MultiSelect` (property) | BOOLEAN, default `false` | yes | Existing master switch. Unchanged. |
| `MultiSelectDBField` (TDBField child) | TDBField | **yes** | Per-row checkbox value source AND per-row visibility gate. Empty row value → no `<input>` rendered for that row. |
| `setValues(json_)` (JS method, dispatchable) | (string) → void | n/a | Overwrites the selection set with a JSON-encoded array string (e.g. `'["1","2"]'`). Updates DOM, fires `onSelectionChange`. No server refresh. |
| `DBField` (TDBField child) | TDBField | no | **No longer required** for multi-select. Independent of the checkbox column. |
| `getValues()`, `onSelectionChange`, `SelectedCount`, `SelectedValues` | unchanged | — | Now operate on `MultiSelectDBField` values instead of `DBField` values. |

The "MultiSelect=true requires DBField" warning is **removed**. A new warning is added: "MultiSelect=true requires MultiSelectDBField; checkbox column will be skipped". The transposed-mode warning is unchanged.

## Architecture

### Render-time gating

The whole multi-select column (head `<th>`, body `<td>`, footer indicator) is gated, at every render site, on:

```php
$this->getProperty('MultiSelect', 'false') === 'true'
  && $this->getProperty('Transposed', 'false') === 'false'
  && $this->getProperty('ViewMode', 'GRID') === 'GRID'
  && $this->getPropertyComponentId('MultiSelectDBField', false) !== false
```

The previous condition `getPropertyComponentId('DBField', false) !== false` is **removed** from these gates — DBField is no longer required for the multi-select column.

### Per-row resolution

The TDBField child referenced by `MultiSelectDBField` is populated per row by the same Tholos data-provider machinery that already fills `DBField`'s Value during row iteration. At the multi-select cell render site we read that child's current Value:

```php
$msFieldId = $this->getPropertyComponentId('MultiSelectDBField');
$msValue = Tholos::$app->findComponentByID($msFieldId)->getProperty('Value', '');
$this->setProperty('MultiSelectValue', $msValue);
$this->setProperty('IsCheckable', $msValue !== '' ? 'true' : 'false');
$this->setProperty('IsSelected',
  ($msValue !== '' && in_array($msValue, $this->selectedValuesArray, true)) ? 'true' : 'false');
$columns = $this->renderPartial($this, 'multiselect.cell') . $columns;
```

`$this->selectedValuesArray` is unchanged from the merged implementation — a `string[]` of selected values. The only change is that those values now come from `MultiSelectDBField`, not `DBField`.

### Cell partial (`TGrid.partial.multiselect.cell.template`)

Switch the input's `data-value` to `$prop_multiselectvalue` and gate the entire `<input>` on `IsCheckable`. When `IsCheckable=false` the cell renders as bare `<td></td>` — naturally excluded from the existing `.TGrid-multiselect-checkbox` selectors that drive select-all, tri-state math, and footer count.

```html
<td class="TGrid-multiselect-cell">
  <%FUNC%
  _function_name=Tholos\TholosCallback::_eqs
  param=prop_ischeckable
  value=true
  true=<input type="checkbox"
         class="TGrid-multiselect-checkbox"
         data-value="$prop_multiselectvalue"
         [%_function_name=Tholos\TholosCallback::_eqs;param=prop_isselected;value=true;true=checked;false=%]
         onchange="TGrid_toggleSelection('$prop_id', $(this).data('value'));">
  false=
  %FUNC%>
</td>
```

The head partial and footer partial are unchanged.

### `init()` warnings

In `TGrid.php::init()`:

- **Remove** the existing `MultiSelect=true && no DBField` warning.
- **Add** a new warning: `MultiSelect=true requires MultiSelectDBField; checkbox column will be skipped` when `MultiSelect=true` and `getPropertyComponentId('MultiSelectDBField', false) === false`.
- **Keep** the existing transposed warning.

## JS — `TGrid_setValues` and dispatch

### Global function (`assets/js/TGrid.js`)

Add alongside the existing `TGrid_*` multiselect functions:

```javascript
function TGrid_setValues(formId_, json_) {
  var parsed;
  try { parsed = JSON.parse(json_); } catch (e) {
    console.warn('TGrid_setValues: invalid JSON, selection unchanged', json_);
    return;
  }
  if (!Array.isArray(parsed)) {
    console.warn('TGrid_setValues: payload is not an array, selection unchanged', json_);
    return;
  }
  var set = new Set(parsed.map(String));
  TGrid_setSelectionSet(formId_, set);
  $("#" + formId_ + " .TGrid-multiselect-checkbox").each(function () {
    var v = String($(this).data('value'));
    $(this).prop('checked', set.has(v));
  });
  TGrid_updateHeaderIcon(formId_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'selectionchange');
}
```

### Dispatch entry (`assets/js/TholosApplication.js`)

Add next to `TGrid_getValues` in the `Tholos.methods` object, matching the existing `(sender, target, route, eventData)` signature:

```javascript
TGrid_setValues: function (sender, target, route, eventData) {
  Tholos.trace("TGrid_setValues()", sender, target, route, eventData);
  TGrid_setValues(target, eventData);
},
```

`eventData` carries the JSON-encoded string per the spec (`'["1","2"]'`); `target` is the grid's `formId_`.

## Edge Cases & Guards

- **MultiSelectDBField not configured**: column entirely absent (no `<th>`, no `<td>`, no footer); logger warning emitted once in `init()`.
- **Per-row empty MultiSelectDBField value**: `<td>` rendered with no `<input>`. Existing select-all-visible, tri-state, and footer-count logic naturally skip it.
- **Selected value's row currently has empty MultiSelectDBField**: value stays in the selection Set but renders no input. Preserved across refreshes, not user-actionable on that page (same semantics as "selected row hidden by filter" today).
- **Duplicate MultiSelectDBField values across rows**: both inputs share the selection key; toggling either toggles both on next refresh.
- **Master change**: existing `masterDataChange` handler still clears `TGrid_Selection_` to `[]`. Unchanged. Works with or without DBField.
- **`setValues('[]')`**: empties selection; footer hides; tri-state goes to "none"; `onSelectionChange` fires.
- **`setValues` with malformed JSON**: `console.warn`, selection unchanged. Mirrors PHP-side fallback for the `TGrid_Selection_` post param.
- **`setValues` with non-string array elements** (e.g. numbers): coerced via `String(...)` to match the existing storage shape of the selection Set.
- **`setValues` called when MultiSelect column is not rendered** (no `MultiSelectDBField`, or transposed mode): the hidden input `TGrid_Selection_` doesn't exist; `TGrid_setSelectionSet` no-ops on the missing element. `console.warn` if helpful — not strictly required.

## Out of Scope

- **Server-side `setValues()` method.** Spec wording describes a JS-side method; PHP continues to read `TGrid_Selection_` from the post param on the next refresh.
- **Reactive cleanup of stale selections.** Values in the Set whose rows now have empty `MultiSelectDBField` simply remain in the Set silently. Apps that need to prune them can call `setValues` themselves from an event.
- **Migration tooling.** No grids in production rely on the post-PR-#1 behavior yet (DBField as checkbox value source), per user; no migration path needed.

## Testing (manual)

No test suite exists in this repo (per `Base/CLAUDE.md`). Verify in-browser:

1. **Configured normally** — Grid with `MultiSelect=true`, `MultiSelectDBField` pointing at a column with mixed empty/non-empty values. Checkboxes render only on rows with non-empty values; other rows show empty cells of matching width. Tri-state and footer count reflect only the checkable rows.
2. **MultiSelectDBField absent** — `MultiSelect=true` with no `MultiSelectDBField` child. No checkbox column anywhere (head/body/footer all absent). Logger shows the new warning.
3. **DBField absent** — `MultiSelect=true` with `MultiSelectDBField` but no `DBField` child. Multi-select works fully; `Selectable` highlight / `LookupValue` simply aren't present. No errors, no warnings.
4. **`setValues` via dispatch** — From a GUI event: `Tholos.methods.TGrid_setValues(sender, 'grid_id', null, '["a","b"]')` checks rows `a` and `b`, footer shows 2, tri-state updates, `onSelectionChange` fires.
5. **`setValues('[]')`** — Empties selection: all checkboxes uncheck, footer hides, tri-state goes to empty icon.
6. **`setValues` malformed JSON** — `console.warn` printed, selection unchanged.
7. **Persistence across sort/filter/page** — Unchanged from the merged behavior; selection survives refreshes.
8. **Master change** — Parent grid row click clears child grid's selection (existing handler).
9. **Transposed mode + MultiSelect=true** — Column skipped; logger warning shown (existing behavior).
10. **Selected value whose row's MultiSelectDBField is empty** — Value stays in the Set; row renders empty; no orphan checkbox.

## Files Touched (preview)

**Modified (4):**
- `src/Tholos/TGrid.php` — gate updates (drop DBField dependency, add MultiSelectDBField requirement); per-row MultiSelectDBField resolution; `init()` warning swap.
- `assets/templates/tholos/TGrid.partial.multiselect.cell.template` — switch `data-value` source, gate `<input>` on `IsCheckable`.
- `assets/js/TGrid.js` — add `TGrid_setValues` function.
- `assets/js/TholosApplication.js` — add `Tholos.methods.TGrid_setValues` dispatch entry.

**Unchanged but worth noting:**
- `TGrid.partial.multiselect.head.template`, `TGrid.partial.multiselect.footer.template`, `TGrid_Selection_` hidden input plumbing, all other `TGrid_*` multiselect JS functions, the `masterDataChange` clearing logic.

**Docs:**
- `docs/Tholos_Component_Types.md` already documents both additions (release-history dated 2026-05-11). No further edits needed.
