# ERD

```mermaid
erDiagram
    users ||--o{ course_enrollments : enrolls
    courses ||--o{ course_enrollments : has

    courses ||--o{ lessons : contains
    users ||--o{ lesson_progress : tracks
    lessons ||--o{ lesson_progress : has

    users ||--o{ course_certificates : earns
    courses ||--o{ course_certificates : issues

    users {
      bigint id PK
      string name
      string email
      enum role
      bool is_admin
    }

    courses {
      bigint id PK
      string title
      string slug UK
      enum level
      enum status
      datetime published_at
      datetime deleted_at
    }

    lessons {
      bigint id PK
      bigint course_id FK
      int position
      bool is_preview
      bool is_required
      string video_url
      datetime deleted_at
    }

    course_enrollments {
      bigint id PK
      bigint user_id FK
      bigint course_id FK
      datetime enrolled_at
      UK user_id_course_id
    }

    lesson_progress {
      bigint id PK
      bigint user_id FK
      bigint lesson_id FK
      datetime started_at
      datetime completed_at
      UK user_id_lesson_id
    }

    course_certificates {
      uuid id PK
      bigint user_id FK
      bigint course_id FK
      datetime issued_at
      datetime completion_email_sent_at
      UK user_id_course_id
    }
```
