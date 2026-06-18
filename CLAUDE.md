# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Daily Zen Quote — a Nextcloud app that shows, on a Dashboard widget, either an inspirational quote of the day or "On This Day" historical entries (Events / Births / Deaths) from the ZenQuotes.io APIs. Each user chooses the content via personal settings. Targets Nextcloud 32-34, PHP 8.1+, Node 24+.

The app source lives at the repository root (the standard Nextcloud app layout).

### Widget content modes

Per-user preference stored as the `mode` user value (`IConfig::getUserValue`/`setUserValue`, default `quote`). The four modes are defined as constants on `Application` (`MODE_QUOTE`, `MODE_EVENTS`, `MODE_BIRTHS_DEATHS`, `MODE_ALL`, plus `MODES`/`DEFAULT_MODE`):

- **quote** — the ZenQuotes quote of the day (original behaviour).
- **events** — On This Day → `Events`.
- **births_deaths** — On This Day → `Births` + `Deaths`.
- **all** — On This Day → `Events` + `Births` + `Deaths`.

## Architecture

### Backend (PHP)

- **`lib/AppInfo/Application.php`** — App bootstrap; registers the dashboard widget; holds the mode constants.
- **`lib/Dashboard/ZenQuoteWidget.php`** — Dashboard widget. `getTitle()` reads the user's mode and returns "Daily Zen Quote" (quote mode) or "On This Day" (any On-This-Day mode). `load()` passes the mode to the frontend via `IInitialState` so no extra HTTP round-trip is needed.
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
- **Per-user config:** use `OCP\Config\IUserConfig` (`getValueString`/`setValueString`), NOT the deprecated `OCP\IConfig::getUserValue`/`setUserValue` — the latter passes psalm on stable32 but fails the `DeprecatedMethod` check on the stable33/34 psalm matrix.
- **Widget full-bleed CSS:** `Widget.vue`'s `.widget` uses `width: calc(100% + 56px); margin-inline: -28px` to bleed the carousel arrows out into the dashboard panel's padding toward the box edge. The `28px`/`56px` pair is the tuning knob and must stay in sync (`width: calc(100% + 2 * margin)`).

## User Instructions
The owner does not want Claude to run any build commands.
The owner does not want Claude to run any git commands.
