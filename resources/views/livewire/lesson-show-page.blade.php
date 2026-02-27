<div>
    <div class="space-y-6">
        <div>
            <a href="{{ route('courses.show', $course) }}" class="text-sm text-gray-600 hover:text-gray-900" wire:navigate>
                ← Back to course
            </a>

            <h1 class="mt-2 text-2xl font-semibold text-gray-900">
                {{ $lesson->title }}
            </h1>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                @if ($lesson->is_preview)
                    <span class="rounded bg-emerald-100 px-2 py-1 font-medium text-emerald-800">Preview</span>
                @endif
                @if (! $lesson->is_required)
                    <span class="rounded bg-gray-100 px-2 py-1 font-medium text-gray-700">Optional</span>
                @else
                    <span class="rounded bg-gray-100 px-2 py-1 font-medium text-gray-700">Required</span>
                @endif

                @if ($isEnrolled)
                    @if ($isCompleted)
                        <span class="font-medium text-emerald-700">Completed</span>
                    @elseif ($startedAt)
                        <span class="font-medium text-amber-700">In progress</span>
                    @else
                        <span class="text-gray-500">Not started</span>
                    @endif
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border bg-white">
            <div
                class="p-4"
                wire:ignore
                x-data="plyrVideoPlayer()"
                x-init="init(); return () => destroy()"
            >
                @if (in_array($lesson->video_provider, ['youtube', 'vimeo'], true) && $lesson->video_embed_id)
                    <div
                        class="aspect-video w-full"
                        x-ref="video"
                        data-plyr-provider="{{ $lesson->video_provider }}"
                        data-plyr-embed-id="{{ $lesson->video_embed_id }}"
                    ></div>
                @else
                    <video x-ref="video" playsinline controls class="w-full">
                        <source src="{{ $lesson->video_playback_url }}" type="video/mp4" />
                    </video>
                @endif
            </div>

            @if ($isEnrolled)
                <div class="border-t px-4 py-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-gray-600">
                            @if ($startedAt)
                                <div>Started: <span class="font-medium text-gray-900">{{ $startedAt }}</span></div>
                            @endif
                            @if ($completedAt)
                                <div>Completed: <span class="font-medium text-gray-900">{{ $completedAt }}</span></div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if (! $isCompleted)
                                <button
                                    type="button"
                                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800"
                                    x-on:click="$dispatch('open-modal', 'confirm-complete-lesson')"
                                >
                                    Mark as completed
                                </button>
                            @else
                                @if ($certificateId)
                                    <a
                                        href="{{ route('certificates.show', $certificateId) }}"
                                        class="rounded-md border bg-white px-4 py-2 text-sm font-medium text-gray-900 shadow-sm hover:bg-gray-50"
                                        wire:navigate
                                    >
                                        View certificate
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    @error('completion')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>
    </div>

    <x-modal name="confirm-complete-lesson" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">Mark lesson as completed?</h2>
            <p class="mt-2 text-sm text-gray-600">
                This action is idempotent — repeated clicks and retries won't create duplicate progress or certificates.
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-md border bg-white px-4 py-2 text-sm font-medium text-gray-900 shadow-sm hover:bg-gray-50"
                    x-on:click="$dispatch('close-modal', 'confirm-complete-lesson')"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 disabled:opacity-60"
                    wire:click="markCompleted"
                    wire:loading.attr="disabled"
                    x-on:click="$dispatch('close-modal', 'confirm-complete-lesson')"
                >
                    Confirm
                </button>
            </div>
        </div>
    </x-modal>
</div>
