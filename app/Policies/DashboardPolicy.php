<?php

namespace App\Policies;

use App\Models\Dashboard;
use App\Models\User;

class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dashboard $dashboard): bool
    {
        return $user->hasAccessToDashboard($dashboard->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Dashboard $dashboard): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Dashboard $dashboard): bool
    {
        return $user->isSuperAdmin();
    }
}