<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Comment $comment)
    {
        return $comment->user_id == $user->id || $user->role == User::ROLE['ADMIN'];
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Comment $comment, Post $post)
    {
        return $comment->user_id == $user->id || $user->role == User::ROLE['ADMIN'] || $user->id == $post->user_id;
    }

    public function commentGroup(User $user, $group)
    {
        $check = false;
        $member = UserGroup::where('user_id', $user->id)->where('group_id', $group->id)->where('status', UserGroup::STATUS['JOINED'])->first();
        if($member) {
            $member->user_id == $user->id ? $check = true : $check = false;
        }
        return $user->role == User::ROLE['ADMIN'] || $check == true;
    }
}
