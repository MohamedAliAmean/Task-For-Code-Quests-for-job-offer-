<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Mail\CourseCompletedMail;
use App\Models\CourseCertificate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendCourseCompletionEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CourseCompleted $event): void
    {
        $updated = CourseCertificate::query()
            ->whereKey($event->certificateId)
            ->whereNull('completion_email_sent_at')
            ->update([
                'completion_email_sent_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            return;
        }

        $certificate = CourseCertificate::query()
            ->with(['user', 'course'])
            ->findOrFail($event->certificateId);

        Mail::to($certificate->user)->send(new CourseCompletedMail($certificate));
    }
}
