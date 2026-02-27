# Product Thinking

## 1) Business Risks (Top 3)

### Risk 1 — Trust & credibility (certificates, completion, duplicates)
If learners can get duplicated certificates, repeated completion emails, or duplicated enrollments, the product loses credibility.

Mitigation in architecture:
- DB unique constraints for enrollments, lesson progress, and certificates.
- Idempotent Actions (`insertOrIgnore`, `whereNull()->update()`).
- `CourseCompleted` dispatched after commit to avoid sending on rolled-back transactions.

### Risk 2 — Low completion & poor learner outcomes
If learners drop off early, the LMS fails the business goal (career outcomes).

Mitigation in architecture:
- Progress tracking at lesson level (`started_at`, `completed_at`).
- Required vs optional lessons allows shaping “completion” without removing enrichment content.
- Clear progress UI (progress bar + completion states) supports motivation.

### Risk 3 — Scalability/performance bottlenecks
The LMS must support thousands of learners without degrading UX or data correctness.

Mitigation in architecture:
- Avoid N+1 (home page uses a single query; course page loads ordered lessons).
- Use indexes + constraints to keep reads/writes predictable.
- Use queues for email side-effects.

## 2) Metrics That Matter (5)

1. **Course view → enrollment conversion**
   - Capture: increment a `course_views` counter or store events in an `analytics_events` table.
   - Compute: daily aggregates via a scheduled job.
2. **Enrollment → first lesson started**
   - Capture: `lesson_progress.started_at` (first started lesson after enrollment).
   - Compute: query aggregates or store a denormalized “first_started_at” per enrollment.
3. **Lesson drop-off points**
   - Capture: last started vs completed lesson per user/course.
   - Compute: background aggregation to avoid expensive real-time scans.
4. **Course completion rate + time-to-complete**
   - Capture: `course_certificates.issued_at` and enrollment time.
   - Compute: stored (certificate is the completion snapshot) + derived duration.
5. **Email engagement (welcome & completion)**
   - Capture: store “sent” timestamps in DB; for opens/clicks use provider webhooks later.
   - Avoid performance issues: process webhook events asynchronously into an outbox/events table.

## 3) Future Evolution (6 months)

### Paid courses
Supports now:
- Enrollment is already centralized in an Action (`EnrollUserInCourseAction`).

Needs refactor/additions:
- Replace “enrollment” with an **entitlement** model (free via enrollment, paid via purchase).
- Add `orders`, `payments`, `entitlements` tables and checks in access policies.

### Mobile app API
Supports now:
- Business logic in Actions can be reused by controllers/API resources.

Needs refactor/additions:
- Add API routes/resources (Laravel API Resources), token auth, versioning.

### Corporate multi-tenant accounts
Supports now:
- Policies + centralized Actions reduce scattered access rules.

Needs refactor/additions:
- Add `tenant_id` to core tables and apply tenancy scoping (middleware + global scopes).

### Gamification badges
Supports now:
- Event-driven completion flow (`CourseCompleted`) is a natural hook.

Needs refactor/additions:
- Add badge awarding jobs/listeners and badge definitions table.

## 4) Trade-offs Made (3)

1. **Completion email “at-most-once”**
   - I mark `completion_email_sent_at` before sending to avoid duplicates under retries.
   - Trade-off: if the job crashes after marking, the email may be skipped.
2. **Certificate is a completion snapshot**
   - A certificate is not revoked if new required lessons are added later.
   - Trade-off: course completion reflects a point-in-time definition.
3. **Re-evaluate completion on learner interaction**
   - When lessons are removed, completion can be re-evaluated on the course page view.
   - Trade-off: avoids expensive fan-out recalculation jobs, but completion may update “eventually” (on next interaction).

