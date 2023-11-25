<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Auth\Access\HandlesAuthorization;
use PhpParser\Node\Stmt\GroupUse;

class GroupPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Group $group)
    {
        return $group->user_id == $user->id || $user->role == User::ROLE['ADMIN'];
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Group $group)
    {
        return $group->user_id == $user->id || $user->role == User::ROLE['ADMIN'];
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function confirmJoin(User $user, Group $group)
    {
        $userGroup = UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->first();
        return $user->id === $group->user_id || $user->role == User::ROLE['ADMIN']  || $userGroup->role == UserGroup::ROLE_GROUP['COLLABORATOR'] || $userGroup->role == UserGroup::ROLE_GROUP['ADMIN'];
    }

    public function pleaseLeave(User $user, Group $group)
    {
        $userGroup = UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->first();
        return $user->id === $group->user_id || $user->role == User::ROLE['ADMIN']  || $userGroup->role == UserGroup::ROLE_GROUP['ADMIN'] || $userGroup->role == UserGroup::ROLE_GROUP['COLLABORATOR'];
    }

    public function setRole(User $user, Group $group)
    {
        $userGroup = UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->first();
        return $user->id == $group->user_id || $user->role == User::ROLE['ADMIN']  || $userGroup->role == UserGroup::ROLE_GROUP['ADMIN'];
    }

}
