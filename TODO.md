# Tool Management System – Execution Plan

Status: In progress

## Milestones
- M0: Project skeleton, CI, env config
  - [ ] Verify env (.env), database connection (sqlite), storage links
  - [ ] Add CI workflow (PHP, Pint, Pest)
  - [ ] Ensure vite/dev builds run
- M1: Auth (signup/login/logout)
  - [ ] Choose/auth scaffold (Fortify/Breeze/Filament Panel) per Laravel 12
  - [ ] Routes, controllers/Livewire components
  - [ ] Session hardening, rate limits
  - [ ] Tests: signup/login/logout
- M2: Tools CRUD + Search v1
  - [x] Migration: tools (status, serial unique, attributes JSON, qr_secret)
  - [x] Model, policies, factories, seeders
  - [x] Filament Resource or Livewire pages
  - [x] Search filters on table (brand, voltage)
  - [x] Tests: table filters (brand, voltage)
- M3: Workers CRUD
  - [x] Migration: workers (status, external_code?, qr_secret)
  - [ ] Model, policies, factories
  - [ ] UI pages/forms
  - [ ] Tests: CRUD
- M4: Assignment + Audit
  - [x] Migrations: assignments, audit_logs
  - [ ] Services: assign, return, transfer (transactions, validations)
  - [ ] Policies, events/listeners to append audit
  - [ ] UI actions/buttons
  - [ ] Tests: happy paths, conflicts
- M5: QR Codes (tools/workers)
  - [ ] QR resolve endpoint (/qr/t/<token>, /qr/w/<token>)
  - [ ] Token rotation + revocation
  - [ ] Render SVG/PNG; display on detail pages
  - [ ] Rate limit QR resolve
  - [ ] Tests
- M6: Mobile Scan UX
  - [ ] /scan page with camera; fallback input
  - [ ] Context-aware CTA (Assign/Return)
  - [ ] Tests (browser smoke)
- M7: Admin/Reporting Basics
  - [ ] Dashboard: active assignments, overdue, recent activity
  - [ ] Tests
- M8: Hardening & Perf
  - [ ] RBAC: roles Admin/Manager/Viewer enforced via policies
  - [ ] CSRF, rate limits, headers
  - [ ] Index review; p95 search target

## Immediate Next Actions
- [ ] Finalize DB schema draft for: users.role, tools, workers, assignments, audit_logs, tool_images
- [ ] Create migrations (php artisan make:migration …) with constraints and indexes
- [x] Implement models with relationships and casts()
- [x] Seed minimal demo data
- [x] Add Pest tests for migrations/models basics

## Notes
- Packages: Laravel 12.26, Livewire v3, Filament v4, Pest v4, Pint
- Database: sqlite (local). Prefer Eloquent, avoid raw DB:: unless needed
- Follow CLAUDE.md conventions and run Pint before finishing code edits
