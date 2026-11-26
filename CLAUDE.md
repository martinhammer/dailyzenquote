# CLAUDE.md — Daily Zen Quote

## Project Overview

Daily Zen Quote — a Nextcloud app that shows, on a Dashboard widget, either an inspirational quote of the day or "On This Day" historical entries (Events / Births / Deaths) from the ZenQuotes.io APIs. Each user chooses the content via personal settings. Targets Nextcloud 33-36 (`appinfo/info.xml`), PHP 8.2+, Node 24+.

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

## Commands

The Makefile is the shared one (nextcloud `app-project-files` skill): `make lint`,
`make test`, `make psalm`, `make openapi`, `make check`. Release and staging
targets are Martin's. `make lint` needs an npm `ts:check` script, which is still
missing (`docs/backlog.md`).

## Gotchas

Platform gotchas are in the nextcloud skills (php-backend, vue-frontend,
app-icons, dashboard-widget, eslint-v9-migration). Project-specific ones:

- **TypeScript in `.vue` files:** until the ESLint v9 migration (`docs/backlog.md`),
  keep `<script setup lang="ts">` free of TS-only syntax and put types in `.ts`
  files (why: nextcloud `eslint-v9-migration` skill).
- **@nextcloud/vue internal class names:** `Widget.vue` shrinks the carousel arrows with `.dzq-carousel .button-vue { … !important }`. `button-vue` is NcButton's internal class, not public API — re-verify it after any `@nextcloud/vue` major bump, since a rename fails silently (arrows just revert to full width).
- **Widget full-bleed CSS:** only the carousel bleeds, and only when it shows arrows: `Widget.vue` adds `.bleed` (`width: calc(100% + 32px); margin-inline: -16px`) when `hasMultiple`, pushing the arrows out into the panel's padding toward the box edge. The `16px`/`32px` pair is the tuning knob and must stay in sync (`width: calc(100% + 2 * margin)`). Don't put the bleed on `.widget` itself — that once squeezed the quote text to ~4px from the panel edge. The width must exactly offset the negative margins: the widget is a centring flex container, so a negative margin without the matching width is silently cancelled out.
