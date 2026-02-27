<div>
    <div class="space-y-6">
        <a href="{{ route('courses.show', $certificate->course) }}" class="text-sm text-gray-600 hover:text-gray-900" wire:navigate>
            ← Back to course
        </a>

        <div class="rounded-lg border bg-white p-6">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold text-gray-900">Certificate</h1>
                <p class="text-sm text-gray-600">Proof of completion for this course.</p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-xs font-medium text-gray-500">Course</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">{{ $certificate->course->title }}</div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-500">Issued at</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">{{ $certificate->issued_at->toDayDateTimeString() }}</div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-xs font-medium text-gray-500">Certificate UUID</div>
                    <div class="mt-1 font-mono text-sm text-gray-900">{{ $certificate->getKey() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
