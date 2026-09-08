# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Daily Zen Quote — a Nextcloud app that shows, on a Dashboard widget, either an inspirational quote of the day or "On This Day" historical entries (Events / Births / Deaths) from the ZenQuotes.io APIs. Each user chooses the content via personal settings. Targets Nextcloud 32-35 (`appinfo/info.xml`), PHP 8.1+, Node 24+.

The app source lives at the repository root (the standard Nextcloud app layout).

### Widget content modes

Per-user preference stored as the `mode` user value (`IUserConfig::getValueString`/`setValueString`, default `quote`). The four modes are defined as constants on `Application` (`MODE_QUOTE`, `MODE_EVENTS`, `MODE_BIRTHS_DEATHS`, `MODE_ALL`, plus `MODES`/`DEFAULT_MODE`):

- **quote** — the ZenQuotes quote of the day (original behaviour).
- **events** — On This Day → `Events`.
- **births_deaths** — On This Day → `Births` + `Deaths`.
- **all** — On This Day → `Events` + `Births` + `Deaths`.

## Architecture

### Backend (PHP)

- **`lib/AppInfo/Application.php`** — App bootstrap; registers the dashboard widget; holds the mode constants.
- **`lib/Dashboard/ZenQuoteWidget.php`** — Dashboard widget; implements `IWidget` + `IIconWidget`. `getTitle()` reads the user's mode and returns "Daily Zen Quote" (quote mode) or "On This Day" (any On-This-Day mode). `load()` passes the mode to the frontend via `IInitialState` so no extra HTTP round-trip is needed.
- **`lib/Service/QuoteService.php`** — Fetches the daily quote from `zenquotes.io/api/today`, distributed cache (TTL 24h, keyed by date). Returns `{quote, author}`.
- **`lib/Service/OnThisDayService.php`** — Mirrors `QuoteService`: fetches `today.zenquotes.io/api/{month}/{day}` (UNPADDED month/day, e.g. `/api/6/18`), distributed cache (TTL 24h, keyed by date), strips HTML/links, drops malformed/blank entries, returns `{events, births, deaths}` of `{text}`. Reuses `QuoteFetchException`.
- **`lib/Controller/ApiController.php`** — OCS endpoints: `GET /quote` → `{quote, author}`; `GET /onthisday` → all three sections (mode-agnostic; the frontend selects/combines per mode).
- **`lib/Controller/SettingsController.php`** — OCS `PUT /settings/mode` (validates against `Application::MODES`, persists per-user).
- **`lib/Settings/PersonalSettings.php` + `PersonalSection.php`** — Personal settings page + section, registered via the `<settings>` block in `appinfo/info.xml`. `getForm()` provides the current mode as initial state and renders `templates/settings.php`.
- **`lib/Service/QuoteFetchException.php`** — Custom exception for upstream API failures (shared by both services).

### Frontend (Vue 3 + TypeScript)

Two Vite entry points (configured in `vite.config.ts`):

- **`src/widget.ts`** → `Widget.vue` — Dashboard widget, registered via `OCA.Dashboard.register()`. Quote mode keeps the single-figure layout; On-This-Day modes render a carousel (left/right arrows, wrap-around, per-entry label lozenge, position counter). The label is hidden in events-only mode (redundant).
- **`src/settings.ts`** → `Settings.vue` — Personal settings page (4-way radio), mounted on `#dailyzenquote-settings`; saves via `PUT /settings/mode`.

The widget uses **`src/composables/useWidgetContent.ts`**: reads `mode` from initial state (`loadState`), fetches `/quote` or `/onthisday`, builds a labelled item list, and **shuffles client-side** (Fisher–Yates) so entry order differs from the API on each load (the backend cache stays deterministic). Exposes `items`, `index`, `currentItem`, `hasMultiple`, `next`/`prev`, `loading`, `error`, `mode`.

### Data Flow

Widget reads its mode from `IInitialState` → composable fetches `/quote` or `/onthisday` (OCS) → `QuoteService` / `OnThisDayService` (cache check → ZenQuotes.io) → response bubbles back; the composable labels + shuffles On-This-Day entries for the carousel.

## Gotchas

- **TypeScript in `.vue` files:** the `@nextcloud` eslint config parses `<script setup lang="ts">` with `@babel/eslint-parser`, which CANNOT parse TS type syntax (type annotations, generics, `import type`). Keep `.vue` scripts free of TS-only syntax; put types in `.ts` files. (esbuild/vite still elides type-only imports, so plain `import { SomeType }` works at build time.)
- **OpenAPI:** new/changed OCS routes require regenerating `openapi.json` (`make openapi`); CI fails if it's out of date.
- **Per-user config:** use `OCP\Config\IUserConfig` (`getValueString`/`setValueString`), NOT the deprecated `OCP\IConfig::getUserValue`/`setUserValue` — the latter passes psalm on stable32 but fails the `DeprecatedMethod` check on the higher branches. (`composer.json` pins `nextcloud/ocp:dev-stable32` locally = the min version; CI derives the psalm matrix from `info.xml`, so every branch up to stable35 is checked.)
- **Toast styles are not auto-injected:** `@nextcloud/dialogs` v7 ships its toast CSS as a separate `@nextcloud/dialogs/style.css` export with CSS-module-hashed class names, and importing `showSuccess`/`showError` does NOT pull it in. Any entry point that shows a toast must `import '@nextcloud/dialogs/style.css'` (see `src/settings.ts`) or the toast renders as unstyled text in the page corner. `@nextcloud/vue` is the opposite — each component self-imports its CSS, so it needs no manual import.
- **Icons must carry no hardcoded colour:** both `img/app.svg` and `img/app-dark.svg` are deliberately fill-less so they inherit `currentColor`. The Active-apps list (`OC_App::getAppInfo()` picks `img/<appid>.svg`, else `img/app.svg`) **inlines** the SVG through `NcIconSvgWrapper`, whose `.icon-vue svg { fill: currentColor }` only overrides a `fill` on the root `<svg>` — a `fill="…"` on a `<path>` wins over that and freezes the icon to one colour in both themes. Never reintroduce per-path `fill`/`stroke`.
- **Dashboard vs. settings icon URLs:** `IIconWidget::getIconUrl()` requires an **absolute** URL (`getAbsoluteURL(imagePath(...))`), `IIconSection::getIcon()` in `PersonalSection` wants the opposite — a bare relative `imagePath()`. Both point at `img/app-dark.svg`, which is loaded as an image (not inlined) and so renders black + gets inverted for dark mode.
- **`js/`+`css/` are disposable:** both are gitignored build output and both are deleted on every build — `js/` by default, `css/` only via `emptyOutputDirectory: { additionalDirectories: ['css'] }` in `vite.config.ts` (without it, hashed chunks from old builds pile up). Never hand-edit or store anything there; source styles belong in `src/`.
- **@nextcloud/vue internal class names:** `Widget.vue` shrinks the carousel arrows with `.dzq-carousel .button-vue { … !important }`. `button-vue` is NcButton's internal class, not public API — re-verify it after any `@nextcloud/vue` major bump, since a rename fails silently (arrows just revert to full width).
- **Widget full-bleed CSS:** `Widget.vue`'s `.widget` uses `width: calc(100% + 56px); margin-inline: -28px` to bleed the carousel arrows out into the dashboard panel's padding toward the box edge. The `28px`/`56px` pair is the tuning knob and must stay in sync (`width: calc(100% + 2 * margin)`).

## User Instructions
The owner does not want Claude to run any build commands.
The owner does not want Claude to run any git commands.
