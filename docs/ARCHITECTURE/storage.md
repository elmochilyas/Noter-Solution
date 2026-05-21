# Storage Architecture

## Provider

**Supabase Storage** — S3-compatible object store, same vendor as the database, EU-Central-1 (Frankfurt).

## Why Supabase Storage

- S3 protocol — interchangeable with AWS S3 / DigitalOcean Spaces if we ever migrate.
- Built-in encryption at rest.
- Signed URLs supported natively.
- Same vendor / same region / same dashboard as the database.
- No egress charges within the Supabase Pro tier limits.

## Laravel filesystem configuration

```php
// config/filesystems.php
'disks' => [
    'supabase' => [
        'driver' => 's3',
        'endpoint' => env('SUPABASE_STORAGE_ENDPOINT'),  // https://<ref>.supabase.co/storage/v1/s3
        'key' => env('SUPABASE_STORAGE_KEY'),
        'secret' => env('SUPABASE_STORAGE_SECRET'),
        'region' => 'eu-central-1',
        'bucket' => null,  // bucket selected per call
        'use_path_style_endpoint' => true,
        'throw' => true,
    ],
],
```

The `supabase` disk is the only object-storage disk. The default `local` disk is used only for ephemeral files (e.g. temporary PDF generation before upload).

## Buckets

| Bucket | Visibility | Contents | Retention |
|---|---|---|---|
| `documents` | private | Client-uploaded supporting documents (CIN scans, deeds, contracts) | 90 days post-appointment, then auto-purge |
| `receipts` | private | Generated payment receipts (PDF) | 10 years (fiscal law) |
| `internal` | private | Admin uploads (Sana's templates, scanned reference docs) | Indefinite |
| `public` | public | Marketing assets (Sana's portrait, office photos, OG images) | Indefinite |

All buckets configured as **private by default** except `public`. Access to private buckets is only via short-lived signed URLs.

## Path conventions

Paths are organized by entity and use UUIDs (never user input) for file names.

```
documents/
  booking-{booking_uuid}/
    {document_uuid}.pdf
    {document_uuid}.jpg

receipts/
  {YYYY}/
    {MM}/
      {receipt_number}.pdf

internal/
  templates/
    {YYYY-MM-DD}-{slug}.docx
  notes/
    {YYYY-MM}-{slug}.pdf

public/
  portrait/
    sana-bouhamidi.webp
    sana-bouhamidi-2x.webp
  office/
    {n}.webp
  og/
    {locale}-default.png
```

Rationale:
- UUIDs avoid information leakage via filenames.
- Date-partitioning on receipts simplifies fiscal exports.
- Booking-scoped folder for documents enables easy "delete all docs for booking X" operations.

## Upload flow

```
1. Client selects file in browser (max 10 MB, mime types: pdf, jpg, jpeg, png)
2. Client-side validation (size, mime)
3. Livewire upload to /livewire/upload-file (temporary path: storage/app/livewire-tmp)
4. Server-side validation:
   - Re-check mime via PHP's getMimeType() — never trust the client
   - Check actual file content matches claimed extension (magic bytes)
   - Reject if size > 10 MB
5. DocumentService::attachToBooking(Booking $booking, UploadedFile $file):
   - Generate new UUID
   - Move file to documents/booking-{uuid}/{uuid}.{ext} on the supabase disk
   - Insert Document row with scan_status=pending, purge_after=null
   - Dispatch ScanDocumentForViruses job
6. Return Document to caller
7. (Async) Virus scan job runs
   - On clean: scan_status=clean, scanned_at=now
   - On infected: scan_status=infected, file deleted, admin notified
```

## Access control

**Never return a public bucket URL for private content.**

All document access goes through `DocumentService::temporaryUrl`:

```php
class DocumentService
{
    public function temporaryUrl(Document $doc, int $minutes = 5): string
    {
        // Authorization checked by the caller via policy
        return Storage::disk('supabase')
            ->temporaryUrl($doc->storage_path, now()->addMinutes($minutes), [
                'ResponseContentDisposition' => 'attachment; filename="' . $this->safeFilename($doc) . '"',
            ]);
    }
}
```

- Signed URL expires in **5 minutes**.
- Forces download (not in-browser viewing) for documents.
- Filename header uses the original filename, sanitized.
- The route handing out signed URLs verifies the requesting user can view the document via `DocumentPolicy::view`.

### Download routes

| Route | Caller | Authorization |
|---|---|---|
| `GET /portal/bookings/{booking}/documents/{document}` | Client | DocumentPolicy: actor.id === document.client_id |
| `GET /admin/documents/{document}` | Admin | Permission `documents.view.all` |
| `GET /portal/receipts/{receipt}` | Client | ReceiptPolicy: actor.id === receipt.booking.client_id |
| `GET /admin/receipts/{receipt}` | Admin | Permission `payments.view` |

These routes don't return the file — they redirect to a freshly-signed URL. The actual bytes are served by Supabase, not by our app server.

## Virus scanning

`ScanDocumentForViruses` is a queued job that calls an external scanner.

### v1 implementation

- Pluggable interface `VirusScanner`.
- Default driver: `null` (always returns clean) — acceptable for v1 given the audience.
- A flag is set in `Document.scan_status`; downloads are allowed for `pending` files in v1 because we don't yet have a real scanner. **This is a known limitation, tracked in `PHASES/07-polish-launch.md`.**

### Pre-launch upgrade path

Replace the `null` driver with one of:

- **ClamAV** running as a separate container in the Hetzner box. Local socket call, no API cost.
- **VirusTotal API** — paid tier required for commercial use, ~50 USD/month for v1 volume.

Chosen at launch based on cost-benefit.

If `scan_status === 'infected'`:
- The file is deleted from storage immediately.
- The Document row is kept with metadata for audit.
- The admin gets an email alert.
- The uploading client gets an email: "Le fichier que vous avez téléchargé a été refusé. Merci de nous contacter."

## Encryption

### At rest

- Supabase Storage encrypts all objects server-side (AES-256).
- We do not perform additional client-side encryption — adds complexity, and Supabase's at-rest encryption is sufficient for this threat model.
- Sensitive metadata about the file (e.g. the description of the document type, original filename if it contains PII) is stored in the DB, where sensitive columns can be encrypted via Laravel's `encrypted` cast.

### In transit

- HTTPS only — Supabase doesn't accept HTTP requests.
- TLS 1.2 minimum.

## Retention and auto-purge

A scheduled job `PurgeExpiredDocuments` runs nightly:

```php
public function handle(): void
{
    $expired = Document::where('purge_after', '<=', now())->get();

    foreach ($expired as $doc) {
        Storage::disk('supabase')->delete($doc->storage_path);
        $doc->delete();  // soft delete the DB row for audit
        activity()->performedOn($doc)->log('document_purged');
    }
}
```

`purge_after` is set to `appointment_date + 90 days` when the booking is marked completed. For bookings cancelled before the appointment, it's set to `cancellation_date + 30 days`.

Receipts are never purged automatically (legal retention).

## Backups

- Supabase Storage Pro tier includes daily snapshots + 7-day PITR.
- Weekly logical backup: a job lists every object via the S3 API and writes a manifest (CSV: path, size, etag) to a separate bucket.
- For real disaster recovery, the actual file restore relies on Supabase support — documented in `OPERATIONS/backup-recovery.md`.

## Quotas and alerts

- Supabase Pro: 100 GB storage included, then $0.021 per GB-month.
- Current expected usage at 50 bookings/week with ~3 docs each, average 2 MB:
  - 50 × 52 × 3 × 2 = ~15 GB/year, minus ~80% purged after 90 days.
- Steady-state: < 5 GB. Well within Pro limits.
- Alert at 80% of plan (80 GB) via Supabase webhook → admin email.

## Disaster scenarios

| Scenario | Recovery |
|---|---|
| Accidental file deletion | Restore from Supabase PITR (≤ 7 days) |
| Bucket deletion | Recreate, restore manifest, contact Supabase support (last-resort) |
| Supabase outage | Brief — site degrades: uploads disabled, downloads fail with friendly message |
| Region outage | Cross-region copy not implemented in v1 — accepted risk given small volume; document for v1.2 |
| Credential leak | Rotate keys immediately, invalidate all signed URLs (note: they expire in 5 min anyway), audit access logs |

## Local development

Local dev uses the `local` disk pointing to `storage/app/public` instead of Supabase. The `supabase` disk is mocked via Laravel's `Storage::fake('supabase')` in tests, or pointed to a local MinIO container when integration testing is needed.

## Operational tasks

| Task | How |
|---|---|
| List all docs for a booking | `Storage::disk('supabase')->files("documents/booking-{$uuid}/")` |
| Delete all docs for a booking | `DocumentService::deleteAllForBooking($booking)` (loops, deletes from disk, soft-deletes rows) |
| Migrate a file | `Storage::move()` works on the same disk |
| Verify integrity | Weekly job that lists DB documents and checks each `storage_path` exists; mismatches alerted |

## Anti-patterns

- ❌ Returning a Supabase URL directly without signing.
- ❌ Storing the user's original filename as the storage path.
- ❌ Letting clients choose the storage path / bucket.
- ❌ Skipping mime-type verification on the server side.
- ❌ Making the `documents` bucket public for convenience.
- ❌ Using the `local` disk for anything that should persist beyond a single request.
