# Config-driven `TDataProvider` auto-open (`Tholos.AutoOpenDataProvider`) — Design

**Date:** 2026-07-21
**Status:** Implemented; **amended 2026-08-04** — the render-root assumption in §2.2 proved wrong
in production. See **§12 Amendments** for what changed and why; sections below are updated in
place.
**Scope:** Backend (`TholosApplication`) — introduce a Config parameter that switches the
page-generation auto-open phase between the current behavior and a reference-aware
"only open what the render actually needs" behavior. No template/JS changes.

## 1. Goal

Today, every page generation eagerly opens **all** `TDataProvider` components (subject to
`AutoOpenAllowed` and, for `TQuery`/`TExternalDataProvider`, the presence of a `TDBfield`
child). On pages/partials that only render a small slice of a large action, this opens
(and runs the DB queries of) providers whose data is never used.

Introduce a Config parameter **`Tholos.AutoOpenDataProvider`** with three modes:

- **`Always`** (default) — current behavior, unchanged.
- **`Disabled`** — `TholosApplication::autoOpen()` never runs.
- **`Optimized`** — a provider auto-opens only if `AutoOpenAllowed=true` **and** it is either
  **directly called** (the render root *is* the provider) **or** **referenced from within the
  render subtree** (some component under the render point points to the provider or any of its
  descendant fields).

## 2. Background / current state

### 2.1 The auto-open chain

- **`TholosApplication::run()`** (`src/Tholos/TholosApplication.php:1041`) is the page-generation
  entry point. In the non-`REDIRECT` path it logs `Auto opening dataproviders` and calls
  **`$this->autoOpen()` (`:1067`) *before* the render phase**.
- **`TholosApplication::autoOpen()`** (`:825`) iterates **all** providers and delegates to each:
  ```php
  private function autoOpen(): void {
    foreach ($this->findComponentsByClass('TDataProvider') as $object) {
      /* @var TDataProvider $object */
      $object->autoOpen();
    }
  }
  ```
- **`TDataProvider::autoOpen()`** (`src/Tholos/TDataProvider.php:231`) gates on the
  `AutoOpenAllowed` property (default `'true'`) then calls `run(NULL)` → `openDatabase()` →
  `open()` → `propagateResult()`.
  - **`TQuery::autoOpen()`** (`src/Tholos/TQuery.php:421`) and
    **`TExternalDataProvider::autoOpen()`** (`src/Tholos/TExternalDataProvider.php:316`) add a
    second gate: they only open when the provider has ≥1 `TDBfield` child.
  - **`TJSONDataProvider::autoOpen()`** (`src/Tholos/TJSONDataProvider.php:81`) is a no-op.

### 2.2 The render root (render point)

`run()` resolves the render root the same way for autoOpen's purposes (`:1069–1090`):

- `isset($this->renderer)` → `$this->renderer->getId()`;
- else `tholos_partial` empty **or** `'*'` → `$this->action_id`;
- else the named partial: `findChildByName(findComponentByID($this->action_id), <name>)` →
  its id (throws if not found).

This root can be **narrower than the `TAction`** (a named partial), which is exactly where
the optimization pays off.

> **Amended 2026-08-04.** The original design assumed the render root's **component subtree** is
> what gets rendered. It is not. Rendering leaves the `parent_id` tree in three places, so the
> subtree is an incomplete — sometimes empty — model of what the page actually renders:
>
> | component | renders | in the render root's subtree? |
> |---|---|---|
> | `TPDFPage` | the action's header/body/footer containers, resolved **by name** (`TPDFPage.php:50-58`) | no — and `TPDFPage::init()` (`:27`) makes *itself* the render root while having **no children at all** |
> | `TAction` | its `Page` reference (`TAction.php:59-60`), which then renders its own subtree — the layout `TPartial`s (`TPage.php:30`) | no — the page component hangs off `TApplication` |
> | `TLinkedComponent` | its `Component` reference (`TLinkedComponent.php:32`) | no — an arbitrary component anywhere in the tree |
>
> Consequence in production (greengo backoffice): on `PRINT_RENTAL/rental_close` the scope was a
> single id, so **no provider auto-opened at all** and the generated PDF lost its header data; on
> `SAL_SERVICE_RENTALS/index` the `TLinkedComponent → rowGrid` indirection hid all six providers.
> §5.2b replaces the plain subtree with a render scope that follows these indirections.

### 2.3 The reverse reference index (`refComponents`)

Component-to-component references are already tracked. In `TComponent::setProperties()`
(`src/Tholos/TComponent.php:141`), any property of type `COMPONENT` registers:
```php
Tholos::$app->setReferencingComponent($prop['c'], $this->_id);
```
- `setReferencingComponent($referred, $referrer)` (`TholosApplication.php:583`) appends to
  `$this->refComponents[$referred][] = $referrer`.
- `findReferencingComponents($id)` (`:593`) returns the list of components that point at `$id`.

So a `THidden` whose `DBField` property points at `TDataProvider/DBField1` makes
`findReferencingComponents(DBField1_id)` include the `THidden` id. This mechanism is
**generic** (any `COMPONENT`-typed property, not just `DBField`).

### 2.4 Component tree

`$this->components[$id] = ['parent_id' => …, 'class_name' => …, 'object' => …]` is a flat map,
so a component's subtree is derivable by walking `parent_id`. `findChildIDsByType()`
(`TholosApplication.php:670`) only returns **direct** typed children; there is no generic
recursive descendant helper yet.

A subtree is **not** the same as "what gets rendered" — see the amendment in §2.2.

### 2.5 Why some dependencies are invisible (accepted risk)

Providers frequently feed each other **through global parameters**, not component references:
`TDBParam::initValue()` (`src/Tholos/TDBParam.php:102`) reads its value from
`Eisodos::$parameterHandler->getParam(...)`, and `TDBField::setProperty()` writes a field's
value into a global parameter (via `GlobalParameterPrefix`) when its provider opens. These
couplings are **not** in `refComponents`. The same is true for provider results consumed via
template functions (`<%FUNC%>`), events, or raw global parameters. In `Optimized` mode these
providers will **not** be detected — see §6 (accepted per §3 decisions).

## 3. Design decisions (locked)

| Decision | Choice |
|---|---|
| Config name / values | `Tholos.AutoOpenDataProvider` ∈ { `Always`, `Disabled`, `Optimized` } |
| Default (unset) | **`Always`** — full backward compatibility; `Optimized` is opt-in |
| Unknown value | Treated as `Always` (fail-safe to current behavior) |
| Detection strategy | **Pure reference detection** — no transitive closure *over provider dependencies*, **no** force/escape-hatch property |
| "children" scope | **Recursive descendants** of the provider (not just direct `TDBfield`) |
| Self-reference | A referencing component **inside the provider's own subtree does not count** (must be external to `DP` yet within the render subtree) |
| Render-scope evaluation | **Static structural** membership at auto-open time — independent of runtime role/visibility decisions (conservative: opens more, never fewer) |
| `AutoOpenAllowed` semantics | Still required in every mode; in `Optimized` it is **necessary but no longer sufficient** |
| Code location | **Private methods on `TholosApplication`** — no new class (only component types have their own PHP file in this codebase) |

> **Amended 2026-08-04.** "No transitive closure" was — and remains — a statement about **provider
> data dependencies**: the fact that provider A needs provider B is still never inferred. It was
> never a statement about *what the page renders*. Determining the render scope **does** require a
> closure over render indirections (§5.2b); without it the scope is not conservative but simply
> wrong, and opens **fewer** providers than the render needs.

## 4. Config parameter

Read once at the top of `TholosApplication::autoOpen()`:
```php
$mode = Eisodos::$parameterHandler->getParam('Tholos.AutoOpenDataProvider', 'Always');
```
Consistent with existing `Tholos.*` config reads (e.g. `Tholos.CSPEnabled`,
`Tholos.UseLogicalBool`).

## 5. Implementation (`src/Tholos/TholosApplication.php`)

### 5.1 New private field + shared render-root resolver

```php
private ?array $childIndex = null; // parent_id (string) => list<string child_id>, built lazily

/**
 * Resolves the id of the render root (render point) exactly as run() dispatches rendering.
 * Falls back to action_id when a named partial is not found (run() throws during render in
 * that case, so the fallback is never actually rendered).
 */
private function getRenderRootId(): int {
  if (isset($this->renderer)) {
    return $this->renderer->getId();
  }
  if (Eisodos::$parameterHandler->eq('tholos_partial', '')
    || Eisodos::$parameterHandler->eq('tholos_partial', '*')) {
    return $this->action_id;
  }
  $partial = $this->findChildByName(
    $this->findComponentByID($this->action_id),
    Eisodos::$parameterHandler->getParam('tholos_partial'));
  return $partial !== null ? (int)$this->findComponentId($partial) : $this->action_id;
}
```

### 5.2 Generic recursive subtree collector (id-set)

```php
/**
 * Returns the set { $root_ } ∪ all descendants, as an associative array (string id => true)
 * for O(1) membership tests. The parent→children index is built once per request.
 */
private function collectSubtreeIds(string $root_): array {
  if ($this->childIndex === null) {
    $this->childIndex = array();
    foreach ($this->components as $id => $component) {
      $this->childIndex[(string)$component['parent_id']][] = (string)$id;
    }
  }
  $result = array();
  $stack = array($root_);
  while ($stack) {
    $id = array_pop($stack);
    if (isset($result[$id])) {
      continue;
    }
    $result[$id] = true;
    foreach ($this->childIndex[$id] ?? array() as $childId) {
      $stack[] = $childId;
    }
  }
  return $result;
}
```
> Safe to cache: `autoOpen()` runs after `initComponents()`, so `$this->components` is complete
> and stable for the rest of the request.

### 5.2b Render scope — subtree plus render indirections *(added 2026-08-04)*

The set the per-provider predicate tests against. It seeds with the render root's subtree and
then, to a fixpoint, adds the subtree of every component reachable through a render indirection
(§2.2). `TPDFPage` is the one indirection that cannot be followed as a reference — it resolves
its containers **by name** — so the action subtree is seeded directly.

```php
private function collectRenderScopeIds(string $root_): array {
  $renderReferences = array('TLinkedComponent' => 'Component', 'TAction' => 'Page');

  $scope = $this->collectSubtreeIds($root_);
  if (isset($this->renderer, $this->action_id)) { // TPDFPage renders the action's containers
    $scope += $this->collectSubtreeIds((string)$this->action_id);
  }

  $pending = array_keys($scope);
  while ($pending) {
    $id = (string)array_pop($pending);
    $propertyName = $renderReferences[$this->components[$id]['class_name'] ?? ''] ?? NULL;
    if ($propertyName === NULL) {
      continue;
    }
    $component = $this->findComponentByID($id);
    if ($component === NULL || ($targetId = $component->getPropertyComponentId($propertyName)) === false) {
      continue;
    }
    foreach ($this->collectSubtreeIds((string)$targetId) as $targetSubtreeId => $ignored_) {
      if (!isset($scope[$targetSubtreeId])) {
        $scope[$targetSubtreeId] = true;
        $pending[] = $targetSubtreeId;
      }
    }
  }

  return $scope;
}
```

> `$this->action_id` is `int|null`; the `isset()` guard covers both. Without it a NULL action id
> would cast to `''`, and `collectSubtreeIds('')` would return every component whose `parent_id`
> is empty — a silently over-wide scope.

**Adding a component type to `$renderReferences`** is the maintenance point: whenever a component
renders a target that is not its descendant, its type and the `COMPONENT` property holding the
target belong in this map, or providers under that target become invisible again.

### 5.3 Optimized per-provider predicate

```php
/**
 * Optimized-mode test: should this provider auto-open?
 * True iff it is the render root (direct call) OR some component that is inside the render
 * subtree — but outside the provider's own subtree — references the provider or any of its
 * descendant fields.
 */
private function shouldAutoOpenOptimized(TComponent $dp_, string $renderRoot_, array $renderSubtree_): bool {
  $dpId = (string)$this->findComponentId($dp_);

  if ($dpId === $renderRoot_) {
    return true; // direct call: the URL/render root points at the provider itself
  }

  $dpSubtree = $this->collectSubtreeIds($dpId); // provider + its descendant fields
  foreach (array_keys($dpSubtree) as $triggerId) {
    foreach ($this->findReferencingComponents($triggerId) as $refId) {
      $refId = (string)$refId;
      if (isset($renderSubtree_[$refId]) && !isset($dpSubtree[$refId])) {
        return true; // referenced from within the render subtree, from outside the provider
      }
    }
  }
  return false;
}
```

### 5.4 Rewritten `autoOpen()`

```php
private function autoOpen(): void {
  $mode = Eisodos::$parameterHandler->getParam('Tholos.AutoOpenDataProvider', 'Always');

  if ($mode === 'Disabled') {
    Tholos::$logger->debug('AutoOpen disabled (Tholos.AutoOpenDataProvider=Disabled)');
    return;
  }

  if ($mode !== 'Optimized') { // 'Always' or any unknown value → legacy behavior
    foreach ($this->findComponentsByClass('TDataProvider') as $object) {
      /* @var TDataProvider $object */
      $object->autoOpen();
    }
    return;
  }

  // Optimized
  $renderRoot = (string)$this->getRenderRootId();
  $renderSubtree = $this->collectRenderScopeIds($renderRoot); // §5.2b — not the plain subtree
  foreach ($this->findComponentsByClass('TDataProvider') as $object) {
    /* @var TDataProvider $object */
    if ($this->shouldAutoOpenOptimized($object, $renderRoot, $renderSubtree)) {
      $object->autoOpen();
    } else {
      Tholos::$logger->trace('AutoOpen skipped (Optimized, not referenced in render subtree)', $object);
    }
  }
}
```
> `AutoOpenAllowed` and the `TQuery`/`TExternalDataProvider` "has `TDBfield` child" gates stay
> **inside** each provider's own `autoOpen()`. `TholosApplication` only decides *whether to call
> it*, so the effective condition is exactly `AutoOpenAllowed=true AND (direct OR referenced)`.

### 5.5 Optional, behavior-preserving `run()` refactor

To remove the duplicated render-root computation, `run()`'s render dispatch (`:1069–1090`) may
adopt `getRenderRootId()`:
```php
$renderRoot = $this->getRenderRootId();
// ... use $renderRoot in each render(NULL, $renderRoot) call ...
```
**Behavior that MUST be preserved** if this refactor is taken:
- The named-partial-not-found **`RuntimeException`** (guard it explicitly before rendering).
- `responseType` defaulting to `JSON` whenever `tholos_partial` is non-empty.
- Head/foot item wrapping applied for **both** `'*'` and named-partial renders.

This refactor is isolable from the feature: the three new modes work with `run()` untouched
(the resolver merely mirrors the existing dispatch). Recommended, but may be deferred.

## 6. Impact on current behavior

- **`Always` (default):** zero change. Existing apps behave identically.
- **`Disabled`:** `autoOpen()` does nothing. Only explicit / on-demand opens happen
  (e.g. `TGrid::renderDetails()` `src/Tholos/TGrid.php:1275`, or code that calls
  `open()`/`autoOpen()` directly). Providers relying on eager open yield empty results —
  intended for API/partial endpoints where opening is managed elsewhere.
- **`Optimized`:** a provider opens only when directly called or referenced from within the
  render subtree. **Accepted breakages** (per §3):
  1. Upstream providers that feed others **only through global parameters** and are not
     referenced by any rendered component → not opened → dependent providers/params fall back
     to defaults. Developer must restructure (e.g. reference the field, or use a supported mode
     per action).
  2. Providers whose results are consumed **only via `<%FUNC%>` template functions, events, or
     raw global parameters** (no `COMPONENT` reference) → not opened.
- **Unaffected regardless of mode:** `TGrid`, `TMapSource`, `TRoleManager`, `TQueryGroup` and —
  since 2026-08-04 — `TIterator` set their source's `AutoOpenAllowed='false'` in `init()` and open
  it on demand. (`TIterator` did not, so its `ListSource` was opened twice per request: once by
  the auto-open phase with `$sender = NULL`, then again by `TIterator::render()`'s
  `close()` + `run($this)`. Besides the redundant query the two runs evaluate
  `TQueryFilter::initValue()` with opposite `RecordSelector` semantics, which shows up as
  contradictory `QueryFilter applied` / `QueryFilter skipped` log lines for the same filter.)
- **Performance (`Optimized`):** one `O(N)` parent→children index build, the render-scope closure,
  and per-provider descendant collection plus `refComponents` lookups. Measured on the compiled
  greengo backoffice definitions (php 8.4, no opcache, 200 iterations), the whole analysis costs
  **0.07–0.12 ms per request** (271–637 loaded components, scope 157–393); with the *entire*
  application force-loaded — 49 080 components, 1 686 providers — it is 8.9 ms. Real requests on
  the same app take 1.1–18.6 s (`tholos.access.log`), so the analysis is ~0.001% of a request and
  is **not** worth caching. The win of `Optimized` is the DB work avoided: 3.7 providers open per
  action on average (max 28) across 530 actions, against the 1 686 that `Always` opens.

## 7. Edge cases & caveats

- **Provider physically inside the render subtree, but unused:** present under the render root
  yet referenced by nothing external → **not** opened. Correct (this is the optimization).
- **Self-references:** a provider's own `TDBParam`/`TQueryFilter` pointing at its own (or a
  sibling field within the same provider) does **not** self-trigger it — the `!isset($dpSubtree[$refId])`
  exclusion handles this.
- **Component-reference inter-provider deps:** if provider A's param references provider B's
  field **via a `COMPONENT` property**, and A's param is within the render subtree, B **is**
  detected and opened. Only **non-component** (global-parameter) couplings are missed (§6).
- **Id typing:** component ids are compared as **strings** throughout the new helpers to match
  existing `(string)` comparisons in `render()` and the `refComponents` map.
- **Named partial not found:** `getRenderRootId()` falls back to `action_id`; `run()` still
  throws during render, so page behavior is unchanged.
- **Render indirections *(2026-08-04)*:** components that render a non-descendant target pull that
  target's subtree into the scope (§5.2b). A **partial** render stays narrow: the closure only
  fires for indirections that are themselves inside the partial, so `TAction.Page` is not followed
  unless the `TAction` is in scope.
- **Components verified as *not* indirect:** `TPartial` (`parent::render()`), `TCacheArrayItem`
  (`findChildIDsByType($this, …)`), `TGrid` and `TIterator` (own children) render only their own
  descendants; `TCacheArrayRenderer` renders no components at all — it concatenates
  pre-serialized cached HTML. None of them needs a `$renderReferences` entry.
- **`REDIRECT` path:** `autoOpen()` is not reached (it is in the non-redirect branch) — no change.

## 8. Verification (no automated test suite in this repo)

`Base/CLAUDE.md`: "There is no test suite or linter configured in this repository." Because the
logic lives in private methods on the `final` `TholosApplication` (an Eisodos `Singleton` that
cannot be instantiated in isolation), there is no standalone unit harness; verification is
manual, driven by the existing trace/debug logs.

**Syntax:** `php -l src/Tholos/TholosApplication.php` → `No syntax errors detected`.

**Behavioral scenarios** — render a narrow **named partial** (render root narrower than the
action) and read the `Auto opening dataproviders` / per-provider trace logs to confirm which
providers opened, per mode:

- `Always` (or config unset): all eligible providers open (baseline unchanged).
- `Disabled`: `AutoOpen disabled …` logged; no provider opens in `autoOpen()` (on-demand opens
  such as `TGrid::renderDetails()` still occur).
- `Optimized`: only directly-called / referenced providers open; skipped ones log
  `AutoOpen skipped (Optimized …)`. Concretely confirm: (a) a directly-called provider (render
  root) opens; (b) a provider whose field is bound by a control **inside** the partial opens;
  (c) a provider referenced only from **outside** the partial is skipped; (d) a provider present
  in the tree but unreferenced is skipped; (e) a provider whose only reference is internal
  (self-reference) is skipped; (f) a provider with `AutoOpenAllowed=false` never opens (enforced
  by the provider's own gate, independent of this logic).

**Render-indirection scenarios** *(added 2026-08-04)* — these are the ones the original
verification missed, because a full-page render was never exercised in `Optimized`:

- **`TPDFPage` route:** render a PDF action (e.g. `PRINT_RENTAL/rental_close`) and confirm the
  providers behind the page *header* open — a provider that is not a `TIterator`/`TGrid`
  `ListSource` is the signal, since those open themselves regardless of this logic.
- **`TLinkedComponent` route:** render an action whose content comes through a
  `TLinkedComponent` pointing at a container elsewhere (e.g. `SAL_SERVICE_RENTALS/index`) and
  confirm the providers referenced from *inside that container* open — lookup/lov providers are
  the visible symptom, as empty dropdowns.
- **Partial render, unchanged:** a named-partial render must still open only what the partial
  needs — the closure must not widen it through the containing action's `Page`.

## 9. Out of scope

- Transitive dependency closure across **providers** (explicitly rejected). The render-scope
  closure of §5.2b is a different thing: it determines what the page renders, not which provider
  feeds which.
- Any force/always-open escape-hatch property (explicitly rejected).
- Detecting global-parameter / template-function / event couplings.
- Changes to `TGrid`/`TMapSource`/`TRoleManager`/`TQueryGroup` on-demand opening.
- Frontend (JS/template) changes.

## 10. Resolved decisions

- **Missing/undetected providers in `Optimized` (2026-07-21):** **pure reference detection** —
  no transitive closure, no escape-hatch property. Global-parameter-fed chains are the
  developer's responsibility to restructure.
- **Default Config value (2026-07-21):** **`Always`** (backward compatible); `Optimized` is
  opt-in per deployment/action.
- **Code location (2026-07-21):** the decision logic lives as **private methods on
  `TholosApplication`** (`getRenderRootId`, `collectSubtreeIds`, `collectRenderScopeIds`,
  `shouldAutoOpenOptimized`), not in a separate class — only component types have their own PHP
  file in this codebase.
- **Render scope vs. render root (2026-08-04):** `getRenderRootId()` is **not** changed to widen
  the scope, because `run()` uses it for the actual render dispatch; the widening lives entirely
  in `collectRenderScopeIds()`.
- **Scope caching (2026-08-04):** **rejected.** A file-based per-entry-point cache of the scope /
  provider ids under `Tholos.CacheDir` was considered and dropped after measurement — the analysis
  it would save is ~0.1 ms (§6), i.e. below the cost of the `stat` + `include` needed to read the
  cache. If per-request time is the target, the init phase (1.4–2.5 s: `.tcd` loading,
  `json_decode`, component instantiation) is where the time actually is.

## 11. Documentation

- This design lives in `Base/docs/plans/` (per `Base/CLAUDE.md`).
- On implementation, document the `Tholos.AutoOpenDataProvider` config key and the three-mode
  semantics alongside the other `Tholos.*` config parameters.

## 12. Amendments

### 2026-08-04 — the render root is not what gets rendered

`Optimized` was switched on in the greengo backoffice and the `PRINT_RENTAL/rental_close` PDF came
out empty apart from the sections fed by a `TIterator`. Every provider logged
`AutoOpen skipped (Optimized, not referenced in render subtree)`, including `qRentalHead` — the
source of 16 labels and of the `HAS_*` flags gating whole sections.

Root cause: §2.2's assumption. `TPDFPage::init()` makes itself the render root but has **no
children**, so the scope was a set of size 1 and no reference could ever be found. Two further
indirections had the same defect (`TAction.Page`, `TLinkedComponent.Component`) — on
`SAL_SERVICE_RENTALS/index` the latter hid all six of the route's providers, so that route opened
nothing at all.

Fixes, in order:

| version | change |
|---|---|
| 1.0.19 | `TIterator::init()` sets `AutoOpenAllowed='false'` on its `ListSource` (§6) — unrelated double-open, found while investigating |
| 1.0.20 | seeded the action subtree when a renderer is set (`TPDFPage` only) — superseded |
| 1.0.21 | `collectRenderScopeIds()` (§5.2b): transitive closure over render indirections, absorbing 1.0.20 |
| 1.0.24 | `AutoOpenAlwaysRequired` property on `TDataProvider`: side-effect providers opt out of the optimization; defaults to `true` on `TFileProcessor` |

Measured effect on the compiled greengo backoffice definitions:

| route/action | scope | providers opened |
|---|---|---|
| `PRINT_RENTAL/rental_close` (`TPDFPage`) | 1 → 157 | 0 → 9 |
| `SAL_SERVICE_RENTALS/index` (`TLinkedComponent` → `rowGrid`) | 11 → 393 | 0 → 6 |
| `HR_EMPLOYEES/index` (`TLinkedComponent` → `cGrid`) | 157 → 412 | 5 → 9 |
| `PUB_CLEANING_STATUS/index` (`TLinkedComponent` → `rowTransactions`) | 57 → 282 | 3 → 5 |

No provider is dropped on any of the 530 actions, and the mode keeps its value (§6).

**Lesson for future changes:** the parent/child tree describes *containment*, not *rendering*.
Any new component that renders a target it does not own has to be added to `$renderReferences`
(§5.2b), or the providers under that target silently stop opening. The failure mode is a blank
region on the page — no error, no warning, only a `AutoOpen skipped (Optimized …)` trace line.

### 1.0.24 — providers the reference analysis can never see

`TFileProcessor` broke on `upload/standard/fpStandard` (greengo backoffice): the upload completed
in the browser but no file was ever moved. The reference analysis was working exactly as designed
— there simply was nothing to find:

- the request is a 3-segment URL, and the rewrite rule only sets `tholos_partial` on the
  4-segment form, so `tholos_partial` is empty and the render root is the **action**, not the
  file processor — the direct-call branch of `shouldAutoOpenOptimized()` never fires;
- the only component referencing `fpStandard` is a `TFileUpload` control in a *different* route
  (`COR_ATTACHMENTS`), whose definition is not even loaded on the upload request.

The scope walk is a question about **data**: "does anything being rendered need this provider's
result?" `TFileProcessor` answers no and is still required, because it exists for its side effect
on `$_FILES`. No amount of reference tracing can discover a trigger that lives in the HTTP request
rather than in the component graph.

Hence the **`AutoOpenAlwaysRequired`** boolean property, declared on `TDataProvider` and inherited
by every provider. `autoOpen()` consults it before the Optimized decision; the PHP fallback is
`'false'`, so the effective value comes from the component definition — `false` everywhere except
`TFileProcessor`, whose definition defaults it to `true`.

Keeping this in the definition rather than in a PHP override means an individual component can be
opted in from the `.tcd` without touching the framework: any provider that runs for a
request-level side effect (an import, a webhook sink) sets the property instead of subclassing.

> **Deployment note.** The value is read from the compiled definition, so a `.tcd` compiled before
> the property existed yields the `'false'` fallback and the provider is skipped again. Recompile
> the definitions when rolling this out.
