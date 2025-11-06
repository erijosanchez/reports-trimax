<?php

namespace App\Policies;

use App\Models\UploadedFile;
use App\Models\User;

class FilePolicy
{
    public function view(User $user, UploadedFile $file): bool
    {
        return $file->canBeViewedBy($user);
    }

    public function download(User $user, UploadedFile $file): bool
    {
        return $file->canBeDownloadedBy($user);
    }

    public function delete(User $user, UploadedFile $file): bool
    {
        return $file->user_id === $user->id || $user->isAdmin();
    }
}