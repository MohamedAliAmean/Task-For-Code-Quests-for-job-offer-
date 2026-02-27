<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                @if (($totalEnrollmentCount ?? 0) > ($courses?->count() ?? 0))
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        You are enrolled in {{ $totalEnrollmentCount }} course(s), but only published courses are shown here.
                        If a course is still in draft, it won’t appear for learners.
                    </div>
                @endif

                <div class="rounded-lg bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">My Courses</h3>
                            <p class="mt-1 text-sm text-gray-600">Courses you are enrolled in.</p>
                        </div>

                        <a
                            href="{{ route('home') }}"
                            class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800"
                        >
                            Browse all courses
                        </a>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($courses as $course)
                            @php
                                $required = (int) ($requiredLessonsByCourse[$course->id] ?? 0);
                                $completed = (int) ($completedRequiredLessonsByCourse[$course->id] ?? 0);
                                $percent = $required > 0 ? (int) floor(($completed / $required) * 100) : 0;
                            @endphp

                            <a
                                href="{{ route('courses.show', $course) }}"
                                class="group overflow-hidden rounded-lg border bg-white shadow-sm transition hover:shadow"
                            >
                                <div class="aspect-video w-full bg-gray-100">
                                    @if ($course->image_url)
                                        <img
                                            src="{{ $course->image_url }}"
                                            alt="{{ $course->title }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-sm text-gray-500">
                                            No image
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <h4 class="font-semibold text-gray-900 group-hover:underline">
                                            {{ $course->title }}
                                        </h4>

                                        <span class="shrink-0 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ str($course->level->value)->replace('_', ' ')->title() }}
                                        </span>
                                    </div>

                                    <div class="mt-4">
                                        <div class="flex items-center justify-between text-xs text-gray-600">
                                            <span>Progress</span>
                                            <span>{{ $percent }}%</span>
                                        </div>
                                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                            <div class="h-full rounded-full bg-gray-900 transition-all" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-lg border bg-white p-6 text-sm text-gray-600">
                                You are not enrolled in any published courses yet.
                                <a href="{{ route('home') }}" class="underline">Browse courses</a>.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
