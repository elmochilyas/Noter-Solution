# Feature: Document Management

## Scope

Client-uploaded supporting documents for a booking. Lifecycle from upload to auto-purge.

Out of scope: e-signature, document drafting, document generation by Sana.

## Why documents

Most consultations benefit from Sana having sight of relevant documents ahead of time:
- Family: marriage contracts, birth certificates, judgments
- Real estate: title deeds, plans, prior contracts
- Financial: prior agreements, ID
- Contracts: existing drafts

Pre-upload lets Sana prepare and makes the consultation more efficient.

## Upload paths

1. **During booking flow** (step 5) — see `FEATURES/booking.md`.
2. **From client portal** — see `FEATURES/client-portal.md`.
3. **Admin upload on behalf of client** — see `FEATURES/admin-panel.md` (rare; only for support cases).

## Constraints

- Mime types: `application/pdf`, `image/jpeg`, `image/png`.
- Max size per file: 10 MB.
- Max files per booking: 20 total.
- Max upload per session: 5 files at a time.
- Filenames stored as UUIDs; original names preserved in DB metadata.

Validation happens client-side (UX) and server-side (security).

## Upload UX (client)

- Drag-and-drop zone with browse fallback.
- Per-file progress bar.
- Thumbnail for images, generic icon for PDFs.
- Per-file remove (X) button before final submit.
- Inline error per file if rejected (size, type, scan).

```
┌────────────────────────────────────────────┐
│                                             │
│   ⬆  Glissez-déposez vos fichiers ici       │
│         ou [Parcourir]                      │
│                                             │
│   PDF, JPG, PNG · 10 Mo max par fichier     │
└────────────────────────────────────────────┘

┌──────────────────────────────────────────┐
│  📄 CIN_recto.jpg                  2.3 Mo │
│  ▓▓▓▓▓▓▓▓▓▓ 100%       [ Supprimer ]     │
├──────────────────────────────────────────┤
│  📄 Contrat_mariage.pdf            5.1 Mo │
│  ▓▓▓▓▓▓░░░░ 60%        [ Annuler ]       │
└──────────────────────────────────────────┘
```

## Storage

See `ARCHITECTURE/storage.md`:
- Bucket: `documents` (private)
- Path: `documents/booking-{booking_uuid}/{document_uuid}.{ext}`
- Access via signed URL only (5-min TTL)

## Virus scan

See `ARCHITECTURE/storage.md`:
- v1: `null` scanner (always returns clean) — known limitation
- Pre-launch: switch to ClamAV or VirusTotal
- On infected: file deleted, admin notified, client emailed

## Document lifecycle

```
1. Uploaded
   - Document row: scan_status=pending, purge_after=null
   - File on Supabase
   - ScanDocumentForViruses job queued
2. Scanned
   - scan_status=clean (download enabled)
   - OR scan_status=infected (file deleted, alert raised)
3. Booking completed
   - purge_after = now() + 90 days
4. Booking cancelled
   - purge_after = now() + 30 days
5. Purge time reached
   - File deleted from Supabase
   - Document row soft-deleted (for audit)
6. Audit log entry created
```

## Retention

- Default: 90 days after appointment.
- Cancelled bookings: 30 days after cancellation.
- Sana can manually extend the retention on a per-document basis if a matter is ongoing — admin override.
- Sana can manually purge early if needed (with confirmation + audit log).

## Download

- Client: from portal booking detail page.
- Admin: from Filament booking detail or `DocumentResource`.
- Both go through `DocumentService::temporaryUrl` which checks authorization via `DocumentPolicy::view`.
- Signed URL forces download (`Content-Disposition: attachment`).
- File name in download header: original filename, sanitized.

## Preview

- Images: inline thumbnail + lightbox.
- PDFs: no inline preview in v1 (would require a PDF JS library — Lighthouse-budget cost too high). Download instead.

## Privacy

- Documents contain PII (CIN scans, addresses, financial data).
- See `STANDARDS/security.md` for handling rules.
- Sana's internal team only — never shared with third parties without explicit instruction.

## Deletion

Three ways a document is deleted:

1. **Client deletes it before booking confirmation** — full delete (file + row).
2. **Auto-purge** — file deleted, row soft-deleted, audit logged.
3. **Admin deletes** — same as auto-purge plus reason captured.

After deletion:
- File gone from Supabase.
- DB row marked deleted but kept for audit.

## Account deletion

When a client deletes their account (Loi 09-08 right to erasure):
- All their documents are auto-purged at the next nightly run.
- The 90-day rule is overridden.

## Audit

Logged in `activity_log`:
- Upload (with size, mime, scan status)
- Download (with downloader user_id)
- Delete (with reason)
- Purge (with date)

## Limits & error states

| Scenario | Response |
|---|---|
| File > 10 MB | "Le fichier dépasse 10 Mo." |
| Wrong type | "Type de fichier non autorisé. PDF, JPG, PNG uniquement." |
| Network drop | "Le téléversement a échoué. Réessayez." with retry button |
| Booking full (20 files) | "Vous avez atteint le maximum de 20 fichiers pour ce dossier." |
| Storage outage | "Le service de téléversement est temporairement indisponible." |
| Scan flagged file | Email to client: "Le fichier a été refusé. Contactez-nous." |

## Mobile UX

- Native file picker opens (allows camera capture).
- Image-only quick path: "Prendre une photo" button uses camera directly.
- Same drag-and-drop fallback (some mobile browsers support it).

## i18n

- All UI text translated.
- Document name preserved as-is (we don't translate filenames).

## Acceptance criteria

- [ ] Upload from booking flow works
- [ ] Upload from portal works
- [ ] Multi-file upload with per-file progress
- [ ] Mime + size validation client + server
- [ ] Files stored with UUID name in correct bucket path
- [ ] Document row created with metadata
- [ ] Scan job dispatched
- [ ] Authorization works: client A can't access client B's docs
- [ ] Signed URL download with 5-min TTL
- [ ] Forced download (not inline)
- [ ] Auto-purge after 90 days (completed) / 30 days (cancelled) tested via clock manipulation
- [ ] Manual delete from portal works
- [ ] Admin delete with reason audit-logged
- [ ] Account deletion triggers auto-purge of all docs
- [ ] Infected scan results: file deleted, client + admin notified
- [ ] Storage outage shows friendly error
- [ ] All upload + download + delete events audit-logged
- [ ] No PII in error messages
- [ ] Works on iOS Safari, Android Chrome, desktop browsers

## Out of scope (v1)

- E-signature
- PDF preview in browser
- OCR / text extraction
- Document templates / generation
- Versioning (multiple versions of the same logical doc)
- Folder structure beyond per-booking grouping
- Bulk download as ZIP (could add easily later)

## Risks

- **Without real virus scanning at launch (v1 `null` driver), we accept residual risk.** Mitigated by:
  - Server-side mime / magic-byte verification.
  - Files served via signed URLs with `Content-Disposition: attachment` (browser downloads, no execution).
  - Admin downloads files at their own machine where local AV is presumed.
  - Pre-launch upgrade to real scanner.

- **Document content is sensitive.** Mitigated by encryption at rest, signed-URL-only access, audit logging, and limited retention.
