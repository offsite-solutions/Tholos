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
| `LabelSelected` (property) | STRING | `[:GRID.SELECTED,Kiválasztva: %s:]` | Translatable footer label; `%s` is replaced with the count. |
| `onSelectionChange` (event, GUI) | — | — | Fires after every checkbox toggle, select-all-visible, or clear-all action. |
| `getValues()` (method, JS) | returns `string[]` | — | Returns the parsed selection array. `getValue()` is unchanged and still returns `LookupValue`. |

`Selectable`, `LookupValue`, `Value`, and the existing `onChange` event are not modified — single-row "current row" semantics are preserved so a multi-select grid can still drive child master/detail grids.

## Architecture

### State of truth

JS owns a `Set<string>` per grid as the runtime source of truth, mirrored into a hidden form input `TGrid_Selection_<id>` inside the existing `helper_<id>` form. PHP reads that input on every AJAX render and emits `checked` attributes on matching rows during the render pass, so the new HTML arrives already-checked (no flicker).

### Per-action sequence

1. User toggles a body checkbox / clicks header tri-state / clicks clear-all.
2. JS updates the Set, writes `JSON.stringify([...set])` into `TGrid_Selection_`, refreshes footer indicator, refreshes header tri-state icon, fires `onSelectionChange`.
3. (Sort / filter / page / refresh) Helper form submits with the up-to-date hidden input.
4. PHP parses `SelectedValues` from the post param, clears it if `TGrid_MasterValueChanged_=T` (existing flag, see TGrid.php:1355), and exposes `IsSelected` per row to the row template.
5. After HTML swap, JS re-reads the hidden input and rebuilds the Set (the in-memory state is now redundant but matches the wire), then re-runs `updateHeaderIcon` and `updateFooter`.

### Why this approach

It mirrors the existing `LookupValue` / `TGrid_Value_` pattern that already drives single-row selection, so:
- Master change clearing reuses the existing `TGrid_MasterValueChanged_` plumbing.
- AJAX refresh logic is unchanged — the existing helper form simply carries one extra field on the same request; no new endpoint, no new partial route.
- Server-rendered `checked` removes flicker that a pure-client approach would have.

Trade-off: the selection array travels in every refresh request. Acceptable: typical TGrid use is for human-scale selections (tens, occasionally hundreds). If that grows past a few KB in a real deployment, we can switch to a session-keyed delta protocol later without changing the public contract.

## Server-Side (PHP)

**File: `src/Tholos/TGrid.php`**

- In the existing pre-render block (around the `LookupValue` master-change handling at line 1355), read `TGrid_Selection_<id>` from the parameter handler into the `SelectedValues` property. Clear it (set to `[]`) when `TGrid_MasterValueChanged_=T`.
- Decode `SelectedValues` once into a PHP array; keep it on the TGrid instance for fast per-row lookup.
- During row iteration (in the row-rendering path that already emits `selectionoutoflist` and per-row template vars), set a per-row `IsSelected` template var: `IsSelected = in_array($rowDBFieldValue, $selectedValuesArray, true) ? 'true' : 'false'`.
- When `MultiSelect=true`, expose one extra template var at table foot: `SelectedCount` (size of the parsed array). The tri-state header icon is rendered as a static initial state (`fa fa-square-o text-muted`) — JS recomputes the correct state on `init` after HTML swap. Keeping this single-owner avoids server/client divergence on what counts as "all visible".

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

New module section under `tgrid` (object literal):

```
tgrid.multiSelect = {
  init(gridId)                  // wire DOM listeners, rebuild Set from hidden input
  toggle(gridId, value)         // single-row checkbox toggle
  selectVisible(gridId)         // header icon → check all visible rows
  deselectVisible(gridId)       // header icon → uncheck all visible rows
  clearAll(gridId)              // footer button → empty Set, refresh grid
  isSelected(gridId, value)     // helper for tests/console
  getValues(gridId)             // returns Array.from(set)
  updateHeaderIcon(gridId)      // recompute & swap fa-square-o / fa-minus-square / fa-check-square
  updateFooter(gridId)          // write "N selected" + show/hide clear button
  fireSelectionChange(gridId)   // invoke configured onSelectionChange handler
}
```

Hooked into the existing AJAX-refresh callback so `init` runs after every HTML swap.

`getValues(gridId)` is exposed at the grid-instance level so user JS can call `tgrid_<id>.getValues()` symmetrically with the existing `getValue()`.

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

- Inline span next to existing row-count: `[:GRID.SELECTED,Kiválasztva: %s:]` (translatable via `LabelSelected`).
- Clear-all button: icon-only `fa fa-times-circle text-danger`, `title="Clear selection"`, hidden when count is `0`.
- Clicking clear-all empties the Set, writes `[]` into the hidden input, fires `onSelectionChange`, then triggers a grid refresh so server-side state matches.

## Edge Cases & Guards

- **No `DBField` on grid**: log a warning via `Tholos::$logger->warn` once at render. Render the column but checkboxes are `disabled`. App still loads.
- **Transposed mode (`Transposed=true`)**: skip the multiselect column rendering entirely; `Tholos::$logger->warn` once. Selection state is preserved (kept in the hidden input) but not user-actionable.
- **Chart view (`ViewMode=CHART`)**: skip rendering the column; selection state preserved.
- **Duplicate DBField values across rows**: by spec, DBField values identify rows. If duplicates exist, both checkboxes for that value share state — toggling either toggles both on next refresh.
- **Master change**: existing `TGrid_MasterValueChanged_=T` flag clears `LookupValue` (TGrid.php:1355–1359). We piggyback there and also clear `SelectedValues` to `[]`.

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
11. **`getValues()`**: from browser console, `tgrid_<id>.getValues()` returns expected array of strings.
12. **No DBField fallback**: define a TGrid with `MultiSelect=true` and no `DBField` → checkboxes render disabled; logger shows warning.
