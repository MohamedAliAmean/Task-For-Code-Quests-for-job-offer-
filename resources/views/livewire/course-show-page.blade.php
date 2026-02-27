<div>
    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-start">
            <div class="min-w-0">
                <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-gray-900" wire:navigate>
                    ← All courses
                </a>

                <h1 class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ $course->title }}
                </h1>

                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                        {{ str($course->level->value)->replace('_', ' ')->title() }}
                    </span>

                    @if ($course->status->value === 'published')
                        <span class="text-xs text-emerald-700">Published</span>
                    @endif
                </div>

                @if ($course->description)
                    <p class="mt-4 max-w-3xl text-sm leading-6 text-gray-700">
                        {{ $course->description }}
                    </p>
                @endif
            </div>

            <div class="flex shrink-0 flex-col items-stretch gap-2">
                @if ($hasCertificate && $certificateId)
                    <a
                        href="{{ route('certificates.show', $certificateId) }}"
                        class="rounded-md border bg-white px-4 py-2 text-center text-sm font-medium text-gray-900 shadow-sm hover:bg-gray-50"
                        wire:navigate
                    >
                        View certificate
                    </a>
                @endif

                @auth
                    @if (! $isEnrolled)
                        <button
                            type="button"
                            wire:click="enroll"
                            wire:loading.attr="disabled"
                            class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 disabled:opacity-60"
                        >
                            <span wire:loading.remove>Enroll</span>
                            <span wire:loading>Enrolling…</span>
                        </button>
                    @else
                        <div class="rounded-md bg-emerald-50 px-4 py-2 text-center text-sm font-medium text-emerald-800">
                            Enrolled
                        </div>
                    @endif
                @else
                    <a
                        href="{{ route('login') }}"
                        class="rounded-md bg-gray-900 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-800"
                        wire:navigate
                    >
                        Login to enroll
                    </a>
                @endauth

                @error('enrollment')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($isEnrolled)
            <div
                class="rounded-lg border bg-white p-4"
                x-data="{ value: 0, target: {{ $this->progressPercent }} }"
                x-init="requestAnimationFrame(() => value = target)"
            >
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="text-sm font-medium text-gray-900">Progress</h2>
                        <p class="mt-1 text-xs text-gray-600">
                            {{ $completedRequiredLessonCount }} / {{ $requiredLessonCount }} required lessons completed
                        </p>
                    </div>
                    <div class="shrink-0 text-sm font-semibold text-gray-900" x-text="`${value}%`"></div>
                </div>

                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                    <div
                        class="h-full bg-gray-900 transition-all duration-700"
                        :style="`width: ${value}%`"
                    ></div>
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-lg border bg-white">
            <div class="border-b px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Lessons</h2>
            </div>

            <div class="divide-y" x-data="{ openId: null }">
                @forelse ($course->lessons as $lesson)
                    @php
                        $progress = $progressByLessonId[$lesson->id] ?? null;
                        $isCompleted = (bool) ($progress['completed_at'] ?? null);
                        $isStarted = (bool) ($progress['started_at'] ?? null);
                        $canAccess = (bool) ($lesson->is_preview || (auth()->check() && $isEnrolled));
                    @endphp

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-4">
                            <button
                                type="button"
                                class="min-w-0 text-left"
                                @click="openId = (openId === {{ $lesson->id }} ? null : {{ $lesson->id }})"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $lesson->position }}. {{ $lesson->title }}
                                    </span>

                                    @if ($lesson->is_preview)
                                        <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                            Preview
                                        </span>
                                    @endif

                                    @if (! $lesson->is_required)
                                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                            Optional
                                        </span>
                                    @endif
                                </div>
                            </button>

                            <div class="flex shrink-0 items-center gap-3 text-xs">
                                @if ($isEnrolled)
                                    @if ($isCompleted)
                                        <span class="font-medium text-emerald-700">Completed</span>
                                    @elseif ($isStarted)
                                        <span class="font-medium text-amber-700">In progress</span>
                                    @else
                                        <span class="text-gray-500">Not started</span>
                                    @endif
                                @endif

                                @if ($canAccess)
                                    <a
                                        href="{{ route('courses.lessons.show', [$course, $lesson]) }}"
                                        class="font-medium text-gray-700 underline hover:text-gray-900"
                                        wire:navigate
                                    >
                                        Open
                                    </a>
                                @else
                                    <span class="text-gray-400">Locked</span>
                                @endif
                            </div>
                        </div>

                        <div
                            x-show="openId === {{ $lesson->id }}"
                            x-transition.duration.150ms
                            class="mt-3 text-sm text-gray-600"
                        >
                            <div class="space-y-1">
                                <p>{{ $lesson->is_preview ? 'Free preview lesson.' : 'Enrollment required to watch.' }}</p>
                                <p>{{ $lesson->is_required ? 'Required for course completion.' : 'Optional lesson (does not affect completion).' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-sm text-gray-600">No lessons yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
