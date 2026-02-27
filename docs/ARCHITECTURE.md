# Architecture

## Overview

This project is a Mini-LMS foundation built around a few core flows:

- Browse published courses (`/`)
- Enroll in a course (idempotent)
- Watch lessons (free preview for guests)
- Track per-lesson progress (`started_at`, `completed_at`)
- Auto-complete a course when all **required** lessons are completed
- Issue a **UUID certificate** and send a completion email **once only** (async)

The architecture emphasizes **data integrity + concurrency safety** through DB constraints + transactions + idempotent writes.

## High-level components

- **Livewire page components** (`app/Livewire/*Page.php`) for UI + UX.
- **Action classes** (`app/Actions/*Action.php`) for core business flows.
- **Events + queued listeners**
  - `CourseCompleted` → `SendCourseCompletionEmail` (queued)
  - `Registered` → `SendWelcomeEmail` (queued)
- **Policies**
  - `CourseCertificatePolicy` prevents cross-user access.
- **Admin panel (Filament v3)**
  - CRUD for `Users` and `Courses` (and nested `Lessons`, `Enrollments`, `Certificates`).
  - Access controlled via `User::canAccessPanel()` + a dedicated `admin` auth guard.

## Data model (core tables)

- `courses` (soft deletes, unique `slug`)
- `lessons` (soft deletes, ordered by `position`, `is_preview`, `is_required`)
- `course_enrollments` (unique `(user_id, course_id)`)
- `lesson_progress` (unique `(user_id, lesson_id)`, tracks `started_at`, `completed_at`)
- `course_certificates` (UUID PK, unique `(user_id, course_id)`, `completion_email_sent_at`)

## Admin panel (Filament)

### Why Filament

Business goal: reviewers and non-engineers should be able to validate the system quickly.

Filament accelerates the “manage content + manage users” workflow without custom CRUD pages, while still keeping business logic inside Actions and DB constraints.

### Access control

- Panel path: `/admin`
- Guard: `admin` (configured in `config/auth.php`, set in `app/Providers/Filament/AdminPanelProvider.php`)
- User access: `App\Models\User` implements `FilamentUser` and allows access only when:
  - `role === admin` (or `is_admin === true` for backward compatibility)

This prevents learners from gaining admin access just because they are authenticated on the public site.

## Concurrency & integrity strategy

### 1) Enrollment idempotency

- DB constraint: `course_enrollments` has a unique index on `(user_id, course_id)`.
- Action: `EnrollUserInCourseAction`
  - Uses a transaction.
  - Locks the course row (`lockForUpdate`) to prevent enrolling in a course that might be concurrently unpublished.
  - Uses `insertOrIgnore` to remain safe under rapid clicks / retries.

### 2) Lesson progress idempotency

- DB constraint: `lesson_progress` has a unique index on `(user_id, lesson_id)`.
- Actions:
  - `MarkLessonStartedAction` sets `started_at` only if it’s `NULL`.
  - `MarkLessonCompletedAction` sets `completed_at` only if it’s `NULL` (and also backfills `started_at`).

This makes repeated requests safe (rapid clicks / network retries).

### 3) Course completion + certificate uniqueness

- Completion rule: all **required** lessons (that are not deleted) must be completed.
- DB constraint: `course_certificates` has unique `(user_id, course_id)` and UUID primary key.
- Action: `IssueCourseCertificateAction`
  - Computes required vs completed required lessons.
  - Issues the certificate via `insertOrIgnore` to guarantee “once only” creation under race conditions.
  - Dispatches `CourseCompleted` **after commit**.

### 4) Completion email once-only

- Listener: `SendCourseCompletionEmail` (queued).
- Idempotency marker: `course_certificates.completion_email_sent_at`.
- Strategy: update `completion_email_sent_at` **only when NULL**; only the first handler run sends the email.

Trade-off: prioritizes “at-most-once” sending (never duplicates) over “exactly-once even on crashes”.

## Queue driver (database)

This task uses the `database` queue driver (simple DX, Docker-only setup). The architecture keeps all side-effects (welcome email / completion email) behind queued listeners, so switching to Redis later is a config change, not a code rewrite.

## Courses edited after enrollment (lessons added/removed)

Course completion is evaluated:

- when a lesson is completed, and
- when an enrolled learner loads the course page (to catch cases like required lessons being removed).

Once a certificate exists, the course is treated as completed (certificate is a completion snapshot).

## Performance notes

- `/` uses a single query with selected columns (no N+1).
- `courses.slug` is unique and indexed.
- `lesson_progress` and enrollment tables have unique constraints + supporting indexes for counting.

## Docker

See `docker-compose.yml`:

- `app` (PHP-FPM)
- `web` (Nginx)
- `db` (MySQL)
- `queue` (queue worker)
