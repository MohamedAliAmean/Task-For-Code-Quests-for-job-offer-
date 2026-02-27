<?php

namespace App\Livewire;

use App\Actions\MarkLessonCompletedAction;
use App\Actions\MarkLessonStartedAction;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LessonShowPage extends Component
{
    public Course $course;
    public Lesson $lesson;

    public bool $isEnrolled = false;
    public bool $isCompleted = false;
    public ?string $startedAt = null;
    public ?string $completedAt = null;
    public ?string $certificateId = null;

    public function mount(Course $course, Lesson $lesson): void
    {
        abort_unless($course->isPublished(), 404);

        $this->course = $course;
        $this->lesson = $lesson;

        $user = Auth::user();

        if ($user) {
            $this->isEnrolled = CourseEnrollment::query()
                ->where('user_id', $user->getKey())
                ->where('course_id', $course->getKey())
                ->exists();

            if ($this->isEnrolled) {
                $this->refreshProgress();

                $certificate = CourseCertificate::query()
                    ->where('user_id', $user->getKey())
                    ->where('course_id', $course->getKey())
                    ->first();

                $this->certificateId = $certificate?->getKey();
            }
        }

        if (! $lesson->is_preview && ! $this->isEnrolled) {
            if (! $user) {
                $this->redirectRoute('login', navigate: true);

                return;
            }

            abort(403);
        }
    }

    public function markStarted(MarkLessonStartedAction $markLessonStarted): void
    {
        $user = Auth::user();
        if (! $user || ! $this->isEnrolled) {
            return;
        }

        try {
            $markLessonStarted->execute($user, $this->lesson);
        } catch (ValidationException) {
            return;
        }

        $this->refreshProgress();
    }

    public function markCompleted(MarkLessonCompletedAction $markLessonCompleted): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        try {
            $markLessonCompleted->execute($user, $this->lesson);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Unable to complete the lesson.';
            $this->addError('completion', $firstError);

            return;
        }

        $this->refreshProgress();

        $certificate = CourseCertificate::query()
            ->where('user_id', $user->getKey())
            ->where('course_id', $this->course->getKey())
            ->first();

        $this->certificateId = $certificate?->getKey();
    }

    private function refreshProgress(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $progress = LessonProgress::query()
            ->where('user_id', $user->getKey())
            ->where('lesson_id', $this->lesson->getKey())
            ->first();

        $this->isCompleted = (bool) $progress?->completed_at;
        $this->startedAt = $progress?->started_at?->toDayDateTimeString();
        $this->completedAt = $progress?->completed_at?->toDayDateTimeString();
    }

    public function render()
    {
        return view('livewire.lesson-show-page')->layout('layouts.site');
    }
}
