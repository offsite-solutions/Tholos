# TGrid MultiSelect Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a checkbox column to TGrid (driven by `MultiSelect=true`) with persistent multi-row selection, tri-state header icon, footer indicator + clear button, and `onSelectionChange` event — without breaking the existing single-row `Selectable` / master-detail flow.

**Architecture:** Selection lives client-side as a `Set<string>`, mirrored into a hidden helper input `TGrid_Selection_` that round-trips on every AJAX refresh. PHP reads it during render to emit `checked` attributes (no flicker). Master change clears the set in the existing `masterDataChange` jQuery handler. Server inserts a new first head/cell/footer fragment via three new partial templates when `MultiSelect=true`.

**Tech Stack:** PHP 8.4 (Tholos framework on Eisodos), jQuery, Eisodos templates, Font Awesome 5.

**Spec:** `docs/plans/2026-04-28-tgrid-multiselect-design.md`

**No test suite exists in this repo (per `Base/CLAUDE.md`).** Tasks therefore replace TDD with code-write + manual-browser verification. A final task walks the design spec's manual test checklist.

---

## File Structure

**New files (5):**
- `assets/templates/tholos/TGrid.partial.multiselect.head.template` — `<th>` with the tri-state Font Awesome icon
- `assets/templates/tholos/TGrid.partial.multiselect.cell.template` — `<td>` with the body checkbox
- `assets/templates/tholos/TGrid.partial.multiselect.footer.template` — "Kiválasztva: N" indicator + clear button
- `assets/css/_tgrid-multiselect.css` — column-width, icon-button styles (linked from the existing TGrid CSS bundle if there is one; otherwise the file is fine standalone)

**Modified files (5):**
- `src/Tholos/TGrid.php` — read `TGrid_Selection_`, set `SelectedCount`, prepend multiselect head/cell, edge-case guards
- `assets/templates/tholos/TGrid.partial.head.template` — add the `TGrid_Selection_` hidden input
- `assets/templates/tholos/TGrid.pagination.main.template` — include the multiselect footer partial next to the row-count badge
- `assets/js/TGrid.js` — new `TGrid_*` functions, wire into `TGrid_ready`, extend two `masterDataChange` handlers
- `docs/Tholos_Component_Types.md` — already synced (no further edit needed)

---

## Task 1: PHP — read `TGrid_Selection_` post param and compute `SelectedCount`

**Files:**
- Modify: `src/Tholos/TGrid.php` (init() block ~line 75–205, render() pre-master-clear block ~1351–1360)

- [ ] **Step 1: Add SelectedValues/SelectedCount default initialization in `init()`**

In `src/Tholos/TGrid.php`, inside `init()` after the existing `setProperty('cellRowType', ...)` calls (around line 96), add:

```php
// MultiSelect runtime state defaults — overwritten below if the post carries values
$this->setProperty('SelectedValues', '[]');
$this->setProperty('SelectedCount', '0');
```

- [ ] **Step 2: Read the `TGrid_Selection_` post parameter**

In `init()`, after the existing block that reads `TGrid_MasterValue_` (around line 190–192), add:

```php
if (Eisodos::$parameterHandler->neq('TGrid_Selection_', '')) {
  $raw = Eisodos::$parameterHandler->getParam('TGrid_Selection_');
  $decoded = json_decode($raw, true);
  if (is_array($decoded)) {
    // store both forms — string for round-trip, integer for templates
    $this->setProperty('SelectedValues', $raw);
    $this->setProperty('SelectedCount', (string)count($decoded));
  } else {
    Tholos::$logger->warn('TGrid_Selection_ payload is not a JSON array, ignored', $this);
    $this->setProperty('SelectedValues', '[]');
    $this->setProperty('SelectedCount', '0');
  }
}
```

- [ ] **Step 3: Cache the decoded array on the instance**

Add a private property near the existing private state (around line 75):

```php
/** @var string[] decoded selection set; rebuilt from SelectedValues at the start of render() */
private array $selectedValuesArray = [];
```

In `render()` (around line 1291) at the very top of the method (before any existing logic), add:

```php
$this->selectedValuesArray = json_decode($this->getProperty('SelectedValues', '[]'), true) ?: [];
```

- [ ] **Step 4: Manual sanity check (browser)**

Open any TGrid page that has `MultiSelect=true` set (or set it temporarily on an existing grid). Refresh and inspect the page source:
- `<input id="TGrid_Selection_" ...>` must exist (Task 2 will add this; verifying in this task is optional and can be done after Task 2).
- Server should not error.

- [ ] **Step 5: Commit**

```bash
git add src/Tholos/TGrid.php
git commit -m "feat(TGrid): read TGrid_Selection_ post param and cache decoded array"
```

---

## Task 2: Add `TGrid_Selection_` hidden input to the helper form

**Files:**
- Modify: `assets/templates/tholos/TGrid.partial.head.template:36`

- [ ] **Step 1: Add the hidden input**

After line 36 (the existing `TGrid_Value_` hidden input), insert:

```html
<input type="hidden" id="TGrid_Selection_" name="TGrid_Selection_" value="$prop_selectedvalues">
```

The block becomes:

```html
<input type="hidden" id="TGrid_Value_" name="TGrid_Value_" value="$prop_value">
<input type="hidden" id="TGrid_Selection_" name="TGrid_Selection_" value="$prop_selectedvalues">
<input type="hidden" id="TGrid_ViewMode_" name="TGrid_ViewMode_" value="$prop_ViewMode">
```

- [ ] **Step 2: Manual verification**

Reload a TGrid page; in DevTools, find `#helper_<grid_id>` and confirm `TGrid_Selection_` exists with `value="[]"` (or whatever was last posted).

- [ ] **Step 3: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.head.template
git commit -m "feat(TGrid): add TGrid_Selection_ hidden input to helper form"
```

---

## Task 3: Create the multiselect head partial template

**Files:**
- Create: `assets/templates/tholos/TGrid.partial.multiselect.head.template`

- [ ] **Step 1: Write the file**

Create `assets/templates/tholos/TGrid.partial.multiselect.head.template` with:

```html
<th class="TGrid-multiselect-head" style="width: 36px; text-align: center;">
  <a href="javascript:void(0);"
     onclick="TGrid_toggleVisibleSelection('$prop_id');"
     class="TGrid-multiselect-toggle"
     title="[:GRID.MULTISELECT.TOGGLE_VISIBLE,Összes láthatóság kijelölése/visszavonása:]">
    <i id="multiselect_head_icon_$prop_id" class="fa fa-square-o text-muted"></i>
  </a>
</th>
```

The icon starts as `fa-square-o`; JS swaps it to `fa-minus-square`/`fa-check-square` on `TGrid_ready`. `TGrid_toggleVisibleSelection` is defined in Task 7 — it inspects current state and calls either `TGrid_selectVisible` or `TGrid_deselectVisible`.

- [ ] **Step 2: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.multiselect.head.template
git commit -m "feat(TGrid): add multiselect tri-state head partial"
```

---

## Task 4: Create the multiselect cell partial template

**Files:**
- Create: `assets/templates/tholos/TGrid.partial.multiselect.cell.template`

- [ ] **Step 1: Write the file**

Create `assets/templates/tholos/TGrid.partial.multiselect.cell.template` with:

```html
<td class="TGrid-multiselect-cell" style="width: 36px; text-align: center;">
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

`$prop_value` is the row's DBField value (set by the row iteration before this partial is rendered, same way the existing `selectable` partial reads it — see `TGrid.partial.selectable.template:9`).

`$prop_isselected` is set by Task 5 just before each `renderPartial(...,'multiselect.cell')` call.

- [ ] **Step 2: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.multiselect.cell.template
git commit -m "feat(TGrid): add multiselect body cell partial"
```

---

## Task 5: Inject multiselect head and cell into row assembly

**Files:**
- Modify: `src/Tholos/TGrid.php` (column-head finalization ~line 733/762, row-content assembly ~line 1548–1602)

- [ ] **Step 1: Prepend the multiselect head fragment to `$this->columnHeadItems`**

After each `$this->columnHeadItems = $this->renderPartial($this, 'headitems', implode($items));` line (line 733 and 762 in the current file), add a sibling block immediately after it:

```php
if ($this->getProperty('MultiSelect', 'false') === 'true'
  && $this->getProperty('Transposed', 'false') === 'false'
  && $this->getProperty('ViewMode', 'GRID') === 'GRID') {
  $this->columnHeadItems = $this->renderPartial($this, 'multiselect.head') . $this->columnHeadItems;
}
```

(Both call sites are inside the same `if/else` branch responsible for the non-row vs row column layouts; both need the prepend so the header has one extra leading cell either way.)

- [ ] **Step 2: Inject the multiselect cell into standalone-column rows**

In the standalone-column branch (around line 1548–1551), replace the existing block:

```php
if ($columns !== '') {
  if ($this->getPropertyComponentId('DBField') !== false) {
    $columns = $this->renderPartial($this, 'selectable') . $columns;
  }
  $result .= $this->renderPartial($this, 'row', $columns) . "\n";
  $hasAnyStandaloneGridColumn = true;
}
```

with:

```php
if ($columns !== '') {
  if ($this->getProperty('MultiSelect', 'false') === 'true'
    && $this->getProperty('Transposed', 'false') === 'false'
    && $this->getProperty('ViewMode', 'GRID') === 'GRID') {
    $rowValue = $this->getProperty('Value', '');
    $this->setProperty('IsSelected', in_array($rowValue, $this->selectedValuesArray, true) ? 'true' : 'false');
    $columns = $this->renderPartial($this, 'multiselect.cell') . $columns;
  }
  if ($this->getPropertyComponentId('DBField') !== false) {
    $columns = $this->renderPartial($this, 'selectable') . $columns;
  }
  $result .= $this->renderPartial($this, 'row', $columns) . "\n";
  $hasAnyStandaloneGridColumn = true;
}
```

- [ ] **Step 3: Inject the multiselect cell into TGridRow-grouped rows**

In the TGridRow branch (around line 1594–1602), replace:

```php
if (!$isEmptyRow || Tholos::$app->findComponentByID($rowID)->getProperty('HideWhenEmpty', 'false') === 'false') {
  if ($this->getPropertyComponentId('DBField') !== false) {
    if ($hasAnyStandaloneGridColumn) {
      $columns = $this->renderPartial($this, 'noselectable') . $columns;
    } else {
      $columns = $this->renderPartial($this, 'selectable') . $columns;
    }
  }
  $result .= Tholos::$app->findComponentByID($rowID)->render($this, $columns) . "\n";
}
```

with:

```php
if (!$isEmptyRow || Tholos::$app->findComponentByID($rowID)->getProperty('HideWhenEmpty', 'false') === 'false') {
  if ($this->getProperty('MultiSelect', 'false') === 'true'
    && $this->getProperty('Transposed', 'false') === 'false'
    && $this->getProperty('ViewMode', 'GRID') === 'GRID'
    && $rowNum === 1) {
    $rowValue = $this->getProperty('Value', '');
    $this->setProperty('IsSelected', in_array($rowValue, $this->selectedValuesArray, true) ? 'true' : 'false');
    $columns = $this->renderPartial($this, 'multiselect.cell') . $columns;
  }
  if ($this->getPropertyComponentId('DBField') !== false) {
    if ($hasAnyStandaloneGridColumn) {
      $columns = $this->renderPartial($this, 'noselectable') . $columns;
    } else {
      $columns = $this->renderPartial($this, 'selectable') . $columns;
    }
  }
  $result .= Tholos::$app->findComponentByID($rowID)->render($this, $columns) . "\n";
}
```

The `$rowNum === 1` guard ensures the checkbox renders only on the first sub-row when a logical row is split across multiple TGridRow children (matches how the existing `selectable` partial behaves via `$hasAnyStandaloneGridColumn`).

- [ ] **Step 4: Manual verification (browser)**

Set `MultiSelect=true` on a non-transposed grid in GRID mode. Reload. Confirm:
- A new first column appears with an empty checkbox icon in the header.
- Each row has an unchecked `<input type="checkbox">` in its first cell.
- No JS console errors yet (functions don't exist; clicks fail silently — fixed in Task 7).

- [ ] **Step 5: Commit**

```bash
git add src/Tholos/TGrid.php
git commit -m "feat(TGrid): prepend multiselect head and per-row cell when MultiSelect=true"
```

---

## Task 6: Create the multiselect footer partial and wire into pagination

**Files:**
- Create: `assets/templates/tholos/TGrid.partial.multiselect.footer.template`
- Modify: `assets/templates/tholos/TGrid.pagination.main.template:14`

- [ ] **Step 1: Create the footer partial**

Create `assets/templates/tholos/TGrid.partial.multiselect.footer.template` with:

```html
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=prop_multiselect
value=true
true=<span id="multiselect_footer_$prop_id" class="badge badge-primary ms-2" [%_function_name=Tholos\TholosCallback::_eqs;param=prop_selectedcount;value=0;true=style="display:none;";false=%]>[:GRID.SELECTED,Kiválasztva:]: <span id="multiselect_count_$prop_id">$prop_selectedcount</span><a href="javascript:void(0);" onclick="TGrid_clearSelection('$prop_id');" class="ms-2 text-white" title="[:GRID.MULTISELECT.CLEAR,Kijelölés törlése:]"><i class="fa fa-times-circle"></i></a></span>
false=
%FUNC%>
```

- [ ] **Step 2: Insert the partial next to the row-count badge in `pagination.main.template`**

In `assets/templates/tholos/TGrid.pagination.main.template`, replace line 14:

```html
  <div class="col-md-2 d-flex"><span class="badge badge-secondary">[:GRID.PAGINATION.NUMBER_OF_ROWS,%s sor:$prop_TotalRowCount:]</span></div>
```

with:

```html
  <div class="col-md-2 d-flex align-items-center"><span class="badge badge-secondary">[:GRID.PAGINATION.NUMBER_OF_ROWS,%s sor:$prop_TotalRowCount:]</span>$templateabs_tholos__TGrid_partial_multiselect_footer</div>
```

The existing Eisodos template engine resolves `$templateabs_tholos__TGrid_partial_multiselect_footer` to the rendered partial by file path — same convention used elsewhere in this codebase (e.g., `$templateabs_tholos__TGrid_export` at TGrid.partial.head.template:54).

- [ ] **Step 3: Manual verification**

Reload a `MultiSelect=true` grid. Confirm the indicator span exists in DOM (hidden initially since `SelectedCount=0`). Force `display:block` in DevTools to inspect the markup.

- [ ] **Step 4: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.multiselect.footer.template assets/templates/tholos/TGrid.pagination.main.template
git commit -m "feat(TGrid): add multiselect footer indicator + clear button beside row-count badge"
```

---

## Task 7: Add `TGrid_*` JS functions for selection management

**Files:**
- Modify: `assets/js/TGrid.js` (append after line 591, before the document-ready block at 593)

- [ ] **Step 1: Append the new functions**

Open `assets/js/TGrid.js`. Immediately after the closing `}` of `TGrid_ready` (line 591) and before `$(document).ready(...)` (line 593), insert:

```javascript
function TGrid_getSelectionSet(formId_) {
  var raw = $("#helper_" + formId_ + " #TGrid_Selection_").val() || "[]";
  var parsed;
  try { parsed = JSON.parse(raw); } catch (e) { parsed = []; }
  if (!Array.isArray(parsed)) { parsed = []; }
  return new Set(parsed.map(String));
}

function TGrid_setSelectionSet(formId_, set_) {
  var arr = Array.from(set_);
  $("#helper_" + formId_ + " #TGrid_Selection_").val(JSON.stringify(arr));
  $("#multiselect_count_" + formId_).text(arr.length);
  if (arr.length === 0) {
    $("#multiselect_footer_" + formId_).hide();
  } else {
    $("#multiselect_footer_" + formId_).show();
  }
}

function TGrid_getValues(formId_) {
  return Array.from(TGrid_getSelectionSet(formId_));
}

function TGrid_isSelected(formId_, value_) {
  return TGrid_getSelectionSet(formId_).has(String(value_));
}

function TGrid_toggleSelection(formId_, value_) {
  var set = TGrid_getSelectionSet(formId_);
  var v = String(value_);
  if (set.has(v)) { set.delete(v); } else { set.add(v); }
  TGrid_setSelectionSet(formId_, set);
  TGrid_updateHeaderIcon(formId_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'selectionchange');
}

function TGrid_selectVisible(formId_) {
  var set = TGrid_getSelectionSet(formId_);
  $("#" + formId_ + " .TGrid-multiselect-checkbox").each(function () {
    set.add(String($(this).data('value')));
    $(this).prop('checked', true);
  });
  TGrid_setSelectionSet(formId_, set);
  TGrid_updateHeaderIcon(formId_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'selectionchange');
}

function TGrid_deselectVisible(formId_) {
  var set = TGrid_getSelectionSet(formId_);
  $("#" + formId_ + " .TGrid-multiselect-checkbox").each(function () {
    set.delete(String($(this).data('value')));
    $(this).prop('checked', false);
  });
  TGrid_setSelectionSet(formId_, set);
  TGrid_updateHeaderIcon(formId_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'selectionchange');
}

function TGrid_toggleVisibleSelection(formId_) {
  var $checkboxes = $("#" + formId_ + " .TGrid-multiselect-checkbox");
  if ($checkboxes.length === 0) { return; }
  var allChecked = $checkboxes.length === $checkboxes.filter(":checked").length;
  if (allChecked) {
    TGrid_deselectVisible(formId_);
  } else {
    TGrid_selectVisible(formId_);
  }
}

function TGrid_clearSelection(formId_) {
  TGrid_setSelectionSet(formId_, new Set());
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'selectionchange');
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

function TGrid_updateHeaderIcon(formId_) {
  var $icon = $("#multiselect_head_icon_" + formId_);
  if ($icon.length === 0) { return; }
  var $checkboxes = $("#" + formId_ + " .TGrid-multiselect-checkbox");
  var total = $checkboxes.length;
  var checked = $checkboxes.filter(":checked").length;
  $icon.removeClass("fa-square-o fa-minus-square fa-check-square text-muted text-warning text-success");
  if (checked === 0) {
    $icon.addClass("fa-square-o text-muted");
  } else if (checked === total) {
    $icon.addClass("fa-check-square text-success");
  } else {
    $icon.addClass("fa-minus-square text-warning");
  }
}
```

- [ ] **Step 2: Manual verification (browser console)**

Reload a `MultiSelect=true` grid. In DevTools console, on a grid with `id="my_grid"`:

```javascript
TGrid_getValues('my_grid')   // → []
TGrid_toggleSelection('my_grid', '42')
TGrid_getValues('my_grid')   // → ["42"]
```

The footer count should update to `1` and the indicator should show. Click the actual checkboxes — count changes; tri-state header icon updates.

- [ ] **Step 3: Commit**

```bash
git add assets/js/TGrid.js
git commit -m "feat(TGrid): add TGrid_* multiselect functions"
```

---

## Task 8: Wire bootstrap into `TGrid_ready` and extend masterDataChange handlers

**Files:**
- Modify: `assets/js/TGrid.js` (TGrid_ready ~line 568, TGrid_submit success callback ~line 168)

- [ ] **Step 1: Add multiselect bootstrap at the end of `TGrid_ready`**

In `TGrid_ready(formId_)` (line 568), immediately before the closing `}` (line 591), append:

```javascript
  // multiselect bootstrap — runs after every HTML swap
  if ($("#helper_" + formId_ + " #TGrid_Selection_").length > 0) {
    TGrid_updateHeaderIcon(formId_);
    var set = TGrid_getSelectionSet(formId_);
    TGrid_setSelectionSet(formId_, set); // refreshes footer count + visibility
  }
```

- [ ] **Step 2: Clear `TGrid_Selection_` in `TGrid_ready`'s masterDataChange handler**

In `TGrid_ready` (line 582–590), modify the existing handler. The current block:

```javascript
  $("#" + formId_ + "-props").on("masterDataChange", function (e, edata) {
    if ($("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val() != edata) {
      $("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val(edata);
      $("#helper_" + $(this).data().id + " #TGrid_Value_").val('');
      $("#helper_" + $(this).data().id + " #TGrid_ActivePage_").val(1);
      $("#helper_" + $(this).data().id + " #TGrid_MasterValueChanged_").val('T');
      Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
    }
  });
```

becomes:

```javascript
  $("#" + formId_ + "-props").on("masterDataChange", function (e, edata) {
    if ($("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val() != edata) {
      $("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val(edata);
      $("#helper_" + $(this).data().id + " #TGrid_Value_").val('');
      $("#helper_" + $(this).data().id + " #TGrid_Selection_").val('[]');
      $("#helper_" + $(this).data().id + " #TGrid_ActivePage_").val(1);
      $("#helper_" + $(this).data().id + " #TGrid_MasterValueChanged_").val('T');
      Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
    }
  });
```

(Single new line: `$("#helper_" + $(this).data().id + " #TGrid_Selection_").val('[]');`)

- [ ] **Step 3: Clear `TGrid_Selection_` in the matching `TGrid_submit` success callback**

In `TGrid_submit` (line 168–176 — the masterDataChange handler re-bound after AJAX success), apply the same one-line addition:

```javascript
          $("#" + target + "-props").on("masterDataChange", function (e, edata) {
            if ($("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val() != edata) {
              $("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val(edata);
              $("#helper_" + $(this).data().id + " #TGrid_Value_").val('');
              $("#helper_" + $(this).data().id + " #TGrid_Selection_").val('[]');
              $("#helper_" + $(this).data().id + " #TGrid_ActivePage_").val(1);
              $("#helper_" + $(this).data().id + " #TGrid_MasterValueChanged_").val('T');
              Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
            }
          });
```

(Single new line, same as Step 2.)

- [ ] **Step 4: Manual verification — persistence**

Reload a `MultiSelect=true` grid. Select a few rows. Click sort, change a filter, switch page — checkboxes persist; tri-state icon updates correctly each time. Trigger a master change (e.g., parent grid row click); the child grid's selection clears.

- [ ] **Step 5: Commit**

```bash
git add assets/js/TGrid.js
git commit -m "feat(TGrid): wire multiselect into TGrid_ready and clear on masterDataChange"
```

---

## Task 9: Edge-case guards and CSS polish

**Files:**
- Create: `assets/css/_tgrid-multiselect.css`
- Modify: `src/Tholos/TGrid.php` (DBField missing warning, transposed warning)

- [ ] **Step 1: Add CSS for the checkbox column**

Create `assets/css/_tgrid-multiselect.css` with:

```css
.TGrid-multiselect-head,
.TGrid-multiselect-cell {
  width: 36px;
  min-width: 36px;
  text-align: center;
  vertical-align: middle;
}

.TGrid-multiselect-head a {
  color: inherit;
  text-decoration: none;
  font-size: 1.1rem;
}

.TGrid-multiselect-head a:hover .fa {
  filter: brightness(1.2);
}

.TGrid-multiselect-checkbox {
  cursor: pointer;
  transform: scale(1.1);
}
```

Locate the existing TGrid CSS bundle (search for an `@import` of `_tgrid-*.css` in the SCSS sources under `assets/css/`). If a TGrid bundle file exists, append `@import "_tgrid-multiselect";` to it. If not, link the new CSS directly via the existing template head — check `assets/css/` for the prevailing convention before deciding. Make the smallest change that gets the styles loaded on grid pages.

- [ ] **Step 2: Add a logger warning for `MultiSelect=true` without DBField**

In `src/Tholos/TGrid.php` `init()`, after the SelectedValues default initialization (Task 1, Step 1), add:

```php
if ($this->getProperty('MultiSelect', 'false') === 'true'
  && $this->getPropertyComponentId('DBField', false) === false) {
  Tholos::$logger->warn('TGrid MultiSelect=true requires a DBField; checkboxes will not be rendered for this grid', $this);
}
```

- [ ] **Step 3: Add a logger warning for `MultiSelect=true` in transposed mode**

Right after the warning above, add:

```php
if ($this->getProperty('MultiSelect', 'false') === 'true'
  && $this->getProperty('Transposed', 'false') === 'true') {
  Tholos::$logger->warn('TGrid MultiSelect is not supported in transposed mode; checkbox column will be skipped', $this);
}
```

(The render-time guards in Task 5 already skip the column for both cases — these warnings just surface the condition to developers.)

- [ ] **Step 4: Add a render-time guard in the standalone-column branch when DBField is missing**

In Task 5, Step 2's added block:

```php
if ($this->getProperty('MultiSelect', 'false') === 'true'
  && $this->getProperty('Transposed', 'false') === 'false'
  && $this->getProperty('ViewMode', 'GRID') === 'GRID') {
```

extend the condition to require DBField:

```php
if ($this->getProperty('MultiSelect', 'false') === 'true'
  && $this->getProperty('Transposed', 'false') === 'false'
  && $this->getProperty('ViewMode', 'GRID') === 'GRID'
  && $this->getPropertyComponentId('DBField', false) !== false) {
```

Apply the same extension to the TGridRow-grouped block from Task 5, Step 3 and to the column-head injection from Task 5, Step 1.

- [ ] **Step 5: Manual verification**

- Set `MultiSelect=true` on a transposed grid — column does not appear, logger logs the warning.
- Set `MultiSelect=true` on a grid without DBField — column does not appear, logger logs the warning.
- Set `MultiSelect=true` on a normal grid — column appears, styled width 36px, header icon hover-brightens.

- [ ] **Step 6: Commit**

```bash
git add assets/css/_tgrid-multiselect.css src/Tholos/TGrid.php
git commit -m "feat(TGrid): add multiselect CSS and edge-case guards (transposed, missing DBField)"
```

---

## Task 10: End-to-end manual verification pass

**Files:** none (read-only)

Walk the design spec's manual test checklist (`docs/plans/2026-04-28-tgrid-multiselect-design.md` § Testing). Each item below is independent — fix any failure with a follow-up commit before continuing.

- [ ] **Step 1: Toggle**

Click a body checkbox → footer count updates → `onSelectionChange` handler (if registered on the grid in the app) fires.

- [ ] **Step 2: Persist on refresh**

Select rows → click refresh button → all rows stay checked.

- [ ] **Step 3: Persist on sort**

Select rows → click a sortable column header → checked rows still checked at their new positions.

- [ ] **Step 4: Persist on filter change**

Select rows → change a filter that excludes some selected rows → remaining visible selected rows still checked; hidden rows still in the array (verify via `TGrid_getValues('<grid_id>')` in console).

- [ ] **Step 5: Persist on paging**

Select on page 1 → go to page 3 → select more → return to page 1 → both ranges still selected.

- [ ] **Step 6: Tri-state header icon**

- 0 of N visible selected → `fa-square-o`.
- 1..N-1 of N visible selected → `fa-minus-square`.
- N of N visible selected → `fa-check-square`.

- [ ] **Step 7: Header click**

When not all visible selected, clicking checks all visible. When all visible selected, clicking unchecks them.

- [ ] **Step 8: Clear button**

Footer indicator + clear button visible only when count > 0. Click clear → empty array, grid refreshes, footer hides.

- [ ] **Step 9: Master change**

Parent grid changes selected row → child grid's selection clears (footer hidden, all checkboxes unchecked).

- [ ] **Step 10: Coexistence with `Selectable`**

Single-row click still highlights the "current" row and updates `LookupValue`; checkbox column is independent. Verify on a grid that has both `Selectable=true` and `MultiSelect=true`.

- [ ] **Step 11: `getValues()`**

In console: `TGrid_getValues('<grid_id>')` returns expected array of strings.

- [ ] **Step 12: No-DBField fallback**

Define a TGrid with `MultiSelect=true` and no `DBField` → checkbox column does not render; logger shows warning.

- [ ] **Step 13: Final commit / push**

If any fixes were committed during this task, push the branch:

```bash
git push -u origin feature/tgrid-multiselect
```

---

## Self-Review Checklist (run before declaring plan ready)

1. **Spec coverage** — every property/event/method/edge case in `docs/plans/2026-04-28-tgrid-multiselect-design.md` is implemented in some task above. ✔
2. **Placeholders** — no "TBD"/"TODO"/"add error handling"/etc. ✔
3. **Type/name consistency** — `TGrid_toggleSelection`, `TGrid_selectVisible`, `TGrid_deselectVisible`, `TGrid_toggleVisibleSelection`, `TGrid_clearSelection`, `TGrid_getValues`, `TGrid_isSelected`, `TGrid_updateHeaderIcon`, `TGrid_getSelectionSet`, `TGrid_setSelectionSet` — function names and call sites match across tasks. ✔
4. **File paths** — all paths are absolute from repo root and exist (existing files) or are explicitly marked as new. ✔
