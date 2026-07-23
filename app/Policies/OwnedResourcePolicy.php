<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class OwnedResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return $model->getAttribute('user_id') === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Model $model): bool
    {
        return $this->view($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->view($user, $model);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->view($user, $model);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }
}
