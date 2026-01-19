# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Immobilien Rechner Pro** is a WordPress plugin (v2.0.0) for real estate valuation. It provides three calculator modes: rental value estimation, sale value estimation (ImmoWertV method), and rental vs. sale comparison. The plugin includes lead generation, CRM integration (Propstack), PDF generation, and email automation.

**Tech Stack:** PHP 7.4+ backend, React 18 frontend via @wordpress/scripts, DOMPDF for PDF generation.

## Build Commands

```bash
npm install          # Install dependencies
npm run start        # Development mode with watch/hot reload
npm run build        # Production build (required before commits)
npm run lint:js      # Check JavaScript
npm run lint:css     # Check SCSS
npm run format       # Auto-format code
```

The `build/` folder is committed to git (required for WordPress plugin distribution).

## Architecture

### PHP Backend (includes/)

Singleton plugin pattern in `immobilien-rechner-pro.php`. Key classes:

- **class-calculator.php** - Rental value & comparison calculation logic (base price matrix × multipliers)
- **class-sale-calculator.php** - Sale value using German ImmoWertV method
- **class-rest-api.php** - REST endpoints under `/wp-json/irp/v1/`
- **class-leads.php** - Lead CRUD with 2-stage capture (partial → complete)
- **class-admin.php** - Admin pages, settings, AJAX handlers
- **class-error-handler.php** - Centralized error handling with codes E1xxx-E5xxx

### React Frontend (src/)

Multi-step wizard architecture orchestrated by `App.js`:

**Flow:** Mode Selection → Calculator Steps → Pending Animation → Contact Form → Results

- **src/components/** - Main components (App, ModeSelector, calculators, ResultsDisplay)
- **src/components/steps/** - Individual wizard step components (17 files)
- **src/utils/debug.js** - Debug logging that survives minification (use `window.irpLogs`)
- **src/styles/main.scss** - All styling

### 2-Stage Lead Capture

1. User completes calculator → `POST /leads/partial` creates partial lead
2. Pending animation displays
3. Contact form submission → `POST /leads/complete` adds contact info
4. Results shown with PDF/email options

### Database

Single table `wp_irp_leads` stores both partial and complete leads with status field.

### WordPress Options

- `irp_settings` - General settings
- `irp_email_settings` - Email configuration
- `irp_price_matrix` - Base prices by city/property type

## REST API Endpoints

Base: `/wp-json/irp/v1/`

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/calculate/rental` | POST | Estimate rental value |
| `/calculate/comparison` | POST | Compare sell vs. rent |
| `/calculate/sale_value` | POST | Estimate sale value |
| `/leads/partial` | POST | Create partial lead (property data only) |
| `/leads/complete` | POST | Complete lead (add contact info) |
| `/locations` | GET | Address autocomplete |
| `/cities` | GET | Get configured cities |

## Shortcode Usage

```
[immobilien_rechner]                              # Full interactive
[immobilien_rechner mode="rental"]                # Lock to rental mode
[immobilien_rechner city_id="muenchen"]           # Lock to specific city
[immobilien_rechner mode="comparison" theme="dark"]
```

## Error Code System

Maintain the centralized error code system in `class-error-handler.php`:
- E1xxx: Validation errors
- E2xxx: Database errors
- E3xxx: API errors
- E4xxx: Authentication errors
- E5xxx: System errors

## Development Notes

- Admin pages defined in `admin/views/` with handlers in `class-admin.php`
- Email/PDF templates in `includes/templates/`
- Icons as inline SVGs in `assets/icon/`, loaded via `src/components/Icon.js`
- German translations in `languages/`, update POT file when adding strings
- All API requests use WordPress nonce validation and rate limiting (10 req/hour/IP)

## Integrations

- **Propstack CRM** - Automatic lead sync (`class-propstack.php`)
- **Google Ads** - Conversion tracking in `src/utils/tracking.js`
- **Google Maps** - Address autocomplete
- **reCAPTCHA v3** - Form spam protection (`class-recaptcha.php`)
- **GitHub** - Auto-update from releases (`class-github-updater.php`)

## Documentation

Detailed docs available in `docs/`:
- `API.md` - REST API reference
- `ENTWICKLER.md` - Developer guide
- `KONFIGURATION.md` - All configuration options
- `BENUTZERHANDBUCH.md` - User guide (German)
