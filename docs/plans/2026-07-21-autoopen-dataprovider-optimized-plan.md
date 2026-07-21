# Config-driven `TDataProvider` auto-open (`Tholos.AutoOpenDataProvider`) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a `Tholos.AutoOpenDataProvider` config parameter (`Always` / `Disabled` / `Optimized`) that lets the page-generation auto-open phase open only the `TDataProvider` components the render actually needs.

**Architecture:** All new logic lives as **private methods on `TholosApplication`** (no new class — only component types have their own PHP file). `getRenderRootId()` resolves the render root; `collectSubtreeIds()` returns a component's subtree id-set; `shouldAutoOpenOptimized()` decides per provider using the existing `$refComponents` reverse index. `autoOpen()` branches on the config mode: `Always` keeps the current loop, `Disabled` returns immediately, `Optimized` opens only directly-called or referenced providers.

**Tech Stack:** PHP ≥ 8.4, PSR-0 (`namespace Tholos;`), Eisodos parameter handler.

**Design doc:** `Base/docs/plans/2026-07-21-autoopen-dataprovider-optimized-design.md`

## Global Constraints

- **PHP:** `>= 8.4`; typed properties and `?array` allowed.
- **No new class:** the decision logic goes **directly into `src/Tholos/TholosApplication.php`** as private methods — do not create a separate class/file. (`TholosApplication` is `final class TholosApplication extends Singleton`.)
- **Indentation (house style):** method declarations at 4 spaces, method bodies at 6 spaces; private properties at 4 spaces (match the surrounding file).
- **Config read:** `Eisodos::$parameterHandler->getParam('Tholos.AutoOpenDataProvider', 'Always')`. Unknown values are treated as `Always`.
- **Default behavior:** unset config ⇒ `Always` ⇒ **identical to today**. `Optimized` is opt-in.
- **Detection rule (Optimized):** a provider opens iff `AutoOpenAllowed=true` **and** (it *is* the render root, or a component inside the render subtree — but outside the provider's own subtree — references the provider or any descendant). `AutoOpenAllowed` and the `TQuery`/`TExternalDataProvider` "has `TDBfield` child" gates stay **inside** each provider's own `autoOpen()` and are unchanged.
- **No test framework in repo** (`Base/CLAUDE.md`); the class is a `final` Singleton not instantiable in isolation. Verification is `php -l` + manual trace-log inspection across the three modes.
- **All git commands run from the `Base/` directory** (the git repo root). Repo is on `main`; **create a feature branch first** (Task 1, Step 1).
- **Namespace note:** everything is in `namespace Tholos;`; `Eisodos`, `Tholos`, `TComponent`, `RuntimeException` are already imported/in-namespace — **no new `use` statements needed**.

---

### Task 1: Add the config-driven auto-open logic to `TholosApplication`

**Files:**
- Modify: `Base/src/Tholos/TholosApplication.php`
  - Add private field `$childIndex` after `$cacheFindChildIDsByType` (`:204`).
  - Add private methods `getRenderRootId()`, `collectSubtreeIds()`, `shouldAutoOpenOptimized()` immediately above `autoOpen()` (`~:822`).
  - Replace the body of `autoOpen()` (`:825–831`).

**Interfaces:**
- Consumes (existing members): `$this->components` (`:188`), `$this->refComponents` (`:197`), `$this->renderer`, `$this->action_id` (`:87`), `findComponentsByClass()`, `findComponentId()`, `findComponentByID()`, `findChildByName()`, `findReferencingComponents()`.
- Produces (private):
  - `getRenderRootId(): int`
  - `collectSubtreeIds(string $root_): array` — `id(string) => true` set of `{root} ∪ descendants`.
  - `shouldAutoOpenOptimized(TComponent $dp_, string $renderRoot_, array $renderSubtree_): bool`

- [ ] **Step 1: Create the feature branch**

Run (from `Base/`):
```bash
git checkout -b feature/autoopen-dataprovider-optimized
```
Expected: `Switched to a new branch 'feature/autoopen-dataprovider-optimized'`

- [ ] **Step 2: Add the `$childIndex` private field**

In `Base/src/Tholos/TholosApplication.php`, immediately after this existing declaration (`:204`):
```php
    private array $cacheFindChildIDsByType = array();
```
insert:
```php
    
    /** @var array<string,array>|null parent_id (string) => list<child_id (string)>, built once for Optimized auto-open */
    private ?array $childIndex = NULL;
```

- [ ] **Step 3: Insert the three private helper methods above `autoOpen()`**

In `Base/src/Tholos/TholosApplication.php`, immediately **above** the docblock of the existing
`autoOpen()` method (the block whose docstring is `Auto opens TDataProvider components before the
rendering phase`, `~:822`), insert:
```php
    /**
     * Resolves the id of the render root (render point), mirroring run()'s render dispatch.
     * Falls back to action_id when a named partial is not found (run() throws during render in
     * that case, so the fallback is never actually rendered).
     *
     * @return int
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

      return $partial !== NULL ? (int)$this->findComponentId($partial) : $this->action_id;
    }

    /**
     * Returns { $root_ } ∪ all descendants as a set (string id => true) for O(1) membership.
     * The parent->children index is built once per request and cached in $this->childIndex.
     *
     * @param string $root_
     * @return array<string,true>
     */
    private function collectSubtreeIds(string $root_): array {
      if ($this->childIndex === NULL) {
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

    /**
     * Optimized-mode decision for a single provider: true iff it is the render root (direct call)
     * OR some component inside the render subtree — but outside the provider's own subtree —
     * references the provider or any of its descendant fields.
     *
     * @param TComponent $dp_
     * @param string $renderRoot_
     * @param array $renderSubtree_ Set (string id => true) of the render root's subtree
     * @return bool
     */
    private function shouldAutoOpenOptimized(TComponent $dp_, string $renderRoot_, array $renderSubtree_): bool {
      $dpId = (string)$this->findComponentId($dp_);

      if ($dpId === $renderRoot_) {
        return true; // direct call: the render root IS the provider
      }

      $dpSubtree = $this->collectSubtreeIds($dpId); // provider + its descendant fields
      foreach (array_keys($dpSubtree) as $triggerId) {
        foreach ($this->findReferencingComponents((string)$triggerId) as $refId) {
          $refId = (string)$refId;
          if (isset($renderSubtree_[$refId]) && !isset($dpSubtree[$refId])) {
            return true; // referenced from within the render subtree, from outside the provider
          }
        }
      }

      return false;
    }
```

> Id typing: PHP normalises numeric-string array keys to integers on both store and lookup, so
> mixing `'11'` and `11` resolves consistently; the `(string)` casts keep intent explicit.

- [ ] **Step 4: Replace the body of `autoOpen()`**

Replace this exact current method (`:825–831`):
```php
    private function autoOpen(): void {
      
      foreach ($this->findComponentsByClass('TDataProvider') as $object) {
        /* @var TDataProvider $object */
        $object->autoOpen();
      }
    }
```
with:
```php
    private function autoOpen(): void {

      $mode = Eisodos::$parameterHandler->getParam('Tholos.AutoOpenDataProvider', 'Always');

      if ($mode === 'Disabled') {
        Tholos::$logger->debug('AutoOpen disabled (Tholos.AutoOpenDataProvider=Disabled)');

        return;
      }

      if ($mode !== 'Optimized') { // 'Always' or any unknown value -> legacy behavior
        foreach ($this->findComponentsByClass('TDataProvider') as $object) {
          /* @var TDataProvider $object */
          $object->autoOpen();
        }

        return;
      }

      // Optimized: open a provider only if directly called or referenced from the render subtree.
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

- [ ] **Step 5: Lint the modified file**

Run (from `Base/`):
```bash
php -l src/Tholos/TholosApplication.php
```
Expected: `No syntax errors detected in src/Tholos/TholosApplication.php`.

- [ ] **Step 6: Manual verification (all three modes)**

No automated harness exists (`Base/CLAUDE.md`; `TholosApplication` is a `final` Singleton).
Verify against a real application that renders a **named partial** (render root narrower than the
action), using the existing trace/debug logs:

1. **`Always`** (or config unset): log shows `Auto opening dataproviders`; every eligible provider
   opens exactly as before this change (baseline unchanged).
2. **`Disabled`** (`Tholos.AutoOpenDataProvider=Disabled`): log
   `AutoOpen disabled (Tholos.AutoOpenDataProvider=Disabled)` appears; **no** provider is
   auto-opened in this phase (on-demand opens such as `TGrid::renderDetails()` still occur).
3. **`Optimized`** (`Tholos.AutoOpenDataProvider=Optimized`): only providers that are directly
   called or referenced from within the rendered partial open; skipped ones log
   `AutoOpen skipped (Optimized, not referenced in render subtree)`. Confirm a provider whose
   field is bound by a control **inside** the partial **does** open, and one referenced only from
   **outside** the partial is skipped.

Record which providers opened per mode in the PR/description.

- [ ] **Step 7: Commit**

Run (from `Base/`):
```bash
git add src/Tholos/TholosApplication.php
git commit -m "feat(autoopen): config-driven autoOpen (Always/Disabled/Optimized)

Adds Tholos.AutoOpenDataProvider and Optimized reference-aware auto-open as
private methods on TholosApplication.

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Document the config parameter in README

**Files:**
- Modify: `Base/README.md` — the `## Configuration Parameters` section (`:563`), after the `### Application` subsection (`:571–573`).

**Interfaces:**
- Consumes: nothing.
- Produces: user-facing documentation of `Tholos.AutoOpenDataProvider`.

- [ ] **Step 1: Add a `### Data Providers` subsection**

In `Base/README.md`, immediately after the `### Application` table (the line
`| `Tholos.AccessLog.Format` | Log format (use `%` as variable prefix) |` at `:573`) and the
blank line after it, insert:
```markdown
### Data Providers

| Parameter | Default | Description |
|-----------|---------|-------------|
| `Tholos.AutoOpenDataProvider` | `Always` | Page-generation auto-open mode for `TDataProvider` components. `Always`: auto-open every eligible provider (legacy). `Disabled`: never auto-open in the page-generation phase. `Optimized`: auto-open only providers that are directly called (render root) or referenced from within the render subtree. In `Optimized` mode, providers coupled only via global parameters / template functions are not detected. |
```

- [ ] **Step 2: Verify the insertion**

Run (from `Base/`):
```bash
grep -n 'Tholos.AutoOpenDataProvider' README.md
```
Expected: one match showing the new row.

- [ ] **Step 3: Commit**

Run (from `Base/`):
```bash
git add README.md
git commit -m "docs: document Tholos.AutoOpenDataProvider config parameter

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 3 (OPTIONAL): Behavior-preserving `run()` refactor to reuse `getRenderRootId()`

> Optional and isolable — the feature is complete after Task 2. This only removes the duplicated
> render-root computation between `autoOpen()` and `run()`. Skip if minimizing churn.

**Files:**
- Modify: `Base/src/Tholos/TholosApplication.php` — the render dispatch inside `run()` (`~:1069–1090`).

**Interfaces:**
- Consumes: `getRenderRootId()` (Task 1).
- Produces: `run()` computes the render root via the shared helper.

- [ ] **Step 1: Refactor the render dispatch**

Locate the render dispatch in `run()` (the block starting `if (isset($this->renderer)) {` just
after `$this->autoOpen();`). Replace it with the following, which uses `getRenderRootId()` while
**preserving** the named-partial `RuntimeException`, the `responseType=JSON` default for partials,
and head/foot wrapping for both `*` and named partials:
```php
          $renderRoot = $this->getRenderRootId();

          if (isset($this->renderer)) {
            Tholos::$logger->debug('Render phase started', $this->renderer);
            $response_ = $this->render(NULL, $renderRoot);
            Tholos::$logger->debug('Render phase finished');
          } else if (Eisodos::$parameterHandler->eq('tholos_partial', '')) {
            Tholos::$logger->debug('Render phase started');
            $response_ = $this->render(NULL, $renderRoot);
            Tholos::$logger->debug('Render phase finished');
          } else {
            Tholos::$logger->debug('Partial render phase started');
            if (Eisodos::$parameterHandler->eq('responseType', '')) {
              Eisodos::$parameterHandler->setParam('responseType', 'JSON');
            }
            if (Eisodos::$parameterHandler->neq('tholos_partial', '*')
              && $this->findChildByName($this->findComponentByID($this->action_id),
                   Eisodos::$parameterHandler->getParam('tholos_partial')) === NULL) {
              throw new RuntimeException('No partial (' . Eisodos::$parameterHandler->getParam('tholos_partial') . ') found in action!');
            }
            $response_ = $this->render(NULL, $renderRoot);
            $response_ = implode("\n", Tholos::$app->getHeadItems()) . $response_ . implode("\n", Tholos::$app->getFootItems());
            Tholos::$logger->debug('Partial render phase finished');
          }
```

- [ ] **Step 2: Lint**

Run (from `Base/`):
```bash
php -l src/Tholos/TholosApplication.php
```
Expected: `No syntax errors detected`.

- [ ] **Step 3: Manual verification (regression)**

Confirm all render paths are unchanged: (a) `renderer` set → renders renderer id; (b) no partial
→ renders `action_id`; (c) `tholos_partial=*` → renders `action_id` with head/foot wrapping and
`responseType=JSON`; (d) named partial present → renders the partial id with wrapping; (e) named
partial **absent** → `RuntimeException('No partial (…) found in action!')`.

- [ ] **Step 4: Commit**

Run (from `Base/`):
```bash
git add src/Tholos/TholosApplication.php
git commit -m "refactor: run() reuses getRenderRootId() for render-root resolution

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Self-Review

**1. Spec coverage**

| Spec section | Task |
|---|---|
| §3/§4 Config `Always`/`Disabled`/`Optimized`, default `Always`, unknown→Always | Task 1, Step 4 |
| §3 Code location = private methods on `TholosApplication` (no new class) | Task 1 (whole) |
| §5.1 `getRenderRootId()` | Task 1, Step 3 |
| §5.2 `collectSubtreeIds()` + `$childIndex` field | Task 1, Steps 2–3 |
| §5.3 `shouldAutoOpenOptimized()` predicate | Task 1, Step 3 |
| §5.4 rewritten `autoOpen()` | Task 1, Step 4 |
| §5.5 optional `run()` refactor | Task 3 |
| §8 verification (`php -l` + manual, three modes, scenarios a–f) | Task 1, Steps 5–6 |
| §11 documentation | Task 2 |

Spec §8 case (f) `AutoOpenAllowed=false ⇒ skip` is enforced inside each provider's own
`autoOpen()` (unchanged code), not by `shouldAutoOpenOptimized()` — verified by construction and
manual check, not a separate unit.

**2. Placeholder scan:** none — every code and command step is concrete.

**3. Type consistency:** `getRenderRootId(): int`, `collectSubtreeIds(string): array`,
`shouldAutoOpenOptimized(TComponent, string, array): bool` are defined in Task 1, Step 3 and
consumed in Task 1, Step 4 (and `getRenderRootId()` again in Task 3, Step 1). The `$childIndex`
field (Task 1, Step 2) is used only by `collectSubtreeIds()`. Names match across steps/tasks.
