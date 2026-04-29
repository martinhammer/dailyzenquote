# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Daily Zen Quote — a Nextcloud app that displays an inspirational quote of the day from the ZenQuotes.io API as a Nextcloud Dashboard widget. Targets Nextcloud 31–32, PHP 8.1+, Node 24+.

The app source lives at the repository root (the standard Nextcloud app layout).

## Architecture

### Backend (PHP)

- **`lib/AppInfo/Application.php`** — App bootstrap; registers the dashboard widget.
- **`lib/Dashboard/ZenQuoteWidget.php`** — Nextcloud dashboard widget registration.
- **`lib/Service/QuoteService.php`** — Core logic: fetches the daily quote from `zenquotes.io/api/today` with a distributed cache (TTL 24h, keyed by date). Single source of truth for quote data.
- **`lib/Controller/ApiController.php`** — OCS API endpoint (`GET /quote`) that returns `{quote, author}`. Consumed by the dashboard widget.
- **`lib/Service/QuoteFetchException.php`** — Custom exception for upstream API failures.

### Frontend (Vue 3 + TypeScript)

Single Vite entry point (configured in `vite.config.ts`):

- **`src/widget.ts`** → `Widget.vue` — Dashboard widget, registered via `OCA.Dashboard.register()`.

The widget uses the **`src/composables/useQuote.ts`** composable to fetch quote data from the OCS API endpoint.

### Data Flow

Widget composable → OCS API (`ApiController`) → `QuoteService` (cache check → ZenQuotes.io) → response bubbles back as `{quote, author}`.

## User Instructions
The owner does not want Claude to run any build commands.
The owner does not want Claude to run any git commands.
