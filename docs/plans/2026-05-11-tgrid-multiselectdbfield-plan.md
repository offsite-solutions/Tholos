# TGrid MultiSelectDBField & setValues — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Decouple the TGrid multi-select column from `DBField`, drive checkbox value + per-row visibility from a new `MultiSelectDBField` TDBField child, and add a dispatchable `TGrid_setValues` JS method.

**Architecture:** PHP gates the entire multi-select column on `MultiSelectDBField` being configured (instead of `DBField`). At each row, PHP reads the `MultiSelectDBField` child's resolved `Value`, stores it as `MultiSelectValue` plus `IsCheckable` and `IsSelected` template props. The cell partial renders an `<input>` only when `IsCheckable=true`; otherwise the cell stays empty so `.TGrid-multiselect-checkbox` selectors naturally exclude it. JS adds a `TGrid_setValues(formId_, json_)` function plus a `Tholos.methods.TGrid_setValues` dispatch entry mirroring the existing `TGrid_getValues` shape.

**Tech Stack:** PHP 8.4 (Tholos on Eisodos), jQuery, Eisodos templates (`TholosCallback::_eqs` for conditional rendering).

**Spec:** `docs/plans/2026-05-11-tgrid-multiselectdbfield-design.md`

**Branch:** `feature/tgrid-multiselectdbfield` (already created off `main`; spec already committed as `2892755`).

**No test suite exists in this repo (per `Base/CLAUDE.md`).** Tasks therefore replace TDD with code-write + manual browser verification. A final task walks the design spec's manual test checklist.

---

## File Structure

**Modified (4):**
- `src/Tholos/TGrid.php` — `init()` warnings: drop the `DBField` requirement warning, add a `MultiSelectDBField` requirement warning. Column-head injection (two sites): swap the `DBField` gate for a `MultiSelectDBField` gate. Per-row cell injection (two sites): swap the gate, resolve `MultiSelectDBField` Value per row, set new template props (`MultiSelectValue`, `IsCheckable`, `IsSelected`).
- `assets/templates/tholos/TGrid.partial.multiselect.cell.template` — switch the `<input>` `data-value` source to `$prop_multiselectvalue` and gate the `<input>` on `$prop_ischeckable`.
- `assets/js/TGrid.js` — add `TGrid_setValues(formId_, json_)`.
- `assets/js/TholosApplication.js` — add a `Tholos.methods.TGrid_setValues` dispatch entry.

**Created:** none.

**Tasks:** 5 implementation tasks + 1 final manual verification pass.

---

## Task 1: PHP — swap `init()` warnings (drop DBField requirement, add MultiSelectDBField requirement)

**Files:**
- Modify: `src/Tholos/TGrid.php:105–113`

- [ ] **Step 1: Replace the existing DBField warning with a MultiSelectDBField warning**

The current block at `src/Tholos/TGrid.php:105–113`:

```php
      if ($this->getProperty('MultiSelect', 'false') === 'true'
        && $this->getPropertyComponentId('DBField', false) === false) {
        Tholos::$logger->warning('TGrid MultiSelect=true requires a DBField; checkbox column will be skipped', $this);
      }

      if ($this->getProperty('MultiSelect', 'false') === 'true'
        && $this->getProperty('Transposed', 'false') === 'true') {
        Tholos::$logger->warning('TGrid MultiSelect is not supported in transposed mode; checkbox column will be skipped', $this);
      }
```

becomes:

```php
      if ($this->getProperty('MultiSelect', 'false') === 'true'
        && $this->getPropertyComponentId('MultiSelectDBField', false) === false) {
        Tholos::$logger->warning('TGrid MultiSelect=true requires a MultiSelectDBField; checkbox column will be skipped', $this);
      }

      if ($this->getProperty('MultiSelect', 'false') === 'true'
        && $this->getProperty('Transposed', 'false') === 'true') {
        Tholos::$logger->warning('TGrid MultiSelect is not supported in transposed mode; checkbox column will be skipped', $this);
      }
```

(Only the first warning is changed — DBField → MultiSelectDBField in both the gate condition and the message. The transposed warning is unchanged.)

- [ ] **Step 2: Manual sanity check (no browser yet)**

Confirm the file still parses:

```bash
php -l src/Tholos/TGrid.php
```

Expected: `No syntax errors detected in src/Tholos/TGrid.php`.

- [ ] **Step 3: Commit**

```bash
git add src/Tholos/TGrid.php
git commit -m "refactor(TGrid): replace MultiSelect DBField warning with MultiSelectDBField warning"
```

---

## Task 2: PHP — swap column-head gating (two sites) to require `MultiSelectDBField`

**Files:**
- Modify: `src/Tholos/TGrid.php:763–767` (standalone-column branch)
- Modify: `src/Tholos/TGrid.php:797–802` (TGridRow-grouped branch)

- [ ] **Step 1: Update the standalone-column head injection**

The current block at `src/Tholos/TGrid.php:762–769`:

```php
      if (!$transposed && $hasAnyStandaloneGridColumn) {
        if ($this->getProperty('MultiSelect', 'false') === 'true'
          && $this->getProperty('ViewMode', 'GRID') === 'GRID'
          && $this->getPropertyComponentId('DBField', false) !== false) {
          $items = array_merge(['__MultiSelectHeader' => $this->renderPartial($this, 'multiselect.head')], $items);
        }
        $this->columnHeadItems .= $this->renderPartial($this, 'headitems', implode($items));
      }
```

becomes:

```php
      if (!$transposed && $hasAnyStandaloneGridColumn) {
        if ($this->getProperty('MultiSelect', 'false') === 'true'
          && $this->getProperty('ViewMode', 'GRID') === 'GRID'
          && $this->getPropertyComponentId('MultiSelectDBField', false) !== false) {
          $items = array_merge(['__MultiSelectHeader' => $this->renderPartial($this, 'multiselect.head')], $items);
        }
        $this->columnHeadItems .= $this->renderPartial($this, 'headitems', implode($items));
      }
```

(Only the third condition changes: `DBField` → `MultiSelectDBField`.)

- [ ] **Step 2: Update the TGridRow-grouped head injection**

The current block at `src/Tholos/TGrid.php:796–804`:

```php
        if (!$transposed && count($items) > 0 && Tholos::$app->findComponentByID($rowID)->getProperty('ShowColumnHead', '') == 'true') {
          if (!$hasAnyStandaloneGridColumn
            && $this->getProperty('MultiSelect', 'false') === 'true'
            && $this->getProperty('ViewMode', 'GRID') === 'GRID'
            && $this->getPropertyComponentId('DBField', false) !== false) {
            $items = array_merge(['__MultiSelectHeader' => $this->renderPartial($this, 'multiselect.head')], $items);
          }
          $this->columnHeadItems .= $this->renderPartial($this, 'headitems', implode($items));
        }
```

becomes:

```php
        if (!$transposed && count($items) > 0 && Tholos::$app->findComponentByID($rowID)->getProperty('ShowColumnHead', '') == 'true') {
          if (!$hasAnyStandaloneGridColumn
            && $this->getProperty('MultiSelect', 'false') === 'true'
            && $this->getProperty('ViewMode', 'GRID') === 'GRID'
            && $this->getPropertyComponentId('MultiSelectDBField', false) !== false) {
            $items = array_merge(['__MultiSelectHeader' => $this->renderPartial($this, 'multiselect.head')], $items);
          }
          $this->columnHeadItems .= $this->renderPartial($this, 'headitems', implode($items));
        }
```

(Same single-line change: `DBField` → `MultiSelectDBField`.)

- [ ] **Step 3: Syntax check**

```bash
php -l src/Tholos/TGrid.php
```

Expected: `No syntax errors detected in src/Tholos/TGrid.php`.

- [ ] **Step 4: Commit**

```bash
git add src/Tholos/TGrid.php
git commit -m "refactor(TGrid): gate multiselect head column on MultiSelectDBField, not DBField"
```

---

## Task 3: PHP — swap per-row cell gating (two sites) and resolve `MultiSelectDBField` Value per row

**Files:**
- Modify: `src/Tholos/TGrid.php:1592–1605` (standalone-column row branch)
- Modify: `src/Tholos/TGrid.php:1645–1663` (TGridRow-grouped row branch)

- [ ] **Step 1: Update the standalone-column cell injection**

The current block at `src/Tholos/TGrid.php:1592–1605`:

```php
              if ($columns !== '') {
                if ($this->getPropertyComponentId('DBField') !== false) {
                  $columns = $this->renderPartial($this, 'selectable') . $columns;
                }
                if ($this->getProperty('MultiSelect', 'false') === 'true'
                  && $this->getProperty('ViewMode', 'GRID') === 'GRID'
                  && $this->getPropertyComponentId('DBField', false) !== false) {
                  $rowValue = $this->getProperty('Value', '');
                  $this->setProperty('IsSelected', in_array($rowValue, $this->selectedValuesArray, true) ? 'true' : 'false');
                  $columns = $this->renderPartial($this, 'multiselect.cell') . $columns;
                }
                $result .= $this->renderPartial($this, 'row', $columns) . "\n";
                $hasAnyStandaloneGridColumn = true;
              }
```

becomes:

```php
              if ($columns !== '') {
                if ($this->getPropertyComponentId('DBField') !== false) {
                  $columns = $this->renderPartial($this, 'selectable') . $columns;
                }
                if ($this->getProperty('MultiSelect', 'false') === 'true'
                  && $this->getProperty('ViewMode', 'GRID') === 'GRID'
                  && $this->getPropertyComponentId('MultiSelectDBField', false) !== false) {
                  $msFieldId = $this->getPropertyComponentId('MultiSelectDBField');
                  $msValue = Tholos::$app->findComponentByID($msFieldId)->getProperty('Value', '');
                  $this->setProperty('MultiSelectValue', $msValue);
                  $this->setProperty('IsCheckable', $msValue !== '' ? 'true' : 'false');
                  $this->setProperty('IsSelected',
                    ($msValue !== '' && in_array($msValue, $this->selectedValuesArray, true)) ? 'true' : 'false');
                  $columns = $this->renderPartial($this, 'multiselect.cell') . $columns;
                }
                $result .= $this->renderPartial($this, 'row', $columns) . "\n";
                $hasAnyStandaloneGridColumn = true;
              }
```

Changes:
1. Gate condition: `DBField` → `MultiSelectDBField`.
2. The `$rowValue = $this->getProperty('Value', '')` line is replaced by reading the `MultiSelectDBField` child's `Value` property directly (the data-provider machinery already populates that child's `Value` for the current row, identically to how `DBField`'s is populated and was previously read via `$this->getProperty('Value', '')`).
3. Three new template props are set: `MultiSelectValue`, `IsCheckable`, `IsSelected`. `IsSelected` is now guarded against false-positives on empty values (`'' in_array …` would otherwise match any empty stored value).

- [ ] **Step 2: Update the TGridRow-grouped cell injection**

The current block at `src/Tholos/TGrid.php:1645–1663`:

```php
                if (!$isEmptyRow || Tholos::$app->findComponentByID($rowID)->getProperty('HideWhenEmpty', 'false') === 'false') {
                  if ($this->getPropertyComponentId('DBField') !== false) {
                    if ($hasAnyStandaloneGridColumn) {
                      $columns = $this->renderPartial($this, 'noselectable') . $columns;
                    } else {
                      $columns = $this->renderPartial($this, 'selectable') . $columns;
                    }
                  }
                  if ($this->getProperty('MultiSelect', 'false') === 'true'
                    && $this->getProperty('ViewMode', 'GRID') === 'GRID'
                    && $this->getPropertyComponentId('DBField', false) !== false
                    && !$hasAnyStandaloneGridColumn) {
                    $rowValue = $this->getProperty('Value', '');
                    $this->setProperty('IsSelected', in_array($rowValue, $this->selectedValuesArray, true) ? 'true' : 'false');
                    $columns = $this->renderPartial($this, 'multiselect.cell') . $columns;
                  }
                  $result .= Tholos::$app->findComponentByID($rowID)->render($this, $columns) . "\n";
                }
```

becomes:

```php
                if (!$isEmptyRow || Tholos::$app->findComponentByID($rowID)->getProperty('HideWhenEmpty', 'false') === 'false') {
                  if ($this->getPropertyComponentId('DBField') !== false) {
                    if ($hasAnyStandaloneGridColumn) {
                      $columns = $this->renderPartial($this, 'noselectable') . $columns;
                    } else {
                      $columns = $this->renderPartial($this, 'selectable') . $columns;
                    }
                  }
                  if ($this->getProperty('MultiSelect', 'false') === 'true'
                    && $this->getProperty('ViewMode', 'GRID') === 'GRID'
                    && $this->getPropertyComponentId('MultiSelectDBField', false) !== false
                    && !$hasAnyStandaloneGridColumn) {
                    $msFieldId = $this->getPropertyComponentId('MultiSelectDBField');
                    $msValue = Tholos::$app->findComponentByID($msFieldId)->getProperty('Value', '');
                    $this->setProperty('MultiSelectValue', $msValue);
                    $this->setProperty('IsCheckable', $msValue !== '' ? 'true' : 'false');
                    $this->setProperty('IsSelected',
                      ($msValue !== '' && in_array($msValue, $this->selectedValuesArray, true)) ? 'true' : 'false');
                    $columns = $this->renderPartial($this, 'multiselect.cell') . $columns;
                  }
                  $result .= Tholos::$app->findComponentByID($rowID)->render($this, $columns) . "\n";
                }
```

Same shape of change as Step 1: gate switched to `MultiSelectDBField`, per-row resolution reads the child's `Value`, three template props set, `IsSelected` guarded against empty values.

- [ ] **Step 3: Syntax check**

```bash
php -l src/Tholos/TGrid.php
```

Expected: `No syntax errors detected in src/Tholos/TGrid.php`.

- [ ] **Step 4: Commit**

```bash
git add src/Tholos/TGrid.php
git commit -m "feat(TGrid): resolve MultiSelectDBField per row and gate cell injection on it"
```

---

## Task 4: Template — switch cell partial to `$prop_multiselectvalue` and gate input on `IsCheckable`

**Files:**
- Modify: `assets/templates/tholos/TGrid.partial.multiselect.cell.template` (whole file)

- [ ] **Step 1: Rewrite the cell partial**

The current file:

```html
<td class="TGrid-multiselect-cell">
  <input type="checkbox"
         class="TGrid-multiselect-checkbox"
         data-value="$prop_value"
         <%FUNC%
         _function_name=Tholos\TholosCallback::_eqs
         param=prop_isselected
         value=true
         true=checked
         false=
         %FUNC%>
         onchange="TGrid_toggleSelection('$prop_id', $(this).data('value'));">
</td>
```

becomes:

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

Two changes:
1. The `<input>` is now wrapped in a `_eqs(prop_ischeckable, true)` block — when `IsCheckable=false`, the cell renders as bare `<td></td>`.
2. `data-value` reads `$prop_multiselectvalue` (set by PHP per row in Task 3) instead of `$prop_value` (which is the `DBField` row value).

The nested `_eqs` callback that emits `checked` is rewritten using the inline `[%...%]` form so it sits cleanly inside the outer block's `true=...` branch.

- [ ] **Step 2: Manual verification (browser)**

Reload a `MultiSelect=true` grid (configured with `MultiSelectDBField` — Task 5 will hook up the dispatch; rendering works after this task).

Confirm in DevTools:
- Rows where the `MultiSelectDBField` column value is non-empty render `<input class="TGrid-multiselect-checkbox" data-value="...">` with the expected per-row value.
- Rows where the value is empty render `<td class="TGrid-multiselect-cell"></td>` with no `<input>`.
- Column width / alignment is preserved across both row types.

- [ ] **Step 3: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.multiselect.cell.template
git commit -m "feat(TGrid): switch multiselect cell to MultiSelectValue + IsCheckable gate"
```

---

## Task 5: JS — add `TGrid_setValues` function and dispatch entry

**Files:**
- Modify: `assets/js/TGrid.js` (append a new function after `TGrid_updateHeaderIcon` at line 689, before `$(document).ready(...)` at line 691)
- Modify: `assets/js/TholosApplication.js:127–130` (add dispatch entry next to `TGrid_getValues`)

- [ ] **Step 1: Append `TGrid_setValues` to `assets/js/TGrid.js`**

Open `assets/js/TGrid.js`. After the closing `}` of `TGrid_updateHeaderIcon` (line 689) and before `$(document).ready(...)` (line 691), insert:

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

This mirrors the shape of `TGrid_clearSelection` and `TGrid_toggleSelection` — it writes the hidden input via `TGrid_setSelectionSet`, reconciles visible checkboxes against the new Set, refreshes the tri-state header icon, and fires `onSelectionChange`. No server refresh (per spec).

- [ ] **Step 2: Add the dispatch entry to `assets/js/TholosApplication.js`**

Find the existing `TGrid_getValues` dispatch entry at line 127–130:

```javascript
    TGrid_getValues: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_getValues()", sender, target, route, eventData);
      return JSON.stringify(TGrid_getValues(target));
    },
```

Immediately after the closing `},` (line 130) insert:

```javascript
    TGrid_setValues: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_setValues()", sender, target, route, eventData);
      TGrid_setValues(target, eventData);
    },
```

The signature matches the existing `Tholos.methods.*` convention: `(sender, target, route, eventData)`. `target` is the grid's `formId_`; `eventData` carries the JSON-encoded array string (`'["1","2"]'`). The dispatch entry returns `undefined` (no value to surface to the GUI caller).

- [ ] **Step 3: Manual verification (browser console)**

Reload a `MultiSelect=true` grid with `MultiSelectDBField` configured. In DevTools console:

```javascript
TGrid_getValues('<grid_id>')
// → []  (initial)

Tholos.methods.TGrid_setValues(null, '<grid_id>', null, '["1","2"]')
// → checkboxes for rows with MultiSelectDBField value "1" or "2" tick
// → footer shows "Kiválasztva: 2"
// → tri-state icon updates
// → onSelectionChange fires (visible in any registered handler)

Tholos.methods.TGrid_setValues(null, '<grid_id>', null, '[]')
// → all checkboxes uncheck
// → footer hides

Tholos.methods.TGrid_setValues(null, '<grid_id>', null, 'not json')
// → console warning, selection unchanged
```

- [ ] **Step 4: Commit**

```bash
git add assets/js/TGrid.js assets/js/TholosApplication.js
git commit -m "feat(TGrid): add TGrid_setValues function and Tholos.methods dispatch entry"
```

---

## Task 6: End-to-end manual verification pass

**Files:** none (read-only)

Walk the design spec's manual test checklist (`docs/plans/2026-05-11-tgrid-multiselectdbfield-design.md` § Testing). Each item below is independent — fix any failure with a follow-up commit before continuing.

- [ ] **Step 1: Configured normally — mixed empty/non-empty `MultiSelectDBField`**

Grid with `MultiSelect=true` and `MultiSelectDBField` pointing at a column that has a mix of empty and non-empty values. Confirm:
- Checkboxes render only on rows with non-empty values.
- Other rows show empty cells of matching column width.
- Tri-state icon and footer count reflect only the checkable rows.

- [ ] **Step 2: `MultiSelectDBField` absent**

Grid with `MultiSelect=true` but no `MultiSelectDBField` child. Confirm:
- No `<th class="TGrid-multiselect-head">` in the header.
- No `<td class="TGrid-multiselect-cell">` in any row.
- No multiselect footer indicator.
- `Tholos::$logger->warning` for "MultiSelect=true requires a MultiSelectDBField; checkbox column will be skipped" is recorded once.

- [ ] **Step 3: `DBField` absent (MultiSelectDBField present)**

Grid with `MultiSelect=true` and `MultiSelectDBField` but no `DBField`. Confirm:
- Multi-select column renders fully.
- No `<td class="TGrid-selectable">` cell (since the `selectable` partial is still gated on `DBField`).
- No errors or warnings other than what's expected.

- [ ] **Step 4: `setValues` via dispatch**

From DevTools console:

```javascript
Tholos.methods.TGrid_setValues(null, '<grid_id>', null, '["a","b"]')
```

Rows with `MultiSelectDBField` value `a` or `b` tick, footer shows `2`, tri-state updates, `onSelectionChange` fires (verify via any wired handler or a temporary `$('#<grid_id>').on('onSelectionChange', console.log)`).

- [ ] **Step 5: `setValues('[]')`**

```javascript
Tholos.methods.TGrid_setValues(null, '<grid_id>', null, '[]')
```

All checkboxes uncheck, footer hides, tri-state goes to empty icon.

- [ ] **Step 6: `setValues` malformed JSON**

```javascript
Tholos.methods.TGrid_setValues(null, '<grid_id>', null, 'oops')
```

DevTools console shows the `TGrid_setValues: invalid JSON, selection unchanged` warning; selection state is identical to before the call.

- [ ] **Step 7: `setValues` non-array payload**

```javascript
Tholos.methods.TGrid_setValues(null, '<grid_id>', null, '{"a":1}')
```

DevTools console shows `TGrid_setValues: payload is not an array, selection unchanged`; selection unchanged.

- [ ] **Step 8: Persistence across sort / filter / paging**

After a `setValues(["a","b"])`: click a column header to sort, change a filter, switch page — checkboxes for selected values remain checked at their new positions, and rows with selected values whose `MultiSelectDBField` becomes empty after the refresh render no `<input>` but the value stays in the Set (verify via `TGrid_getValues('<grid_id>')`).

- [ ] **Step 9: Master change**

Parent grid changes selected row → child grid's selection clears (footer hidden, all checkboxes unchecked). This validates the existing `masterDataChange` handler still clears `TGrid_Selection_` correctly with the new gate path.

- [ ] **Step 10: Transposed mode + `MultiSelect=true`**

Set `Transposed=true` on a `MultiSelect=true` grid. Column absent; logger warning shown (existing behavior, unaffected by this change).

- [ ] **Step 11: Selected value whose row has empty `MultiSelectDBField`**

Manually craft a state where the selection Set contains a value, but on the current page that row's `MultiSelectDBField` resolves to empty (e.g. via filter or paging). Confirm:
- The row renders with no `<input>`.
- `TGrid_getValues('<grid_id>')` still includes the value.
- The tri-state icon and footer count operate on the visible checkable rows only.

- [ ] **Step 12: Final commit / push**

If any fixes were committed during this task, push the branch:

```bash
git push -u origin feature/tgrid-multiselectdbfield
```

(Skip `git push` if the user has not asked for it — local branch + commits are sufficient until they request a PR.)

---

## Self-Review Checklist (run before declaring plan ready)

1. **Spec coverage** — every contract item, edge case, and manual test in `docs/plans/2026-05-11-tgrid-multiselectdbfield-design.md` is implemented in some task above. ✔
2. **Placeholders** — no "TBD"/"TODO"/"add error handling"/etc. ✔
3. **Type/name consistency** — `MultiSelectValue`, `IsCheckable`, `IsSelected` (PHP template props ↔ `$prop_multiselectvalue` / `$prop_ischeckable` / `$prop_isselected` in the template — lowercased per Eisodos convention) all match across tasks. `TGrid_setValues` (global JS function) and `Tholos.methods.TGrid_setValues` (dispatch entry) consistently named. ✔
4. **File paths** — all paths exist (modified files) or are explicitly marked as new. None are new in this plan; all four touched files exist in the repo. ✔
