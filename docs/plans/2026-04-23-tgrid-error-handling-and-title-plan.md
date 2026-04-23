# TGrid Error Handling & Title Property Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix TGrid UI hang on ListSource errors and add a Title property to the grid header.

**Architecture:** Server-side error detection in TGrid.render() sets a flag passed to the foot template, which conditionally renders an alert-danger banner. Client-side AJAX error callbacks restore grid opacity/loader. Title is rendered inline next to the Filters button via a new CSS class.

**Tech Stack:** PHP 8.4, Eisodos template engine, jQuery, Bootstrap 5

**Design Spec:** `docs/plans/2026-04-23-tgrid-error-handling-and-title-design.md`

**Note:** This project has no test suite. Steps are verify-by-inspection.

---

### Task 1: Server-side error detection in TGrid.php

**Files:**
- Modify: `src/Tholos/TGrid.php:1464-1467` (after `$listSource->run()`)
- Modify: `src/Tholos/TGrid.php:1725-1733` (renderPartial 'foot' call)

- [ ] **Step 1: Add error flag after listSource->run()**

In `src/Tholos/TGrid.php`, after line 1464 (`$listSource->run($this);`) and before line 1466 (`$this->setProperty('RowCount', ...)`), add error detection. Find this block:

```php
        $listSource->run($this);
      }
      $this->setProperty('RowCount', $listSource->getProperty('RowCount', '0'));
```

Replace with:

```php
        $listSource->run($this);
      }
      
      $hasDataError = Tholos::$app->responseErrorMessage !== '';
      if ($hasDataError) {
        Tholos::$app->responseErrorMessage = '';
      }
      
      $this->setProperty('RowCount', $listSource->getProperty('RowCount', '0'));
```

- [ ] **Step 2: Pass showdataerror to renderPartial('foot')**

In the same file, find the `renderPartial` call for the foot template (around line 1725-1733):

```php
        $result .= $this->renderPartial($this, 'foot', '',
          array('pagination' => $pagination,
            'selectionoutoflist' => (($this->getProperty('Selectable', 'false') === 'true'
              && !$selection_found
              && $this->getProperty('DBField', '') !== ''
              && $this->getProperty('LookupValue', '') !== '') ? $this->getProperty('LabelSelectionOutOfList', '') : ''),
            'cacheinfo' => $cacheInfo
          )
        );
```

Replace with:

```php
        $result .= $this->renderPartial($this, 'foot', '',
          array('pagination' => $pagination,
            'selectionoutoflist' => (($this->getProperty('Selectable', 'false') === 'true'
              && !$selection_found
              && $this->getProperty('DBField', '') !== ''
              && $this->getProperty('LookupValue', '') !== '') ? $this->getProperty('LabelSelectionOutOfList', '') : ''),
            'cacheinfo' => $cacheInfo,
            'showdataerror' => $hasDataError ? 'true' : ''
          )
        );
```

- [ ] **Step 3: Verify syntax**

Run: `php -l src/Tholos/TGrid.php`
Expected: `No syntax errors detected in src/Tholos/TGrid.php`

- [ ] **Step 4: Commit**

```bash
git add src/Tholos/TGrid.php
git commit -m "feat(TGrid): detect ListSource errors and pass flag to foot template"
```

---

### Task 2: Error banner in foot template

**Files:**
- Modify: `assets/templates/tholos/TGrid.partial.foot.template:2` (replace raw `$showdataerror`)

- [ ] **Step 1: Replace raw variable with conditional block**

In `assets/templates/tholos/TGrid.partial.foot.template`, find line 2:

```
$showdataerror
```

Replace with:

```
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=showdataerror
value=
true=
false=<div id="$prop_id-showdataerror" class="col-md-12"><div class="alert alert-danger mb-3" style="margin-bottom: 0px"><i class="fa fa-exclamation-triangle text-danger"></i> [:TGRID.ERROR.DATASOURCE,Adatok betöltése sikertelen:]</div></div>
%FUNC%>
```

The full file should now look like:

```
[%_function_name=Tholos\TholosCallback::_case;param=prop_gridhtmltype;div=tholos/TGrid.partial.div.foot;div-responsive=tholos/TGrid.partial.divresponsive.foot;else=tholos/TGrid.partial.table.foot%]
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=showdataerror
value=
true=
false=<div id="$prop_id-showdataerror" class="col-md-12"><div class="alert alert-danger mb-3" style="margin-bottom: 0px"><i class="fa fa-exclamation-triangle text-danger"></i> [:TGRID.ERROR.DATASOURCE,Adatok betöltése sikertelen:]</div></div>
%FUNC%>
</div>

</div>
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
...
```

(rest of file unchanged)

- [ ] **Step 2: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.foot.template
git commit -m "feat(TGrid): add error banner with alert-danger in foot template"
```

---

### Task 3: Client-side AJAX error recovery in TGrid.js

**Files:**
- Modify: `assets/js/TGrid.js:188-191` (GRID mode error callback)
- Modify: `assets/js/TGrid.js:208-211` (CHART mode error callback)

- [ ] **Step 1: Fix GRID mode error callback**

In `assets/js/TGrid.js`, find the GRID mode error callback (line 188-191):

```javascript
        error: function (response, textStatus, errorThrown) {
          if (response.status === 401) location.href = '/';
          Tholos.action(false, sender, target);
        }
```

Replace with:

```javascript
        error: function (response, textStatus, errorThrown) {
          if (response.status === 401) location.href = '/';
          $("#container_" + target).fadeTo(0, 1);
          $("#loader_" + target).hide();
          Tholos.action(false, sender, target);
        }
```

- [ ] **Step 2: Fix CHART mode error callback**

In the same file, find the CHART mode error callback (line 208-211):

```javascript
        error: function (response, textStatus, errorThrown) {
          if (response.status === 401) location.href = '/';
          Tholos.action(false, sender, target);
        }
```

Replace with:

```javascript
        error: function (response, textStatus, errorThrown) {
          if (response.status === 401) location.href = '/';
          $("#container_" + target).fadeTo(0, 1);
          $("#loader_" + target).hide();
          Tholos.action(false, sender, target);
        }
```

- [ ] **Step 3: Commit**

```bash
git add assets/js/TGrid.js
git commit -m "fix(TGrid): restore grid opacity and hide loader on AJAX error"
```

---

### Task 4: Title in grid header template

**Files:**
- Modify: `assets/templates/tholos/TGrid.partial.head.template:49-51`

- [ ] **Step 1: Add Title span after filters**

In `assets/templates/tholos/TGrid.partial.head.template`, find lines 49-51:

```
    <div class="col-md-6">
$filters
    </div>
```

Replace with:

```
    <div class="col-md-6">
$filters[%_function_name=Tholos\TholosCallback::_eqs;param=prop_title;value=;true=;false= <span class="TGrid-title">$prop_title</span>%]
    </div>
```

- [ ] **Step 2: Commit**

```bash
git add assets/templates/tholos/TGrid.partial.head.template
git commit -m "feat(TGrid): add Title property display next to Filters button"
```

---

### Task 5: TGrid-title CSS styles

**Files:**
- Modify: `assets/css/TGrid.css` (append new style)
- Modify: `assets/css/TGrid-responsive.sass` (append mobile breakpoint)
- Modify: `assets/css/TGrid-responsive.css` (append compiled mobile breakpoint)

- [ ] **Step 1: Add .TGrid-title to TGrid.css**

Append to the end of `assets/css/TGrid.css` (after the last rule ending at line 330):

```css

/* title */

.TGrid-title {
  display: inline-block;
  font-weight: 600;
  margin-left: 10px;
  vertical-align: middle;
  font-size: 1rem;
  line-height: 1.5;
}
```

- [ ] **Step 2: Add mobile breakpoint to TGrid-responsive.sass**

Append to the end of `assets/css/TGrid-responsive.sass` (after line 78):

```sass

.TGrid-title
  @media screen and (max-width: 580px)
    display: block
    margin-left: 0
    margin-top: 5px
    margin-bottom: 5px
```

- [ ] **Step 3: Add compiled CSS to TGrid-responsive.css**

In `assets/css/TGrid-responsive.css`, before the sourcemap comment on line 84, insert:

```css
.TGrid-title {
  /* mobile responsive */
}
@media screen and (max-width: 580px) {
  .TGrid-title {
    display: block;
    margin-left: 0;
    margin-top: 5px;
    margin-bottom: 5px;
  }
}

```

- [ ] **Step 4: Commit**

```bash
git add assets/css/TGrid.css assets/css/TGrid-responsive.sass assets/css/TGrid-responsive.css
git commit -m "feat(TGrid): add .TGrid-title CSS with mobile responsive breakpoint"
```

---

### Task 6: Final verification

- [ ] **Step 1: Run PHP syntax check on all modified PHP files**

Run: `php -l src/Tholos/TGrid.php`
Expected: `No syntax errors detected`

- [ ] **Step 2: Verify all files are committed**

Run: `git status`
Expected: Clean working tree, no uncommitted changes.

- [ ] **Step 3: Review the commit log**

Run: `git log --oneline -5`
Expected: 4 commits for this feature (Tasks 1-5).
