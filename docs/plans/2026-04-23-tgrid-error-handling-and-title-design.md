# TGrid Error Handling & Title Property

**Date:** 2026-04-23  
**Status:** Approved

## Problem Statement

### ListSource Error Handling
When TGrid's ListSource (TDataProvider/TQuery/TStoredProcedure) fails — due to database errors, connection timeouts, bad SQL, or filter errors — the UI hangs. The exception is caught silently in `TDataProvider.run()` (line 219-221), logged via `Tholos::$logger->error()`, and stored in `Tholos::$app->responseErrorMessage`, but the grid renders as if it got zero rows. On the AJAX path (`TGrid_submit`), the grid container fades to 20% opacity and never recovers, leaving the user stuck with a dimmed, unresponsive grid and no error feedback.

Three distinct failure points exist:
1. `ListSource.open()` throws an exception (DB error, connection failure) — caught in `TDataProvider.run()`, grid gets `RowCount=0`
2. Filter SQL generation fails — `FilterError` flag set silently in `TQuery`, query returns early with no data
3. AJAX network/server error — `TGrid_submit()` error callback only redirects on 401, otherwise calls generic `Tholos.action(false)` without restoring grid UI

### Title Property
TGrid has no title/heading capability. Users need a visible title shown inline with the filter controls on desktop and above the table on mobile.

## Design

### Feature 1: ListSource Error Handling

#### Server-side (TGrid.php)

In the `render()` method, after `$listSource->run($this)` (line 1464), detect error state by checking if `Tholos::$app->responseErrorMessage` is non-empty. When an error is detected:

1. Capture the error flag (non-empty string, e.g. the generic label) into a local variable
2. Clear `Tholos::$app->responseErrorMessage` after capturing (so it doesn't leak into the response JSON)
3. Continue rendering normally — column headers render, row loop produces nothing (`RowCount=0`)
4. Pass `showdataerror` as a parameter to the existing `renderPartial($this, 'foot', ...)` call (line 1725-1731), alongside the existing `selectionoutoflist`, `pagination`, and `cacheinfo` parameters

Error logging is already handled by `TDataProvider.run()` at line 220: `Tholos::$logger->error($e->getMessage())`. No additional server-side logging is needed.

No error details are sent to the client. Only the generic translated message appears in the rendered HTML.

**TGrid.php change** — add `showdataerror` to the `renderPartial('foot')` parameters array:
```php
'showdataerror' => $hasDataError ? 'true' : '',
```

**TGrid.partial.foot.template change** — replace the raw `$showdataerror` on line 2 with a conditional block matching the `selectionoutoflist` pattern but using `alert-danger`. The translatable label is in the template, not in PHP:
```
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=showdataerror
value=
true=
false=<div id="$prop_id-showdataerror" class="col-md-12"><div class="alert alert-danger mb-3" style="margin-bottom: 0px"><i class="fa fa-exclamation-triangle text-danger"></i> [:TGRID.ERROR.DATASOURCE,Adatok betöltése sikertelen:]</div></div>
%FUNC%>
```

This follows the exact same pattern as `selectionoutoflist` (line 10-12 in the same template) — conditional rendering, alert div with icon, language tag in the template.

#### Client-side (TGrid.js)

In the `error` callback of `TGrid_submit()` (line 188-191), restore the grid to a usable state:

```javascript
error: function (response, textStatus, errorThrown) {
    if (response.status === 401) location.href = '/';
    $("#container_" + target).fadeTo(0, 1);  // restore opacity
    $("#loader_" + target).hide();            // hide loader
    Tholos.action(false, sender, target);
}
```

Same fix for the CHART mode error callback (line 208-211).

#### What remains functional in error state

- Column headers visible (rendered before row loop)
- Filter dropdown and filter slots
- Refresh button (red filter icon at `#filter_refresh`)
- Individual filter removal
- Scroll/transpose/export controls (visible, export produces empty result)
- Pagination suppressed (already happens when `RowCount=0`)

#### Affected views

- **Desktop table view:** Column headers in `<thead>`, error div below `</table>` in foot template
- **Responsive div view:** Same structure — headers render, error appears in foot
- **Transposed view:** Error appears in same foot slot, transpose header column visible
- **Mobile view:** Same — error banner stacks naturally below grid structure
- **Chart view:** Error callback restores UI; chart data will be empty

### Feature 2: Title Property

#### Assumption

The `Title` property is already added to the TGrid component type definition in TholosBuilder. It is a basic STRING property (like `RowClass`), not mandatory, no default value.

#### Template change (TGrid.partial.head.template)

Insert the title inline within the existing `col-md-6` div (line 49-51), right after `$filters`:

```
<div class="col-md-6">
$filters[%_function_name=Tholos\TholosCallback::_eqs;param=prop_title;value=;true=;false= <span class="TGrid-title">$prop_title</span>%]
</div>
```

Only rendered when the property has a value (empty check via `_eqs` callback).

#### CSS (TGrid.css)

New `.TGrid-title` style:

```css
.TGrid-title {
  display: inline-block;
  font-weight: 600;
  margin-left: 10px;
  vertical-align: middle;
  font-size: 1rem;
  line-height: 1.5;
}
```

#### Mobile responsive (TGrid-responsive.css / .sass)

At `max-width: 580px` breakpoint:

```css
@media (max-width: 580px) {
  .TGrid-title {
    display: block;
    margin-left: 0;
    margin-top: 5px;
    margin-bottom: 5px;
  }
}
```

This makes the title stack above the table on mobile instead of staying inline.

#### Transposed view

No special handling needed — the title is in the `.TGrid-gridcontrols` section which renders above the data area regardless of transpose state.

## Files to modify

| File | Change |
|------|--------|
| `src/Tholos/TGrid.php` | Detect error state after `$listSource->run()`, pass `showdataerror` to `renderPartial('foot')` |
| `assets/templates/tholos/TGrid.partial.foot.template` | Replace raw `$showdataerror` with conditional `_eqs` block using `alert-danger` (same pattern as `selectionoutoflist`) |
| `assets/js/TGrid.js` | Restore grid opacity and hide loader in AJAX error callbacks |
| `assets/templates/tholos/TGrid.partial.head.template` | Add Title span after `$filters` |
| `assets/css/TGrid.css` | Add `.TGrid-title` style |
| `assets/css/TGrid-responsive.css` | Add mobile breakpoint for `.TGrid-title` |
| `assets/css/TGrid-responsive.sass` | Add mobile breakpoint for `.TGrid-title` (SASS source) |

## Files NOT modified

| File | Reason |
|------|--------|
| `TDataProvider.php` | Already logs errors and sets `responseErrorMessage` — no change needed |
| `TQuery.php` / `TStoredProcedure.php` | Error logging already in place — no change needed |
| Component type definitions | Title property added via TholosBuilder, not in this codebase |
