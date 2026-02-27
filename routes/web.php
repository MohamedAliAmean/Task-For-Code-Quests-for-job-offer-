<?php

use App\Livewire\CertificateShowPage;
use App\Livewire\CourseShowPage;
use App\Livewire\HomePage;
use App\Livewire\LessonShowPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');

Route::get('/courses/{course:slug}', CourseShowPage::class)
    ->name('courses.show');

Route::get('/courses/{course:slug}/lessons/{lesson}', LessonShowPage::class)
    ->scopeBindings()
    ->name('courses.lessons.show');

Route::get('/certificates/{courseCertificate}', CertificateShowPage::class)
    ->middleware('auth')
    ->name('certificates.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
