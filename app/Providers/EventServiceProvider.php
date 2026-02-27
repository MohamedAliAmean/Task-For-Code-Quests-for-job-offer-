<?php

namespace App\Providers;

use App\Events\CourseCompleted;
use App\Listeners\SendCourseCompletionEmail;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendWelcomeEmail::class,
        ],
        CourseCompleted::class => [
            SendCourseCompletionEmail::class,
        ],
    ];
}
