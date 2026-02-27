<div>
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Courses</h1>
            <p class="mt-1 text-sm text-gray-600">Browse published courses.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($courses as $course)
            <a
                href="{{ route('courses.show', $course) }}"
                wire:navigate
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
                        <h2 class="font-semibold text-gray-900 group-hover:underline">
                            {{ $course->title }}
                        </h2>

                        <span class="shrink-0 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ str($course->level->value)->replace('_', ' ')->title() }}
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-lg border bg-white p-6 text-sm text-gray-600">
                No published courses yet.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $courses->links() }}
    </div>
</div>
