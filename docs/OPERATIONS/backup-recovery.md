# Backup & Recovery

## Recovery objectives

| Objective | Target |
|---|---|
| RPO (Recovery Point Objective) | ≤ 1 hour data loss |
| RTO (Recovery Time Objective) | ≤ 4 hours to restore service |

These are practice-scale targets. A notary's website is not life-critical infrastructure but losing a day of bookings would harm trust.

## What we back up

| Asset | Provider | Method | Frequency | Retention |
|---|---|---|---|---|
| Database (Postgres) | Supabase | Daily snapshot + PITR | Continuous (PITR), daily snapshot | 7 days PITR, 30 days snapshot |
| Database (logical) | Self | `pg_dump` weekly to S3 elsewhere | Weekly | 90 days |
| Object storage (documents, receipts) | Supabase | Daily snapshot | Daily | 7 days |
| Storage manifest | Self | List + checksum weekly | Weekly | 1 year |
| Code | GitHub | Git history | Continuous | Permanent |
| Secrets | Forge encrypted env | Manual export to Bitwarden | Monthly | Latest only |

## Database backups

### Supabase Point-in-Time Recovery (PITR)

- Included in Pro tier.
- Restores to any point within the last 7 days, with ~1-minute granularity.
- Performed via Supabase Dashboard or CLI.
- Note: PITR restores create a **new** project; data is then migrated. Plan accordingly.

### Daily snapshots

- Automatic on Pro tier.
- Visible in Dashboard → Database → Backups.
- Held 30 days.

### Weekly logical dump (defense in depth)

A scheduled job runs every Sunday at 04:00:

```bash
pg_dump \
  --host=$DB_HOST \
  --username=postgres \
  --no-owner --no-privileges \
  --format=custom \
  --file=/tmp/sana-prod-$(date +%Y-%m-%d).dump

aws s3 cp /tmp/sana-prod-*.dump s3://sana-offsite-backups/db/ \
  --endpoint-url=https://<offsite-s3-provider>

rm /tmp/sana-prod-*.dump
```

- Stored in a separate provider (not Supabase) to survive a Supabase outage.
- Encrypted at rest by the bucket policy.
- 90-day retention enforced by bucket lifecycle rules.

The offsite provider: Backblaze B2 or Wasabi (cheap, S3-compatible). Cost ~$1/month at expected size.

## Storage backups

Supabase Storage on Pro tier includes daily snapshots. Objects are versioned.

In addition, weekly job creates an inventory:

```php
// app/Jobs/InventorizeStorage.php
public function handle(): void
{
    $buckets = ['documents', 'receipts', 'internal'];
    $manifest = [];

    foreach ($buckets as $bucket) {
        $files = Storage::disk('supabase')->allFiles($bucket);
        foreach ($files as $file) {
            $manifest[] = [
                'bucket' => $bucket,
                'path' => $file,
                'size' => Storage::disk('supabase')->size($file),
                'last_modified' => Storage::disk('supabase')->lastModified($file),
            ];
        }
    }

    $csv = $this->toCsv($manifest);
    Storage::disk('offsite')->put('manifests/storage-' . now()->toDateString() . '.csv', $csv);
}
```

The manifest doesn't back up the files themselves (would double storage cost). It's an integrity reference: if a file is silently lost, we can identify it.

## Secrets backup

Quarterly:
1. Export Forge env to a `.env` file locally.
2. Encrypt with `age` using Sana's and the lead developer's public keys.
3. Upload encrypted file to a shared password manager vault.
4. Delete local plaintext.

```bash
age --recipient-file recipients.txt --output secrets.age .env
shred -u .env
```

This is a manual checklist item in the calendar.

## Restore procedures

### Scenario 1: A row was accidentally deleted

```sql
-- Find the row in a PITR-restored copy
-- (After creating a PITR clone at a timestamp before deletion)

-- Manually copy data back via INSERT
INSERT INTO bookings (...) SELECT ... FROM pitr_clone.bookings WHERE id = ?;
```

For routine cases, this takes 30 minutes.

### Scenario 2: An entire table is corrupted

```bash
# Step 1: Create PITR restore in Supabase Dashboard at last good timestamp
# Step 2: Copy table from clone to prod

pg_dump --table=bookings --data-only \
  --host=<pitr-clone-host> --username=postgres \
  | psql --host=$DB_HOST --username=postgres -d sana_prod
```

### Scenario 3: Entire database lost

1. Create new Supabase project (or use the PITR restore as new prod).
2. Update `.env` connection strings on the Hetzner box.
3. Run migrations to verify schema parity: `php artisan migrate --pretend`.
4. Test via `/up`.
5. Smoke test the booking flow.
6. Restore DNS / load-balancing to the new endpoint if it changed.

Target: complete within RTO of 4 hours.

### Scenario 4: Supabase region outage

Not directly handled in v1 — we'd wait for Supabase to recover.

If the outage exceeds 4 hours, manual restore from weekly logical dump:

```bash
# Provision a new Postgres instance (Supabase project in different region, or any Postgres)
pg_restore --clean --if-exists \
  --host=<new-host> --username=postgres \
  --dbname=sana_prod \
  /tmp/sana-prod-2026-04-14.dump
```

Data loss up to one week. Document the gap and reconstruct from logs / Stripe / Twilio records where possible.

### Scenario 5: Documents lost from storage

- If <7 days: restore from Supabase Storage snapshot.
- If older: lost. The manifest tells us what was lost. Affected clients are emailed asking to re-upload.

### Scenario 6: All admin accounts locked out

1. SSH to the Hetzner box.
2. `php artisan tinker`
3. Reset password and 2FA for a single owner:
   ```php
   $u = User::where('email', 'sana@...')->first();
   $u->password = Hash::make('temporary-secure-password');
   $u->two_factor_secret = null;
   $u->two_factor_confirmed_at = null;
   $u->save();
   ```
4. Owner logs in with the temporary password.
5. Owner immediately changes password and re-enrolls 2FA.
6. Audit log entry created automatically.

## Restore drills

Quarterly. The lead developer:

1. Creates a PITR clone in Supabase.
2. Connects a local Laravel instance to the clone.
3. Runs migrations: `migrate --pretend` should show "Nothing to migrate".
4. Verifies row counts on key tables match prod.
5. Logs in as an admin, navigates the system.
6. Documents the drill in `OPERATIONS/drills/<date>.md`.
7. Deletes the clone.

Drills caught these issues in their first runs (real ops history would go here):
- (placeholder for actual findings)

## Incident response runbook

### Severity classification

| Sev | Definition | Response time |
|---|---|---|
| **Sev-1** | Service down, data loss, security breach | Immediate (<15 min) |
| **Sev-2** | Major feature broken (e.g. booking flow), partial outage | <1 hour |
| **Sev-3** | Minor feature broken, no PII / payment impact | <1 day |
| **Sev-4** | Cosmetic, internal-only issues | Next sprint |

### Sev-1 procedure

1. **Acknowledge.** First responder posts in agreed channel: "Sev-1 acknowledged at HH:MM by <name>."
2. **Stabilize.** If site is up but data risk is high, `php artisan down`. If site is down, check Forge / Hetzner status first.
3. **Notify Sana.** Within 15 min for any production Sev-1.
4. **Preserve evidence.** Don't restart services that would clear in-memory state. Take screenshots of dashboards. Export relevant logs.
5. **Mitigate.** Rollback if recent deploy. Restore from backup if data issue. Block the attack vector if security.
6. **Communicate.** If PII potentially exposed:
   - Notify CNDP within 72 hours (Loi 09-08).
   - Notify affected users within 72 hours of confirmation.
7. **Post-incident.** Within 72 hours of resolution, write incident report:
   - What happened
   - Impact
   - Root cause
   - Timeline
   - Detection
   - Resolution
   - Prevention measures

Report stored in `OPERATIONS/incidents/<date>-<slug>.md`. Reviewed by lead dev + Sana.

### Sev-2/3 procedure

Same shape, lower urgency. Don't skip the post-incident write-up for Sev-2.

## Communication templates

### Status page during outage (no public status page in v1)

Maintenance page (`storage/framework/maintenance.php`) shown via `php artisan down`:

```
"Nous effectuons une maintenance technique. Le site sera de retour
dans quelques instants. Pour une urgence, contactez-nous au
06 66 12 06 61 ou par WhatsApp."
```

Arabic equivalent below.

### Email to affected users (data incident)

Template stored in `resources/views/emails/incident/data-breach.blade.php`. Customized per incident.

Must include (per Loi 09-08):
- Nature of the incident
- When it occurred and when discovered
- Data categories affected
- Likely consequences
- Measures taken
- Contact for questions
- Their rights (access, rectification, deletion)

## Key-rotation playbook (`APP_KEY`)

Annual. Procedure:

1. Generate new key: `php artisan key:generate --show`.
2. Set `APP_KEY_PREVIOUS=<old key>` in Forge env.
3. Set `APP_KEY=<new key>` in Forge env.
4. Deploy.
5. Run rotation job: `php artisan model:reencrypt` (custom command we maintain).
6. Verify a sample of encrypted columns reads correctly.
7. After 30 days of stability, remove `APP_KEY_PREVIOUS`.

`model:reencrypt` walks all models with `encrypted` casts, decrypts with previous key, encrypts with new key, saves.

## Audit log preservation

The audit log (`activity_log` table) is part of the regular DB backups. In addition:

- A monthly job exports the previous month's activity log as a signed JSON file to offsite storage. This provides tamper-evidence — if the live log were modified, the offsite signed copy would expose the divergence.

## Lessons-learned ledger

Every drill and every incident contributes to this list (`OPERATIONS/lessons-learned.md`):

- What did we expect to work? What actually happened?
- What was unclear in the runbook?
- What automation or alerting would have caught this earlier?

Reviewed each quarter alongside the drill.
