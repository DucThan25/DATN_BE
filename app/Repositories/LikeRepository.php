<?php
namespace App\Repositories;

use App\Models\Like;

class LikeRepository extends BaseRepository
{
    protected $like;

    public function getModel()
    {
        return Like::class;
    }

    public function checkLikePost($userId, $postId)
    {
        return $this->model->where('user_id', $userId)->where('post_id', $postId)->first();
    }
}
