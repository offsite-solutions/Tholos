# Config-driven `TDataProvider` auto-open (`Tholos.AutoOpenDataProvider`) — Design

**Date:** 2026-07-21
**Status:** Approved (design)
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
so the render subtree is derivable by walking `parent_id`. `findChildIDsByType()`
(`TholosApplication.php:670`) only returns **direct** typed children; there is no generic
recursive descendant helper yet.

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
| Detection strategy | **Pure reference detection** — no transitive closure, **no** force/escape-hatch property |
| "children" scope | **Recursive descendants** of the provider (not just direct `TDBfield`) |
| Self-reference | A referencing component **inside the provider's own subtree does not count** (must be external to `DP` yet within the render subtree) |
| Render-subtree evaluation | **Static structural** membership (`parent_id` tree) at auto-open time — independent of runtime role/visibility decisions (conservative: opens more, never fewer) |
| `AutoOpenAllowed` semantics | Still required in every mode; in `Optimized` it is **necessary but no longer sufficient** |
| Code location | **Private methods on `TholosApplication`** — no new class (only component types have their own PHP file in this codebase) |

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
  $renderSubtree = $this->collectSubtreeIds($renderRoot);
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
- **Unaffected regardless of mode:** `TGrid`, `TMapSource`, `TRoleManager`, `TQueryGroup` —
  they already set their source's `AutoOpenAllowed='false'` in `init()` and open on demand.
- **Performance (`Optimized`):** one `O(N)` parent→children index build + one `O(N)` render
  subtree walk, plus per-provider descendant collection and `refComponents` lookups. Net win
  when many providers exist but few are referenced under a narrow (partial) render point; small
  overhead in the worst case (render root = action, most providers referenced).

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

## 9. Out of scope

- Transitive dependency closure across providers (explicitly rejected).
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
  `TholosApplication`** (`getRenderRootId`, `collectSubtreeIds`, `shouldAutoOpenOptimized`), not
  in a separate class — only component types have their own PHP file in this codebase.

## 11. Documentation

- This design lives in `Base/docs/plans/` (per `Base/CLAUDE.md`).
- On implementation, document the `Tholos.AutoOpenDataProvider` config key and the three-mode
  semantics alongside the other `Tholos.*` config parameters.
