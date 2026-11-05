# Tholos Component Types — Update Runbook

When a new `Base/docs/Tholos_Component_Types-<timestamp>.md` extract arrives, follow this procedure to keep the reference file and project memory in sync.

## Inputs

- **Source of truth:** the newest timestamped Markdown extract, `Base/docs/Tholos_Component_Types-<timestamp>.md`. Download it from Tholos Editor with the toolbar's **Download docs** button.
- **Target file:** `Base/docs/Tholos_Component_Types.md` (consumed by Claude and by humans; referenced from `Base/CLAUDE.md`).
- **Older extracts:** the `Base/docs/Tholos_Component_Types-<timestamp>.html` files came from Tholos Builder before 2026-11-05. They use a different layout and stay on disk only as the historical record.

## Extract format

Tholos Editor generates the extract directly as Markdown; no conversion is needed. The file contains:

- a "How to read this file" section, then three trees as nested lists: inheritance, application structure, visual component structure;
- one `## T<Type>` section per component type with `- Inheritance:`, `- Parent must be:` and `- Description:` lines;
- up to four tables per type: `### Properties`, `### Inherited property overrides`, `### Events`, `### Methods`. Empty tables are omitted.

Each type lists **only its own members**. A type also has every member of the types in its Inheritance line. The overrides table lists the inherited properties whose settings (mandatory, runtime, hidden data, default, disabled) differ from the parent type.

## Conventions

- The target file has a **release-history block** at the head, delimited by HTML comment markers:

  ```markdown
  <!-- RELEASE HISTORY START -->
  ## Release history
  - **YYYY-MM-DD** — `Tholos_Component_Types-<timestamp>.md`
    - Bullet of each change (new property, new method, removed member, default-value update, …)
  <!-- RELEASE HISTORY END -->
  ```

  The block is **manually maintained**. The body below it is the extract copied verbatim and must never be hand-edited (manual edits get clobbered on the next refresh).

- The release-history block sits **above** the body. Newest entry first.

## Procedure

### 1. Locate the newest extracts

```bash
NEW_MD=$(ls -t Base/docs/Tholos_Component_Types-*.md | head -1)
PREV_MD=$(ls -t Base/docs/Tholos_Component_Types-*.md | sed -n '2p')
echo "new=$NEW_MD  prev=$PREV_MD"
```

The glob matches only timestamped extracts, never the target file or this runbook. An empty `PREV_MD` means this is the first Markdown extract; see step 4b.

### 2. Preserve the existing release-history block

```bash
awk '/<!-- RELEASE HISTORY START -->/,/<!-- RELEASE HISTORY END -->/' \
    Base/docs/Tholos_Component_Types.md > /tmp/release_history.md
```

If the file has no release-history block yet, skip this step and bootstrap one in step 5.

### 3. Flatten the new extract

A plain `diff` of two extracts does not show which type a changed table row belongs to. Flatten each extract into sorted `Type :: Section :: line` records first:

```bash
cat > /tmp/flatten.awk <<'AWK'
/^## T/ { type = substr($0, 4); section = "Type"; next }
/^### / { section = substr($0, 5); next }
type != "" && /^- (Inheritance|Parent must be|Description):/ { print type " :: " section " :: " $0; next }
type != "" && /^\| / && !/^\| Name \|/ { print type " :: " section " :: " $0 }
AWK
awk -f /tmp/flatten.awk "$NEW_MD" | sort > /tmp/new_flat.txt
```

### 4a. Compute the diff

```bash
awk -f /tmp/flatten.awk "$PREV_MD" | sort > /tmp/prev_flat.txt
diff /tmp/prev_flat.txt /tmp/new_flat.txt > /tmp/extract.diff
```

Lines starting with `<` exist only in the previous extract, lines starting with `>` only in the new one. A changed row appears as a `<` / `>` pair with the same type, section and name. Classify each change:

| Bucket | What it looks like in the diff |
|---|---|
| New component type | `>` records for a type with no `<` records |
| Removed component type | `<` records for a type with no `>` records |
| New property / event / method | `>` row in that type's `Properties`, `Events` or `Methods` section |
| Removed property / event / method | `<` row with no matching `>` row |
| Changed property | `<` / `>` pair in `Properties` (type, flags, default, values or description changed) |
| New or changed override | `>` row in `Inherited property overrides` |
| Ancestor / parent change | `Inheritance:` or `Parent must be:` line changed |

Write one bullet per concrete change and group the bullets by component type. Record each change once, at the type where the diff shows it: a new property on `TControl` appears only under `TControl`, although every descendant inherits it.

### 4b. First Markdown extract (no `PREV_MD`)

The previous extract is HTML with a different layout, so a row-level diff is not possible. Write a single release entry that records the format change, and compare only the type lists:

```bash
PREV_HTML=$(ls -t Base/docs/Tholos_Component_Types-*.html | head -1)
grep -oE '(id|class)="ctdoc_[A-Za-z]+"' "$PREV_HTML" \
  | sed -E 's/.*ctdoc_//; s/"//' | sort -u > /tmp/prev_types.txt
grep '^## T' "$NEW_MD" | cut -c4- | sort > /tmp/new_types.txt
diff /tmp/prev_types.txt /tmp/new_types.txt
```

Mention any added or removed types in the entry.

### 5. Compose the new `.md`

Order: title heading → release-history block (with the new entry on top) → extract body (minus the extract's own title line, which begins with the "How to read this file" section).

```bash
# Strip the extract's title line; we keep our own
tail -n +2 "$NEW_MD" > /tmp/body_notitle.md

# Build the new file
{
  echo '# Tholos :: Component Type documentation'
  echo
  echo '<!-- RELEASE HISTORY START -->'
  echo '## Release history'
  echo
  echo "- **$(date +%Y-%m-%d)** — \`$(basename "$NEW_MD")\`"
  # ... emit one bullet per change, indented two spaces ...
  # append prior history entries (skip the START/END markers + the "## Release history" line)
  if [ -f /tmp/release_history.md ]; then
    sed -n '/^- \*\*/,$p' /tmp/release_history.md \
      | sed '/<!-- RELEASE HISTORY END -->/d'
  fi
  echo '<!-- RELEASE HISTORY END -->'
  cat /tmp/body_notitle.md
} > Base/docs/Tholos_Component_Types.md
```

In an interactive session, the assistant assembles the new file in memory and writes it with the `Write` tool rather than shelling out — same result, easier to audit.

### 6. Sanity-check the regeneration

```bash
# Type count must match the extract's
grep -c '^## T' "$NEW_MD"
grep -c '^## T' Base/docs/Tholos_Component_Types.md

# Release-history markers must appear exactly once each
grep -c 'RELEASE HISTORY START\|RELEASE HISTORY END' Base/docs/Tholos_Component_Types.md   # expect 2

# The body must be the extract verbatim
diff <(tail -n +2 "$NEW_MD") \
     <(awk 'f; /<!-- RELEASE HISTORY END -->/{f=1}' Base/docs/Tholos_Component_Types.md)   # expect no output
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
- **The reference file's format changed** (as on the first Markdown extract) → update any ADR text that describes how members are listed.

Use `mcp__codebase-memory-mcp__manage_adr(mode="update", sections=[...])`. If none of the above hold, leave the ADR alone — per-component property/method changes live in the .md.

### 8. Update Claude memory if needed

Edit `~/.claude/projects/-Users-baxi-Work--tholos/memory/project_tholos_framework.md` and the `MEMORY.md` index entry **only if**:

- the documented-type count changed (the memory currently asserts "84 documented component types"), or
- the path, filename or format of the canonical extract changed.

Otherwise leave memory alone.

When the format changed, also update the sentence in `Base/CLAUDE.md` that points to `docs/Tholos_Component_Types.md`: name the source extract and say that each type lists only its own members.

## Notes

- Keep old timestamped extracts, both `.html` and `.md`, on disk as the historical record. Do not delete them.
- The release-history block is the only hand-maintained part of the .md. Treat it as append-only; do not rewrite prior entries.
- The project-level slash command `/cmd-update-tholos-component-types` (defined at `.claude/commands/cmd-update-tholos-component-types.md`) triggers this whole procedure.
