# Tool Management System – Project Plan

## 1) Vision and Goals
Build a secure, simple, and fast tool management system enabling authenticated users to register tools, manage workers, assign/return tools, audit usage, and search quickly. Mobile-friendly workflows and QR codes provide frictionless field operations.

## 2) Scope (User Stories)
- Auth: As a user, I can sign up, log in/out, and manage my session.
- Tools: I can create/read/update/delete tools with attributes (power, size, serial, etc.), see availability, and view a unique QR for each tool.
- Workers: I can register workers, generate QR codes for them, and view/maintain their details.
- Assignment: I can assign tools to workers via a form or by scanning worker QR then tool QR (or vice versa), and return tools.
- Audit: I can see an audit log of recent actions (tool created, assigned, returned, edited, etc.).
- Search: I can quickly search by tool name, serial, and attributes with filters.

Non-goals (initial): procurement/Purchase Orders, advanced maintenance scheduling, external SSO integrations, multi-tenant org management beyond a single account/instance.

## 3) Roles and Permissions
- Admin: Full access to all data and settings.
- Manager: Manage tools, workers, assignments, view audit, limited settings.
- Viewer: Read-only for tools, workers, audit.
(Role checks on every API call; least privilege by default.)

## 4) Architecture Overview
- Web App: Responsive SPA/SSR UI with mobile-first scan flows.
- API Layer: Type-safe RPC/REST for auth, tools, workers, assignments, audit, QR.
- Database: Relational (e.g., Postgres/SQLite) with strong constraints and indexes.
- AuthN/Z: Session/JWT based auth; server-checked RBAC middleware.
- QR Service: Deterministic signed URLs/tokens; render to PNG/SVG; revocation/rotation.
- Observability: Structured logs, metrics, request tracing.

## 5) Data Model (ERD outline)
- users(id, email, password_hash, name, role, created_at)
- workers(id, external_code?, name, contact?, status, qr_secret, created_at, updated_at)
- tools(id, name, category?, serial?, status, power_watts?, size?, attributes JSON, qr_secret, created_at, updated_at)
- assignments(id, tool_id, worker_id, assigned_at, due_at?, returned_at?, condition_out?, condition_in?)
- audit_logs(id, actor_user_id, event_type, entity_type, entity_id, metadata JSON, created_at)
- tool_images(id, tool_id, url, created_at)

Notes
- status fields: tool.status ∈ {available, assigned, maintenance, retired}; worker.status ∈ {active, inactive}.
- attributes JSON for extensible per-tool fields (e.g., voltage, weight, brand). Add GIN/JSON indexes for search.
- qr_secret: random opaque token for QR resolution; can rotate; not guessable.

## 6) QR Code Scheme
- QR payload encodes a URL path with opaque token, e.g., /qr/t/<token> or /qr/w/<token>.
- Server resolves token → resource (tool/worker). No PII/IDs in QR.
- Optional HMAC signature + short TTL for action-specific links; static tokens for identity only.
- Token rotation: keep previous tokens valid for N minutes after rotate to avoid breakage.
- Revocation list for compromised tokens.

## 7) Core Flows
Auth
- Signup (email+password), Login, Logout, Password reset.
- Middleware: session enforcement, CSRF defense, rate-limiting.

Tools
- Create/edit tool with attributes, images.
- View detail: availability, current holder, history, QR.
- Retire/reactivate tool.

Workers
- Create/edit worker, generate/regenerate QR.
- Optional: link to external employee code.

Assignment
- Assign: select worker+tool or scan worker QR then tool QR. Validate availability, permissions, and due date.
- Return: mark returned, record condition_in.
- Transfer: tool reassigned directly between workers (creates return+assign events atomically).
- Conflict handling: prevent double-assignment via transactions and status checks.

Audit
- Append-only log for: user auth events, tool CRUD, worker CRUD, assignment/return/transfer, QR rotate.
- Filterable by actor, entity, event type, date.

Search
- Global search box: name, serial, and attribute key/value.
- Facets: category, status, power range, size, brand, etc.
- Pagination + sort (name, last activity, status).

Mobile Scan
- /scan page uses camera to scan QR; or QR opens deep link directly.
- Auto-route to tool/worker detail with context-aware CTA (Assign/Return) depending on current state.

## 8) API Surface (indicative)
- auth: signup, login, logout, me
- tools: list, get, search, create, update, delete, rotateQr, images.add/remove
- workers: list, get, create, update, delete, rotateQr
- assignments: create (assign), return, transfer, listByTool, listByWorker, currentHolder
- audit: list, byEntity
- qr: resolveToken(type, token) → entity

## 9) Security and Compliance
- RBAC: route-level + data-level checks; enforce ownership constraints.
- Input validation on all endpoints; safe JSON attribute schemas.
- CSRF protection for state-changing web requests.
- Rate limiting on auth and QR resolve endpoints.
- Session hardening: secure cookies, short-lived tokens, refresh rotation.
- Secrets: no secrets in code; env-managed; prevent logging secrets/PII.
- Audit integrity: append-only table; restrict deletes; include actor, IP, UA.
- PII minimization: only necessary worker fields; export/delete workflows later.

## 10) Performance and Scalability
- DB indexes: (tools.name), (tools.serial), JSONB GIN on attributes, (assignments.tool_id, returned_at), (audit_logs.entity_type, entity_id, created_at).
- Paginated queries; avoid N+1 via joins/selects.
- Caching for QR token resolution hot paths (short TTL), search response caching where applicable.
- Background jobs for heavy tasks (image processing, exports).

## 11) Testing Strategy
- Unit tests: services, validators, RBAC guards.
- Integration tests: API procedures (auth, CRUD, assignment).
- E2E: happy paths — signup → create tool/worker → assign → return; QR scan/resolve.
- Security tests: authz bypass attempts, rate-limit, CSRF, input fuzz.
- Migration tests: forward/backward compatibility.

## 12) Migrations and Data
- Incremental DB migrations with safe defaults and down scripts.
- Seed script: demo users, a few tools/workers for QA and E2E.

## 13) Telemetry and Ops
- Structured logs with correlation IDs.
- Metrics: assignments per day, search latency, QR resolves, auth failures.
- Health checks and readiness probes.

## 14) Accessibility and UX
- Keyboard accessible, WCAG AA contrast.
- Large tap targets on mobile; clear empty states.
- Scan/assign flows optimized for 1–2 taps.

## 15) Milestones and Acceptance Criteria
M0: Project skeleton, CI, env config
- Accept: CI green; dev env up; lint/typecheck/tests scaffolding.

M1: Auth (signup/login/logout)
- Accept: New user can sign up, log in, log out securely.

M2: Tools CRUD + Search v1
- Accept: Create/edit/delete tool; list with filters; attribute search works with indexes.

M3: Workers CRUD
- Accept: Create/edit/delete worker; list; QR generated per worker.

M4: Assignment + Audit
- Accept: Assign/return/transfer with validations; audit logs visible and filterable.

M5: QR Codes for Tools and Workers
- Accept: Unique QR per tool/worker; resolving opens correct detail; tokens rotatable.

M6: Mobile Scan UX
- Accept: /scan works on mobile; camera permissions; assignment flow via scans.

M7: Admin/Reporting Basics
- Accept: Basic dashboard: active assignments, overdue, recent activity.

M8: Hardening & Perf
- Accept: RBAC verified, rate limits, CSRF, indexes tuned; p95 search < 200ms on sample data.

## 16) Risks and Mitigations
- QR token leakage → Signed opaque tokens, rotate/revoke, minimal scopes.
- Double-assignment race → DB transactions, status checks, unique open-assignment constraint.
- Attribute schema sprawl → Controlled dictionary with freeform JSON; documented filters.
- Mobile camera issues → Fallback manual entry; tested across major browsers.
- Search performance → Proper indexing, pagination, and query plans reviewed.

## 17) Open Questions
- Do workers need accounts, or remain entities managed by admins only?
- Do we support multi-org tenants in phase 1?
- Required tool attributes by category — predefined dictionary?
- Do we need offline mode for poor connectivity sites?

## 18) Appendix
Example attributes JSON
- { "brand": "Makita", "voltage": 18, "weight_kg": 2.5, "category": "Drill" }

Example QR paths
- /qr/t/<opaque_token>
- /qr/w/<opaque_token>

Search filters (indicative)
- q=drill&status=available&brand=Makita&voltage_gte=18
