<?php
namespace App\Repositories;

use App\Models\Comment;

class CommentRepository extends BaseRepository
{
    protected $comment;
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Comment::class;
    }

    public function getlistComment($postId)
    {
        return $this->model->with('user')->where('post_id', $postId)->get();
    }
}
