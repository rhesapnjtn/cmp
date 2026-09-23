# CMP — Centralized Monitoring Platform — System Design

## 1. Requirement Analysis

CMP is a centralized platform to monitor company-wide activities: planning, risk, ongoing
business (OGB), HSSE committee meetings, and compliance. Management needs a single
dashboard with KPI, charts, tables, progress and filters by **year / function / department /
zone / site**.

### Functional Modules

| Module | Objects | Key Metrics |
|---|---|---|
| Planning | Monitoring plan per function/department | status Plan/Done, progress %, unrealized plans |
| Risk Management | Risk Register | risk level, mitigation, PIC, status |
| Ongoing Business (OGB) | Contract/business (e.g. Contract ILJ) | contract value, used, remaining, expiry, maintenance progress, remaining work |
| HSSE Committee Meeting | Meetings L1/L2/L3 per zone/site/dept | target vs realization per quarter |
| Compliance | Requirement, PIC, deadline | status compliance, evidence |
| Dashboard | Aggregated KPIs | per-module metrics, charts, filters, reports |

### Non-Functional Requirements
- REST API backend (Laravel 12), Vue 3 SPA frontend, MySQL 8.
- Token auth (Sanctum) + role-based access control (RBAC).
- Responsive UI, modular & scalable architecture.

## 2. Data Analysis & Standardization

Master reference dimensions shared across modules for consistent filtering:
- **Units** — hierarchical organization (Function > Department), type & parent.
- **Zones** — operational zone.
- **Sites** — physical site, belongs to zone.
- **Users** — PIC/owner, has one role.

Standardization rules:
- All module tables carry `year` (+ optional `quarter`) for period filtering.
- Filters apply a common pattern: `?year=&unit_id=&zone_id=&site_id=`.
- Money stored as decimal(18,2); progress & percent stored as unsigned smallint (0–100) or decimal(5,2).
- Risk level, statuses and HSSE levels use enum constants in PHP (single source of truth).

## 3. ERD

```
roles 1─n users n─1 units(*)              zones 1─n sites 1─n (unit/zone/site refs on modules)
roles n─n permissions (role_permission)

users 1─n plans (pic)
unit  1─n plans
plans 1─n plan_tasks

users 1─n risk_registers (pic)
unit  1─n risk_registers
site  1─n risk_registers

unit  1─n contracts (OGB)   site 1─n contracts
contracts 1─n contract_milestones

unit 1─n hsse_meetings   zone 1─n hsse_meetings   site 1─n hsse_meetings

unit 1─n compliance_items   user 1─n compliance_items (pic)
compliance_items 1─n compliance_evidence_files
```

(*) A user optionally belongs to a unit (department).

## 4. Database Schema

### Master data
- **roles**: id, name, code(unique), description
- **permissions**: id, name, code(unique), module, description
- **role_permission**: role_id, permission_id (PK pair)
- **users**: id, name, email, password, unit_id(nullable), job_title, is_active, remember_token, timestamps
- **user_role**: user_id, role_id (PK pair)
- **units**: id, parent_id(self FK null), code, name, type(function|department)
- **zones**: id, code, name
- **sites**: id, zone_id, code, name

### planning
- **plans**: id, unit_id, title, description, category, target_date, status(plan|done|cancelled),
  progress(0-100), year, quarter, pic_user_id, realized_date, evidence, notes, timestamps, soft deletes
- **plan_tasks**: id, plan_id, name, target_date, status(pending|in_progress|done), progress, notes

### risk
- **risk_registers**: id, risk_code, unit_id, site_id, title, description, category(operational|strategic|financial|compliance|environmental),
  likelihood(1-5), impact(1-5), risk_score(=likelihood*impact), risk_level(SELECT computed: critical/high/medium/low),
  mitigation, contingency, pic_user_id, status(identified|assessed|mitigated|monitored|closed),
  due_date, year, quarter, evidence, timestamps, soft deletes

### ogb
- **contracts**: id, contract_number, name, type(ilj|maint|general), unit_id, site_id,
  vendor, contract_value, used_value, remaining_value(computed), currency,
  start_date, end_date, status(active|expired|completed|terminated),
  maintenance_type, maintenance_progress, remaining_work, work_progress,
  pic_user_id, year, notes, timestamps, soft deletes
- **contract_milestones**: id, contract_id, name, planned_date, actual_date, status(pending|reached|missed)

### hsse
- **hsse_meetings**: id, level(1|2|3), title, unit_id, zone_id, site_id, quarter, year,
  target_count, planned_date, realized_date, status(scheduled|done|missed), notes

### compliance
- **compliance_items**: id, requirement, regulation, category(legal|regulatory|internal|audit),
  unit_id, pic_user_id, deadline, status(compliant|partial|non_compliant|in_progress|na),
  progress, quarter, year, notes, timestamps, soft deletes
- **compliance_evidence**: id, compliance_item_id, file_name, file_path, uploaded_by, timestamps

## 5. Role & Permission Matrix

| Permission | admin | manager | editor(pos operational) | viewer |
|---|---|---|---|---|
| dashboard.view | ✓ | ✓ | ✓ | ✓ |
| planning.manage | ✓ | ✓ | ✓ | – |
| planning.view | ✓ | ✓ | ✓ | ✓ |
| risk.manage | ✓ | ✓ | ✓ | – |
| risk.view | ✓ | ✓ | ✓ | ✓ |
| ogb.manage | ✓ | ✓ | ✓ | – |
| ogb.view | ✓ | ✓ | ✓ | ✓ |
| hsse.manage | ✓ | ✓ | ✓ | – |
| hsse.view | ✓ | ✓ | ✓ | ✓ |
| compliance.manage | ✓ | ✓ | ✓ | – |
| compliance.view | ✓ | ✓ | ✓ | ✓ |
| masterdata.manage | ✓ | – | – | – |
| reports.view | ✓ | ✓ | ✓ | – |
| users.manage | ✓ | – | – | – |
| hsse.target | ✓ | ✓ | – | – |

## 6. REST API Endpoints

Auth: `POST /api/auth/login` → `{ token, user, permissions }`. All other endpoints require
`Authorization: Bearer <token>`.

```
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me
GET    /api/auth/filters                 (dimension options: years, units, zones, sites, quarters)

GET|POST|PUT|DELETE  /api/master/units         (admin)
GET|POST|PUT|DELETE  /api/master/zones
GET|POST|PUT|DELETE  /api/master/sites
GET|PUT/PATCH|POST   /api/master/users         (admin)
GET    /api/master/roles

GET      /api/planning?filters
POST     /api/planning
GET|PUT  /api/planning/{id}
DELETE   /api/planning/{id}
GET|POST|PUT|DELETE  /api/planning/{plan}/tasks

GET      /api/risk?filters
POST     /api/risk
GET|PUT  /api/risk/{id}
DELETE   /api/risk/{id}

GET      /api/ogb?filters
POST     /api/ogb
GET|PUT  /api/ogb/{id}
DELETE   /api/ogb/{id}
GET|POST|PUT|DELETE  /api/ogb/{contract}/milestones

GET      /api/hsse?filters
POST     /api/hsse
GET|PUT  /api/hsse/{id}
DELETE   /api/hsse/{id}

GET      /api/compliance?filters
POST     /api/compliance
GET|PUT  /api/compliance/{id}
DELETE   /api/compliance/{id}
POST     /api/compliance/{id}/evidence

GET   /api/dashboard?year=&unit_id=&zone_id=&site_id=
GET   /api/reports?year=&unit_id=&zone_id=&site_id=&modules[]=   (exports JSON/CSV)
```

Envelope: `{ data: ... }`; errors: `{ message: ... }` with proper HTTP status.

## 7. Page / View Structure (SPA routes)

```
/login
/                          Dashboard (KPI cards + charts + module summary tables + filters)
/master/units              Master data
/master/zones
/master/sites
/master/users
/planning                  List + kanban status + progress
/planning/form|/planning/:id
/risk                      Risk register matrix + list
/risk/form|/risk/:id
/ogb                       Contract monitoring
/ogb/form|/ogb/:id
/hsse                      Meeting realization per level/quarter
/hsse/form|/hsse/:id
/compliance                Compliance status list
/compliance/form|/compliance/:id
/reports                   Summary report table + CSV export
```

Layout: sidebar (module nav), topbar (year/period + user), content area, responsive breakpoints.

## 8. Risk Score Algorithm
- `risk_score = likelihood * impact` (1..25)
- `>= 15 → critical`, `>= 9 → high`, `>= 4 → medium`, else `low`.

## 9. Tech Stack
- Backend: Laravel 12, Sanctum, MySQL 8.4
- Frontend: Vue 3 (Composition API), Vue Router, Pinia, Axios, ApexCharts, Tailwind CSS v4, Vite