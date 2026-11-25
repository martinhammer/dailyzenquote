# Backlog

Open work for this project, including where it deviates from the shared skills
(the `nextcloud` plugin and `~/Code/claude-config/skills`). Not loaded at session
start. One line per item: date, the skill it concerns, what to do.

## Deviations from shared skills
- 2026-11-24 · eslint-v9-migration · Still on `@nextcloud/eslint-config` 8 with `.eslintrc.cjs`, base `@nextcloud` config; type syntax is kept out of `.vue` files as a workaround, which the migration makes unnecessary. Migrate to v9 (flat config).
- 2026-11-25 · dashboard-widget · `src/widget.ts` imports Vue and `Widget.vue` eagerly, so every dashboard visit loads the whole widget even for users who never added it; register only and `import()` the widget inside the callback. It also does not unmount the previous instance when the widget is re-added.
- 2026-11-25 · app-project-files · `package.json` has no `ts:check` (`vue-tsc --noEmit`) script, which the shared Makefile's `make lint` and the CI template call; add it (with `typecheck` as an alias if wanted).
