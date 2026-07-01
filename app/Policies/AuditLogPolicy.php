<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('audit-log.view');
    }

    public function export(User $user): bool
    {
        return $user->can('audit-log.export');
    }
}
