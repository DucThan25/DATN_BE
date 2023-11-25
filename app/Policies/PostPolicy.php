<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class PostPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Post $post)
    {
        return $post->user_id == $user->id || $user->role == User::ROLE['ADMIN'];
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Post $post)
    {
        return $post->user_id == $user->id || $user->role == User::ROLE['ADMIN'];
    }

    public function createPostGroup(User $user, $group)
    {
        $check = false;
        $member = UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->where('status', UserGroup::STATUS['JOINED'])->first();
        if($member) {
            $member->user_id == $user->id ? $check = true : $check = false;
        }
        return $user->role == User::ROLE['ADMIN'] || $check == true;
    }

    public function deletePostGroup(User $user, Post $post, $group)
    {
        $check = false;
        Log::info($check);
        $member = UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->where('role', '<>', UserGroup::ROLE_GROUP['MEMBER'])->first();
        if($member) {
            $member->user_id == $user->id ? $check = true : $check = false;
        }
        return $post->user_id == $user->id || $user->role == User::ROLE['ADMIN'] || $check == true;
    }

    public function viewPostGroup (User $user, $group)
    {
        $check = false;
        $member = UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->first();
        if($member) {
            $member->user_id == $user->id ? $check = true : $check = false;
        }
        return $user->role == User::ROLE['ADMIN'] || $check == true;
    }
}
