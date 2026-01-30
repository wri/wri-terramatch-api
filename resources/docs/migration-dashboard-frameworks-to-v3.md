# Migration: Dashboard Frameworks List to V3

## 1. Current Component Overview

### 1.1 Endpoint (deprecated)

| Property | Value |
|----------|--------|
| **Method & URL** | `GET /api/v2/dashboard/frameworks` |
| **Route definition** | `routes/api_v2.php` → `Route::prefix('dashboard')->withoutMiddleware('auth:service-api-key,api')` |
| **Controller** | `App\Http\Controllers\V2\Dashboard\ViewProjectController::getFrameworks` |
| **Authentication** | None (auth middleware explicitly removed for dashboard prefix) |
| **Purpose** | Returns the list of frameworks used to populate **dropdowns** on the dashboard (e.g. programme/framework filter). |

### 1.2 Behaviour

- **Data source**: Distinct `framework_key` values from **approved** V2 projects that match the same filters as the dashboard project list.
- **Filtering**: Uses `TerrafundDashboardQueryHelper::buildQueryFromRequest($request)` so the frameworks list is **contextual**: only frameworks that have at least one approved project matching the current request filters are returned.
- **Default filters** (when query params are omitted):
  - `filter[programmes]`: `['terrafund', 'terrafund-landscapes', 'enterprises']`
  - `filter[cohort]`: `['terrafund', 'terrafund-landscapes']`
  - `filter[organisationType]`: `['non-profit-organization', 'for-profit-organization']`
- **Model**: `App\Models\Framework` (table `frameworks`). Keys are matched by `slug` (stored as `framework_key` on `v2_projects`).

### 1.3 Query parameters (aligned with dashboard project list)

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search term applied to project name |
| `filter[country]` | string | Filter by project country |
| `filter[programmes]` | string or array | Framework slugs (e.g. `terrafund`, `ppc`). Comma-separated or array. |
| `filter[cohort]` | string or array | Cohort values (e.g. `terrafund`, `terrafund-landscapes`). Comma-separated or array. |
| `filter[landscapes]` | array | Landscape codes (e.g. `gcb`, `grv`, `ikr`) — mapped to names in helper |
| `filter[organisationType]` | array | e.g. `non-profit-organization`, `for-profit-organization` |
| `filter[projectUuid]` | string or array | Filter by specific project UUID(s) |
| `filter[has_polygons]` | bool | When truthy, only projects that have approved active site polygons |

### 1.4 Response shape

**HTTP 200** — JSON array (no envelope):

```json
[
  { "framework_slug": "terrafund", "name": "Terrafund" },
  { "framework_slug": "terrafund-landscapes", "name": "Terrafund Landscapes" }
]
```

- **Fields**: `framework_slug` (string), `name` (string).
- No pagination.

### 1.5 Key files

| File | Role |
|------|------|
| `routes/api_v2.php` | Registers `GET /frameworks` under `dashboard` prefix (no auth). |
| `app/Http/Controllers/V2/Dashboard/ViewProjectController.php` | `getFrameworks()` — builds query via helper, then distinct `framework_key` → `Framework::whereIn('slug', ...)` → map to `framework_slug` + `name`. |
| `app/Helpers/TerrafundDashboardQueryHelper.php` | `buildQueryFromRequest(Request)` — returns Eloquent query on `v2_projects` (approved, with organisation join and all filters above). |
| `app/Models/Framework.php` | Eloquent model; `frameworks` table; used for `name` and `slug`. |
| `openapi-src/V2/paths/Dashboard/get-v2-dashboard-frameworks.yml` | OpenAPI spec for this endpoint. |
| `resources/docs/swagger-v2.yml` | Generated/merged Swagger; contains `/v2/dashboard/frameworks`. |

### 1.6 Related V2 endpoints (for migration options)

| Endpoint | Auth | Returns | Use case |
|----------|------|---------|----------|
| `GET /api/v2/admin/reporting-frameworks` | Admin | All frameworks via `ReportingFrameworkResource` (name, uuid, slug, access_code, form UUIDs, total_projects_count) | Admin CRUD; full list. |
| `GET /api/v2/reporting-frameworks/{frameworkKey}` | Optional | Single framework by slug (public) | Resolve one framework. |
| `GET /api/v2/reporting-frameworks/access-code/{ACCESS_CODE}` | Optional | Single framework by access code | Join flow. |

---

## 2. Migration options for V3

### Option A: Use existing V2 admin list (if “all frameworks” is acceptable)

- **Endpoint**: `GET /api/v2/admin/reporting-frameworks`
- **Auth**: Admin required.
- **Semantic difference**: Returns **all** frameworks, not only those with approved projects matching dashboard filters. Dashboard dropdown would show every framework, not only those with data in the current view.
- **Response**: Collection with `slug` and `name` (plus uuid, access_code, form UUIDs, total_projects_count). Frontend can use `slug` as value and `name` as label.
- **When to use**: If the V3 dashboard does not need “filtered by current project set” and admin/specific roles can call this.

### Option B: New V3 endpoint with same “filtered” behaviour

- Replicate current behaviour in V3: accept the same (or a subset of) query parameters, build the same “approved projects + filters” query, return distinct frameworks as `[{ framework_slug, name }]`.
- Requires in V3: equivalent of `TerrafundDashboardQueryHelper::buildQueryFromRequest` (or a dedicated service) and the `Framework` model (or equivalent) to resolve slug → name.
- Keeps UX: dropdown only shows frameworks that have projects in the current filter set.

### Option C: Derive frameworks from projects payload (no dedicated frameworks endpoint)

- If V3 dashboard gets its project list from another API (e.g. a new projects list endpoint), that response can include a `framework_key` (or similar) per project.
- Frontend: collect distinct `framework_key` from the current page/filter set and optionally call a small “lookup” (e.g. `GET /api/v2/reporting-frameworks/{frameworkKey}` or a batch) to get display names, or cache the full framework list once (e.g. from admin/reporting-frameworks or a new public list endpoint) and map client-side.
- No dedicated “dashboard frameworks” endpoint; list is derived from projects + optional framework lookup.

---

## 3. Implementation checklist for V3

- [ ] Decide whether dropdown must be **filtered** (only frameworks with projects in current view) or **full list** (all frameworks).
- [ ] If filtered: implement Option B (new endpoint) or Option C (derive from projects + lookup). If full list and admin is OK: Option A.
- [ ] Ensure V3 uses the same `framework_key`/`slug` and `name` semantics so labels and filters stay consistent.
- [ ] Update dashboard frontend to call the chosen V3 data source and keep dropdown payload shape `{ value: slug, label: name }` (or equivalent).
- [ ] After cutover, remove or fully deprecate `GET /api/v2/dashboard/frameworks` (this endpoint is deprecated as of this migration).

---

## 4. Deprecation in this repo

- The route `GET /api/v2/dashboard/frameworks` is **deprecated**. It remains in place for backward compatibility until the V3 dashboard is live and clients are migrated.
- OpenAPI/Swagger should mark this endpoint as deprecated.
- New clients should use one of the options in Section 2 instead of this endpoint.
