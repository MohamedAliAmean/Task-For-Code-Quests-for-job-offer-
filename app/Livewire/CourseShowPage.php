<?php

namespace App\Livewire;

use App\Actions\EnrollUserInCourseAction;
use App\Actions\IssueCourseCertificateAction;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CourseShowPage extends Component
{
    public Course $course;

    public bool $isEnrolled = false;
    public bool $hasCertificate = false;
    public ?string $certificateId = null;

    public int $requiredLessonCount = 0;
    public int $completedRequiredLessonCount = 0;

    /**
     * @var array<int, array{started_at: string|null, completed_at: string|null}>
     */
    public array $progressByLessonId = [];

    public function mount(Course $course, IssueCourseCertificateAction $issueCourseCertificate): void
    {
        abort_unless($course->isPublished(), 404);

        $this->course = $course->load([
            'lessons' => fn ($query) => $query
                ->select(['id', 'course_id', 'position', 'title', 'is_preview', 'is_required'])
                ->orderBy('position'),
        ]);

        $this->refreshUserState();

        $user = Auth::user();
        if ($user && $this->isEnrolled && ! $this->hasCertificate) {
            $created = $issueCourseCertificate->execute($user, $this->course);

            if ($created) {
                $this->refreshUserState();
            }
        }
    }

    public function enroll(EnrollUserInCourseAction $enroll): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        try {
            $enroll->execute($user, $this->course);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Unable to enroll.';
            $this->addError('enrollment', $firstError);

            return;
        }

        $this->refreshUserState();
    }

    public function getProgressPercentProperty(): int
    {
        if ($this->requiredLessonCount === 0) {
            return 0;
        }

        return (int) floor(($this->completedRequiredLessonCount / $this->requiredLessonCount) * 100);
    }

    private function refreshUserState(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->isEnrolled = false;
            $this->hasCertificate = false;
            $this->certificateId = null;
            $this->requiredLessonCount = 0;
            $this->completedRequiredLessonCount = 0;
            $this->progressByLessonId = [];

            return;
        }

        $this->isEnrolled = CourseEnrollment::query()
            ->where('user_id', $user->getKey())
            ->where('course_id', $this->course->getKey())
            ->exists();

        $certificate = CourseCertificate::query()
            ->where('user_id', $user->getKey())
            ->where('course_id', $this->course->getKey())
            ->first();

        $this->hasCertificate = (bool) $certificate;
        $this->certificateId = $certificate?->getKey();

        $this->requiredLessonCount = Lesson::query()
            ->where('course_id', $this->course->getKey())
            ->where('is_required', true)
            ->whereNull('deleted_at')
            ->count();

        $this->completedRequiredLessonCount = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->where('lesson_progress.user_id', $user->getKey())
            ->whereNotNull('lesson_progress.completed_at')
            ->where('lessons.course_id', $this->course->getKey())
            ->where('lessons.is_required', true)
            ->whereNull('lessons.deleted_at')
            ->count();

        $lessonIds = $this->course->lessons->pluck('id')->all();

        if ($lessonIds === []) {
            $this->progressByLessonId = [];

            return;
        }

        $this->progressByLessonId = LessonProgress::query()
            ->where('user_id', $user->getKey())
            ->whereIn('lesson_id', $lessonIds)
            ->get(['lesson_id', 'started_at', 'completed_at'])
            ->mapWithKeys(fn (LessonProgress $progress) => [
                (int) $progress->lesson_id => [
                    'started_at' => $progress->started_at?->toIso8601String(),
                    'completed_at' => $progress->completed_at?->toIso8601String(),
                ],
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.course-show-page')->layout('layouts.site');
    }
}
