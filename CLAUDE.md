# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Daily Zen Quote — a Nextcloud app that displays an inspirational quote of the day from the ZenQuotes.io API. It provides both a full-page app view and a Nextcloud Dashboard widget. Targets Nextcloud 31–32, PHP 8.1+, Node 24+.

The app source lives entirely under `app/dailyzenquote/`.

## Architecture

### Backend (PHP)

- **`lib/AppInfo/Application.php`** — App bootstrap; registers the dashboard widget.
- **`lib/Service/QuoteService.php`** — Core logic: fetches the daily quote from `zenquotes.io/api/today` with a distributed cache (TTL 24h, keyed by date). Single source of truth for quote data.
- **`lib/Controller/ApiController.php`** — OCS API endpoint (`GET /quote`) that returns `{quote, author}`. Used by both the app view and the dashboard widget.
- **`lib/Controller/PageController.php`** — Serves the full-page app template.
- **`lib/Service/QuoteFetchException.php`** — Custom exception for upstream API failures.

### Frontend (Vue 3 + TypeScript)

Two Vite entry points (configured in `vite.config.ts`):

- **`src/main.ts`** → `App.vue` — Full-page app view, mounts to `#dailyzenquote`.
- **`src/widget.ts`** → `Widget.vue` — Dashboard widget, registered via `OCA.Dashboard.register()`.

Both views share the **`src/composables/useQuote.ts`** composable which fetches quote data from the OCS API endpoint.

### Data Flow

Frontend composable → OCS API (`ApiController`) → `QuoteService` (cache check → ZenQuotes.io) → response bubbles back as `{quote, author}`.

## User Instructions
The owner does not want Claude to run any build commands.
The owner does not want Claude to run any git commands.
