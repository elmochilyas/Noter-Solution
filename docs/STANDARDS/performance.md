# Performance Standards

## Targets

### Frontend (Lighthouse, mobile, simulated 4G)

| Metric | Target | Hard fail |
|---|---|---|
| Performance score | ≥ 90 | < 80 |
| LCP (Largest Contentful Paint) | ≤ 2.5s | > 4.0s |
| INP (Interaction to Next Paint) | ≤ 200ms | > 500ms |
| CLS (Cumulative Layout Shift) | ≤ 0.1 | > 0.25 |
| Total page weight (initial load) | ≤ 500 KB | > 1 MB |
| JavaScript bundle (initial) | ≤ 100 KB compressed | > 200 KB |

### Backend (per request, p95)

| Endpoint type | Target p95 | Hard fail p95 |
|---|---|---|
| Public marketing page | ≤ 200ms | > 500ms |
| Booking calendar load | ≤ 400ms | > 800ms |
| Booking submission | ≤ 600ms | > 1.5s |
| Chatbot message | ≤ 3s (LLM-bound) | > 6s |
| Admin pages | ≤ 500ms | > 1.5s |

## Database query rules

- **Query budget per request: 20 queries maximum.** Anything above requires justification.
- **N+1 is a defect.** CI runs Larastan rules to flag obvious cases; `\Barryvdh\Debugbar\LaravelDebugbar` shows per-request count in dev.
- Use `with()` and `withCount()` for eager loading on listing pages.
- Use `chunkById()` for batch operations, not `all()`.
- Long-running queries (>200ms) get logged via the slow query logger.

```php
// Bad
$bookings = Booking::all();
foreach ($bookings as $booking) {
    echo $booking->client->name;  // N+1
}

// Good
$bookings = Booking::with('client')->get();
```

## Indexing strategy

- Every foreign key column gets an index.
- Every column used in `WHERE`, `ORDER BY`, or `JOIN` on a query that runs >100x/day gets an index.
- Composite indexes follow the leftmost-prefix rule.
- Indexes documented in `ARCHITECTURE/database-schema.md`.

Required indexes on the booking system (most-touched table):

```sql
CREATE INDEX idx_bookings_client_id ON bookings(client_id);
CREATE INDEX idx_bookings_starts_at ON bookings(starts_at);
CREATE INDEX idx_bookings_status_starts_at ON bookings(status, starts_at);
CREATE UNIQUE INDEX idx_bookings_reference ON bookings(reference);
```

## Caching layers

### 1. Page-level caching (none in v1)

Pages are dynamic enough that full-page caching isn't worth the invalidation complexity. Reconsider in v2 if traffic justifies.

### 2. Computed-value caching (Redis)

Use `Cache::remember()` for expensive computations that don't need to be real-time:

- Service-page rendered content: 1 hour TTL
- FAQ list: 15 minutes TTL
- Public consultation plan list: 15 minutes TTL
- Office hours / availability summary: 5 minutes TTL

```php
Cache::remember('faq.fr.family', 900, function () {
    return Faq::query()
        ->where('category', 'family')
        ->where('locale', 'fr')
        ->orderBy('order')
        ->get();
});
```

Invalidate on write via model observers.

### 3. Query result caching

- Use `Cache::tags()` only with the Redis driver.
- Tag cached queries with the relevant model name so invalidation is targeted.

### 4. HTTP caching for static assets

- Vite output: `Cache-Control: public, max-age=31536000, immutable` (hashed filenames).
- Public images: same.
- HTML pages: `Cache-Control: no-cache` (must revalidate, but ETag can return 304).

### 5. Browser caching headers

Use ETag middleware for HTML responses.

## Asset optimization

- **Images:** WebP first, AVIF fallback if browser-supported, JPEG/PNG last. Use `<picture>` element.
- **Image sizes:** every `<img>` has `width` and `height` (CLS prevention).
- **Lazy loading:** `loading="lazy"` on all below-fold images.
- **Responsive images:** `srcset` for the hero portrait and service-page images.
- **SVG:** inlined when small (< 4 KB) and styled with CSS variables; external otherwise.
- **Fonts:** variable fonts, subset by language (Latin + Arabic), `font-display: swap`, preload critical weight only.
  ```html
  <link rel="preload" href="/fonts/fraunces-var.woff2" as="font" type="font/woff2" crossorigin>
  ```

## JavaScript discipline

- No frontend framework loaded for marketing pages — Livewire/Alpine only.
- Defer all non-critical JS.
- Inline JS only with CSP nonce.
- Third-party scripts: deferred, async, or behind user consent (analytics).

## Livewire performance

- `wire:model.live` is forbidden on inputs that fire on every keystroke unless explicitly justified.
- Use `wire:model.blur` or `wire:model.debounce.500ms`.
- Use `wire:loading` for every server action so users see feedback in <100ms.
- Use `wire:navigate` for in-site navigation (SPA-like fast transitions, opt-in per link).
- Computed properties (`#[Computed]`) cached per request.
- Heavy queries inside Livewire components must be paginated; never load >50 records in one component.

## Filament performance

- Resources with >1k rows use `defaultPaginationPageOption(25)`.
- Tables: always set `searchable()` and `sortable()` only on indexed columns.
- Use `LazyLoaded` widgets for slow KPIs on the admin dashboard.
- `recordUrl(null)` if no row action needed — saves a per-row computation.

## Chatbot performance

- LLM call is the bottleneck — typically 1.5–4s.
- Stream responses via Server-Sent Events for perceived speed.
- Embedding lookup (pgvector) target: <50ms per query.
- Cache embeddings of FAQ entries — never re-embed on read.
- Cache short responses to common questions for 24 hours (with bypass on admin update).

## Queue strategy

- Anything taking >100ms not blocking user response goes to a queue:
  - Emails / SMS / WhatsApp sends
  - PDF receipt generation
  - Document virus scans
  - Webhook reactions (post-payment fulfillment)
  - Chatbot conversation logging
- Two queue priorities:
  - `default` — most jobs
  - `notifications` — user-visible, must be fast (reminders, confirmations)
- Failed jobs alert via Sentry; retry policy: 3 attempts with exponential backoff (15s, 1m, 5m).

## Horizon configuration

- Auto-scale workers between 2 and 8 based on queue load.
- `default` queue: minProcesses=2, maxProcesses=6.
- `notifications` queue: minProcesses=1, maxProcesses=4.
- Failed-job retention: 7 days.

## Profiling and monitoring

- **Local dev:** Laravel Debugbar shows query count, time per query, memory.
- **Staging / prod:** Laravel Pulse dashboard (`/admin/pulse`) shows slow requests, slow queries, busy workers.
- **Sentry Performance** on prod for the top 5 routes.
- **Manual profiling** with `php artisan tinker` + `DB::enableQueryLog()` when investigating.

## Performance review checkpoints

- Every PR adding a new page or list: must show query count from local profiling in PR description.
- Every phase: Lighthouse run on staging, results pasted in PR.
- Pre-launch: dedicated performance pass (see `PHASES/07-polish-launch.md`).
