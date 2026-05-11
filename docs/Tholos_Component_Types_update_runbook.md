# Tholos Component Types — Update Runbook

When a new `Base/docs/Tholos_Component_Types-<timestamp>.html` extract arrives, follow this procedure to keep the markdown reference and project memory in sync.

## Inputs

- **Source of truth:** the newest timestamped HTML in `Base/docs/Tholos_Component_Types-*.html` (e.g. `Tholos_Component_Types-20260511111444.html`).
- **Target file:** `Base/docs/Tholos_Component_Types.md` (consumed by Claude and by humans; referenced from `Base/CLAUDE.md`).
- **Required tool:** `pandoc` (any version ≥ 3.x). Output flavour is GFM.

## Conventions

- The markdown file has a **release-history block** at the head, delimited by HTML comment markers:

  ```markdown
  <!-- RELEASE HISTORY START -->
  ## Release history
  - **YYYY-MM-DD** — `Tholos_Component_Types-<timestamp>.html`
    - Bullet of each change (new property, new method, removed member, default-value update, …)
  <!-- RELEASE HISTORY END -->
  ```

  The block is **manually maintained**. The body below it is regenerated mechanically by pandoc and must never be hand-edited (manual edits get clobbered on the next refresh).

- The release-history block sits **above** the pandoc-generated body. Newest entry first.

## Procedure

### 1. Locate the newest extract

```bash
NEW_HTML=$(ls -t Base/docs/Tholos_Component_Types-*.html | head -1)
PREV_HTML=$(ls -t Base/docs/Tholos_Component_Types-*.html | sed -n '2p')
echo "new=$NEW_HTML  prev=$PREV_HTML"
```

### 2. Preserve the existing release-history block

```bash
awk '/<!-- RELEASE HISTORY START -->/,/<!-- RELEASE HISTORY END -->/' \
    Base/docs/Tholos_Component_Types.md > /tmp/release_history.md
```

If the file does not yet contain a release-history block (first run), skip this step and bootstrap one in step 5.

### 3. Regenerate the body from the new HTML

```bash
pandoc -f html -t gfm "$NEW_HTML" -o /tmp/body.md
```

GFM is the chosen flavour because it preserves the raw `<div>` wrappers and `\` hard breaks the upstream HTML relies on. Do **not** use plain `markdown` output (it produces pandoc-specific fenced divs that diverge from prior versions).

### 4. Compute the human-readable diff

```bash
pandoc -f html -t gfm "$PREV_HTML" -o /tmp/prev_body.md
diff -u /tmp/prev_body.md /tmp/body.md > /tmp/extract.diff
```

Inspect `/tmp/extract.diff` and classify the changes. The change set typically falls into one of these buckets:

| Bucket | What it looks like in the diff |
|---|---|
| New component type | New `## T<Foo>` heading appears |
| Removed component type | `## T<Foo>` heading disappears |
| New property | New `**<PropName>**` row inside a `<Type> properties` section |
| New method | New `**<methodName>()**` row inside a `<Type> methods` section |
| New event | New `**on<Event>**` row inside a `<Type> events` section |
| Default-value change | A previously-empty default cell is now filled |
| Ancestor / parent change | The "Parent must be:" line changed, or the ancestor-chain link list changed |

Summarise each bucket in 1 bullet per concrete change. Group by component type.

### 5. Compose the new `.md`

Order: title heading → release-history block (with new entry on top) → blank line → pandoc body (minus the duplicate title pandoc puts on line 1).

```bash
# Strip pandoc's title line; we keep our own
tail -n +2 /tmp/body.md > /tmp/body_notitle.md

# Build the new file
{
  echo '# Tholos :: Component Type documentation'
  echo
  echo '<!-- RELEASE HISTORY START -->'
  echo '## Release history'
  echo
  echo "- **$(date +%Y-%m-%d)** — \`$(basename "$NEW_HTML")\`"
  # ... emit one bullet per change, indented two spaces ...
  echo
  # append prior history entries (skip the START/END markers + the "## Release history" line)
  if [ -f /tmp/release_history.md ]; then
    sed -n '/^- \*\*/,$p' /tmp/release_history.md \
      | sed '/<!-- RELEASE HISTORY END -->/d'
  fi
  echo '<!-- RELEASE HISTORY END -->'
  echo
  cat /tmp/body_notitle.md
} > Base/docs/Tholos_Component_Types.md
```

In an interactive session, the assistant assembles the new file in memory and writes it with the `Write` tool rather than shelling out — same result, easier to audit.

### 6. Sanity-check the regeneration

```bash
# Type count should match the HTML's heading count
grep -c '^## T' Base/docs/Tholos_Component_Types.md

# Confirm release-history markers are present exactly once
grep -c 'RELEASE HISTORY START\|RELEASE HISTORY END' Base/docs/Tholos_Component_Types.md   # expect 2

# Confirm no pandoc title duplication
head -2 Base/docs/Tholos_Component_Types.md
```

### 7. Update codebase-memory ADR

Re-index the project so the kernel knows the file changed:

```
mcp__codebase-memory-mcp__index_repository(repo_path="/Users/baxi/Work/_tholos", mode="full")
```

Then update ADR sections **only if** one of these holds:

- **Type count changed** → update the count in `## STACK` (the "84 component types" figure).
- **A new declarative-subtype family appeared** (e.g. a new set of TFormControl-like subtypes) → update the family list in `## PATTERNS` ("Two-tier component-type model" section).
- **A new top-level component category appeared** (rare) → consider whether `## ARCHITECTURE`'s layering bullets need a new entry.

Use `mcp__codebase-memory-mcp__manage_adr(mode="update", sections=[...])`. If none of the above hold, the ADR does not need touching — per-component property/method changes do not warrant ADR edits; they live in the .md.

### 8. Update Claude memory if needed

Edit `~/.claude/projects/-Users-baxi-Work--tholos/memory/project_tholos_framework.md` and the `MEMORY.md` index entry **only if**:

- the documented-type count changed (the memory currently asserts "84 documented component types"), or
- the path or filename of the canonical extract changed.

Otherwise leave memory alone.

## Notes

- Old timestamped HTMLs are kept on disk as the historical record. Do not delete them.
- The release-history block is the only hand-maintained part of the .md. Treat it as append-only; do not rewrite prior entries.
- If pandoc produces a body that differs cosmetically from prior versions (e.g. wrapped attribute lines on `<div>`s due to a pandoc version bump), accept it — content correctness matters, not byte-identical output.
- The project-level slash command `/cmd-update-tholos-component-types` (defined at `.claude/commands/cmd-update-tholos-component-types.md`) triggers this whole procedure.
