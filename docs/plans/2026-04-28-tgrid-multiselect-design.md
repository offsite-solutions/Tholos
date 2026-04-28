# TGrid MultiSelect — Design Spec

**Date:** 2026-04-28
**Branch:** `feature/tgrid-multiselect`
**Status:** Approved (brainstorming → ready for implementation plan)

## Goal

Let TGrid present a checkbox column when `MultiSelect=true`. The user selects/deselects individual rows, all visible rows, or clears the entire selection. Selected values persist across sort/filter/refresh/paging within a single master scope, and clear automatically when the grid's master value changes. Selection state is exposed to PHP and JS without disturbing the existing single-row `Selectable` + `LookupValue` master/detail mechanism.

## Public Contract

| Surface | Type | Default | Purpose |
|---|---|---|---|
| `MultiSelect` (property) | BOOLEAN | `false` | Master switch. When `true`, render the checkbox column, footer indicator, and clear button. |
| `SelectedValues` (property, runtime, hidden data) | TEXT | `[]` (the literal two-character JSON empty array) | JSON array string of currently selected `DBField` values. Server- and client-readable; round-trips on every AJAX refresh. |
| `SelectedCount` (property, runtime, hidden data) | NUMBER | `0` | Size of `SelectedValues` parsed array; exposed for templates as `$prop_selectedcount`. |
| `onSelectionChange` (event, GUI) | — | — | Fires after every checkbox toggle, select-all-visible, or clear-all action. |
| `getValues()` (method, JS) | returns `string[]` | — | Returns the parsed selection array. `getValue()` is unchanged and still returns `LookupValue`. |

The footer's "Kiválasztva: N" label is rendered directly in the multiselect-footer template via the existing language-tag mechanism: `[:GRID.SELECTED,Kiválasztva:]: $prop_selectedcount`. No new label property is needed — translations live in the language-tag store like every other GRID.* tag.

`Selectable`, `LookupValue`, `Value`, and the existing `onChange` event are not modified — single-row "current row" semantics are preserved so a multi-select grid can still drive child master/detail grids.

## Architecture

### State of truth

JS owns a `Set<string>` per grid as the runtime source of truth, mirrored into a hidden form input `TGrid_Selection_<id>` inside the existing `helper_<id>` form. PHP reads that input on every AJAX render and emits `checked` attributes on matching rows during the render pass, so the new HTML arrives already-checked (no flicker).

### Per-action sequence

1. User toggles a body checkbox / clicks header tri-state / clicks clear-all.
2. JS updates the Set, writes `JSON.stringify([...set])` into `TGrid_Selection_`, refreshes footer indicator, refreshes header tri-state icon, fires `onSelectionChange`.
3. (Sort / filter / page / refresh) Helper form submits with the up-to-date hidden input.
4. PHP parses `SelectedValues` from the post param and exposes `IsSelected` per row to the row template. Master-change clearing happens client-side (see step in `TGrid_ready` wiring below), so the post arrives empty when a master change just fired.
5. After HTML swap, `TGrid_ready(formId_)` re-reads the hidden input, re-binds checkbox handlers, and runs `TGrid_updateHeaderIcon` + `TGrid_updateFooter`.

### Why this approach

It mirrors the existing `LookupValue` / `TGrid_Value_` pattern that already drives single-row selection, so:
- Master change clearing piggybacks on the existing `masterDataChange` jQuery handler in `TGrid_ready` — same place that already wipes `TGrid_Value_`.
- AJAX refresh logic is unchanged — the existing helper form simply carries one extra field on the same request; no new endpoint, no new partial route.
- Server-rendered `checked` removes flicker that a pure-client approach would have.

Trade-off: the selection array travels in every refresh request. Acceptable: typical TGrid use is for human-scale selections (tens, occasionally hundreds). If that grows past a few KB in a real deployment, we can switch to a session-keyed delta protocol later without changing the public contract.

## Server-Side (PHP)

**File: `src/Tholos/TGrid.php`**

- In the existing pre-render block (around the `LookupValue` master-change handling at line 1355), read `TGrid_Selection_<id>` from the parameter handler into the `SelectedValues` property. No server-side master-clear is needed — the client clears `TGrid_Selection_` in the existing `masterDataChange` handler (TGrid.js:582) before the refresh fires, so the request arrives with an already-empty array.
- Decode `SelectedValues` once into a PHP array; keep it on the TGrid instance for fast per-row lookup.
- During row iteration (in the row-rendering path that already emits `selectionoutoflist` and per-row template vars), set a per-row `IsSelected` template var: `IsSelected = in_array($rowDBFieldValue, $selectedValuesArray, true) ? 'true' : 'false'`.
- Set the `SelectedCount` runtime property to `count($selectedValuesArray)` so the footer template can read it as `$prop_selectedcount`.
- The tri-state header icon is rendered as a static initial state (`fa fa-square-o text-muted`) — JS recomputes the correct state in `TGrid_ready` after every HTML swap. Keeping this single-owner avoids server/client divergence on what counts as "all visible".

**Templates (new):**
- `assets/templates/tholos/TGrid.partial.multiselect.head.template` — renders the `<th>` containing the tri-state header icon.
- `assets/templates/tholos/TGrid.partial.multiselect.cell.template` — renders the `<td>` with the body checkbox; reads `IsSelected` and the row's DBField value.
- `assets/templates/tholos/TGrid.partial.multiselect.footer.template` — renders the "N selected" indicator + clear button next to the existing row-count text.

**Templates (edited):**
- `TGrid.partial.table.head.template` and `TGrid.partial.div.head.template` — conditionally include the multiselect head partial as the first column when `MultiSelect=true`.
- `TGrid.partial.row.template` — conditionally prepend the multiselect cell partial.
- `TGrid.partial.foot.template` (and the `divresponsive` foot variant) — conditionally include the multiselect footer partial near the existing `n sor/rows` label.

The checkbox column is rendered inline at the template level rather than as a real `TGridColumn`. This keeps it out of column iteration, sorting, and Excel export, and means the column doesn't need a corresponding entry in the user's grid definition.

## Client-Side (TGrid.js)

Add new flat top-level functions following the existing `TGrid_*` naming pattern (no module / object literal). All operate on `formId_` and read/write the `Set<string>` of selections via the hidden input `#helper_<formId_> #TGrid_Selection_`:

```
TGrid_toggleSelection(formId_, value_)        // single-row checkbox toggle
TGrid_selectVisible(formId_)                  // header tri-state → check all visible rows
TGrid_deselectVisible(formId_)                // header tri-state → uncheck all visible rows
TGrid_clearSelection(formId_)                 // footer clear button → empty array, refresh grid
TGrid_getValues(formId_)                      // returns parsed JS array
TGrid_isSelected(formId_, value_)             // helper used by handlers and console
TGrid_updateHeaderIcon(formId_)               // recompute fa-square-o / fa-minus-square / fa-check-square
TGrid_updateFooter(formId_)                   // write count into the multiselect footer span + toggle clear button visibility
```

`onSelectionChange` is invoked through the standard Tholos event handler the same way the existing TGrid events are fired (e.g., `Tholos.eventHandler(formId_, formId_, 'TGrid', 'selectionchange')`) — no helper wrapper needed.

### Wiring into `TGrid_ready(formId_)`

`TGrid_ready` (TGrid.js:568) is the existing per-grid init hook that runs after every HTML swap. The multiselect bootstrap goes there:

1. After existing logic, if `MultiSelect=true`:
   - Read the `TGrid_Selection_` hidden input, treat as the post-render source of truth.
   - Bind body-checkbox `change` handlers → `TGrid_toggleSelection`.
   - Bind header tri-state icon `click` → `TGrid_selectVisible` / `TGrid_deselectVisible` (depending on current state).
   - Bind footer clear button `click` → `TGrid_clearSelection`.
   - Call `TGrid_updateHeaderIcon(formId_)` and `TGrid_updateFooter(formId_)` for initial state.
2. In the existing `masterDataChange` handler (TGrid.js:582), alongside the line that clears `TGrid_Value_` (line 585), also clear `TGrid_Selection_` (write `[]`) so master change wipes both single and multi selection in one place — no separate `MasterValueChanged_` handling needed in the multiselect functions.

`TGrid_getValues(formId_)` mirrors the existing `TGrid_*` access pattern and serves as the JS-callable equivalent to PHP `getValues()`.

## UI Details

### Header column

- Width: `36px` fixed.
- Content: Font Awesome 5 tri-state icon, click-toggleable.
  - **None visible selected** → `fa fa-square-o text-muted`.
  - **Some visible selected** → `fa fa-minus-square text-warning`.
  - **All visible selected** → `fa fa-check-square text-success`.
- Click handler: if state is "all" → `deselectVisible`; otherwise → `selectVisible`.

### Body cells

- Single `<input type="checkbox" data-value="<row DBField value>">` centered in the cell.
- `checked` attribute emitted by PHP based on `IsSelected`.

### Footer

- Inline span next to existing row-count, rendered in the multiselect-footer template as `[:GRID.SELECTED,Kiválasztva:]: $prop_selectedcount`. Translation lives in the language-tag store; `$prop_selectedcount` is the runtime `SelectedCount` property.
- Clear-all button: icon-only `fa fa-times-circle text-danger`, `title="Clear selection"`, hidden when `SelectedCount = 0`.
- Clicking clear-all writes `[]` into `TGrid_Selection_`, fires `onSelectionChange`, then triggers a grid refresh so server-rendered `checked` state matches.

## Edge Cases & Guards

- **No `DBField` on grid**: log a warning via `Tholos::$logger->warn` once at render. Render the column but checkboxes are `disabled`. App still loads.
- **Transposed mode (`Transposed=true`)**: skip the multiselect column rendering entirely; `Tholos::$logger->warn` once. Selection state is preserved (kept in the hidden input) but not user-actionable.
- **Chart view (`ViewMode=CHART`)**: skip rendering the column; selection state preserved.
- **Duplicate DBField values across rows**: by spec, DBField values identify rows. If duplicates exist, both checkboxes for that value share state — toggling either toggles both on next refresh.
- **Master change**: handled client-side. The existing `masterDataChange` jQuery handler in `TGrid_ready` (TGrid.js:582–590) already wipes `TGrid_Value_` on master change; we extend the same handler to also wipe `TGrid_Selection_` so the next refresh request posts an empty array.

## Out of Scope

- Excel export of selected-only rows. Existing `downloadExcel()` exports the current filter/page result as before. Selection is a UI-only concept for v1.
- Per-row enable/disable rules for the checkbox (e.g., "can't select archived rows"). All visible rows are selectable when `MultiSelect=true`.
- Bulk-action toolbar buttons that operate on the selection. Apps wire those themselves via `onSelectionChange` + `getValues()`.
- Persisting selection across full-page reloads or across sessions. Selection lifetime is "until master changes or page reload".

## Testing (manual)

No test suite exists in this repo (per CLAUDE.md). Verify in-browser:

1. **Toggle**: click a body checkbox → footer count updates → `onSelectionChange` fires.
2. **Persist on refresh**: select rows → click refresh → still selected.
3. **Persist on sort**: select rows → click a column header → still selected.
4. **Persist on filter change**: select rows → change a filter that excludes some selected rows → still selected (rows hidden but in array).
5. **Persist on paging**: select on page 1 → go to page 3 → select more → return to page 1 → both still selected.
6. **Tri-state header**:
   - 0 of N visible selected → empty icon.
   - 1..N-1 of N visible selected → minus icon.
   - N of N visible selected → check icon.
7. **Header click**: when not all visible selected, clicking checks all visible; when all visible selected, clicking unchecks them.
8. **Clear button**: appears only when count > 0; click empties the array and refreshes the grid.
9. **Master change**: parent grid changes selected row → child grid's selection clears (footer shows 0, all checkboxes empty).
10. **Coexistence with `Selectable`**: single-row click still highlights the "current" row and updates `LookupValue`; checkbox column is independent.
11. **`getValues()`**: from browser console, `TGrid_getValues('<formId_>')` returns expected array of strings.
12. **No DBField fallback**: define a TGrid with `MultiSelect=true` and no `DBField` → checkboxes render disabled; logger shows warning.
