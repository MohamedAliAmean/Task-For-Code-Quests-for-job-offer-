<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $courses = Course::query()
            ->published()
            ->whereHas('enrollments', fn ($query) => $query->where('user_id', $user->getKey()))
            ->select(['id', 'title', 'slug', 'level', 'image_path', 'published_at'])
            ->orderByDesc('published_at')
            ->get();

        $courseIds = $courses->pluck('id')->all();

        $totalEnrollmentCount = CourseEnrollment::query()
            ->where('user_id', $user->getKey())
            ->count();

        $requiredLessonsByCourse = [];
        $completedRequiredLessonsByCourse = [];

        if ($courseIds !== []) {
            $requiredLessonsByCourse = Lesson::query()
                ->whereIn('course_id', $courseIds)
                ->where('is_required', true)
                ->whereNull('deleted_at')
                ->select('course_id', DB::raw('COUNT(*) as required_count'))
                ->groupBy('course_id')
                ->pluck('required_count', 'course_id')
                ->all();

            $completedRequiredLessonsByCourse = LessonProgress::query()
                ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
                ->where('lesson_progress.user_id', $user->getKey())
                ->whereNotNull('lesson_progress.completed_at')
                ->whereIn('lessons.course_id', $courseIds)
                ->where('lessons.is_required', true)
                ->whereNull('lessons.deleted_at')
                ->select('lessons.course_id', DB::raw('COUNT(*) as completed_count'))
                ->groupBy('lessons.course_id')
                ->pluck('completed_count', 'lessons.course_id')
                ->all();
        }

        return view('dashboard', [
            'courses' => $courses,
            'totalEnrollmentCount' => $totalEnrollmentCount,
            'requiredLessonsByCourse' => $requiredLessonsByCourse,
            'completedRequiredLessonsByCourse' => $completedRequiredLessonsByCourse,
        ]);
    }
}

