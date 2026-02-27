<?php

namespace App\Livewire;

use App\Models\CourseCertificate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class CertificateShowPage extends Component
{
    use AuthorizesRequests;

    public CourseCertificate $certificate;

    public function mount(CourseCertificate $courseCertificate): void
    {
        $this->authorize('view', $courseCertificate);

        $this->certificate = $courseCertificate->load('course');
    }

    public function render()
    {
        return view('livewire.certificate-show-page')->layout('layouts.site');
    }
}
