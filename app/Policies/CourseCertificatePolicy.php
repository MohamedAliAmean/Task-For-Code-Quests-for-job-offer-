<?php

namespace App\Policies;

use App\Models\CourseCertificate;
use App\Models\User;

class CourseCertificatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CourseCertificate $courseCertificate): bool
    {
        return (int) $courseCertificate->user_id === (int) $user->getKey();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CourseCertificate $courseCertificate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CourseCertificate $courseCertificate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CourseCertificate $courseCertificate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CourseCertificate $courseCertificate): bool
    {
        return false;
    }
}
