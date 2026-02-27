<?php

namespace App\Livewire;

use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;

class HomePage extends Component
{
    use WithPagination;

    public function render()
    {
        $courses = Course::query()
            ->published()
            ->select(['id', 'title', 'slug', 'level', 'image_path'])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('livewire.home-page', [
            'courses' => $courses,
        ])->layout('layouts.site');
    }
}
