<?php

namespace App\Policies;

use App\Domain\Document\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $user->can('documents.view');
    }

    public function upload(User $user): bool
    {
        return $user->can('documents.upload');
    }

    public function download(User $user, Document $document): bool
    {
        return $user->can('documents.download');
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->can('documents.delete');
    }
}
